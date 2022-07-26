<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\RabbitMq;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use Shlinkio\Shlink\Common\UpdatePublishing\PublishingHelperInterface;
use Shlinkio\Shlink\Common\UpdatePublishing\Update;
use Throwable;

use function Shlinkio\Shlink\Common\json_encode;

class RabbitMqPublishingHelper implements PublishingHelperInterface
{
    public function __construct(private readonly AMQPStreamConnection $connection)
    {
    }

    /**
     * @throws Throwable
     */
    public function publishUpdate(Update $update): void
    {
        if (! $this->connection->isConnected()) {
            $this->connection->reconnect();
        }

        try {
            $channel = $this->connection->channel();

            // Declare an exchange and a queue that will persist server restarts
            $exchange = $queue = $update->topic; // We use the same name for the exchange and the queue
            $channel->exchange_declare($exchange, AMQPExchangeType::DIRECT, false, true, false);
            $channel->queue_declare($queue, false, true, false, false);

            // Bind the exchange and the queue together, and publish the message
            $channel->queue_bind($queue, $exchange);
            $channel->basic_publish($this->updateToMessage($update), $exchange);

            $channel->close();
        } finally {
            $this->connection->close();
        }
    }

    private function updateToMessage(Update $update): AMQPMessage
    {
        return new AMQPMessage(json_encode($update->payload), [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ]);
    }
}
