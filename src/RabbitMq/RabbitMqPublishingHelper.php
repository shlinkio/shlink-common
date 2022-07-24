<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\RabbitMq;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

use function Shlinkio\Shlink\Common\json_encode;

class RabbitMqPublishingHelper implements RabbitMqPublishingHelperInterface
{
    public function __construct(private readonly AMQPStreamConnection $connection)
    {
    }

    /**
     * @throws Throwable
     */
    public function publishPayloadInQueue(array $payload, string $queue): void
    {
        if (! $this->connection->isConnected()) {
            $this->connection->reconnect();
        }

        try {
            $channel = $this->connection->channel();

            // Declare an exchange and a queue that will persist server restarts
            $exchange = $queue; // We use the same name for the exchange and the queue
            $channel->exchange_declare($exchange, AMQPExchangeType::DIRECT, false, true, false);
            $channel->queue_declare($queue, false, true, false, false);

            // Bind the exchange and the queue together, and publish the message
            $channel->queue_bind($queue, $exchange);
            $channel->basic_publish($this->payloadToMessage($payload), $exchange);

            $channel->close();
        } finally {
            $this->connection->close();
        }
    }

    private function payloadToMessage(array $payload): AMQPMessage
    {
        return new AMQPMessage(json_encode($payload), [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ]);
    }
}
