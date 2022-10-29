<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\RabbitMq;

use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\RabbitMq\RabbitMqPublishingHelper;
use Shlinkio\Shlink\Common\UpdatePublishing\Update;

use function Shlinkio\Shlink\Common\json_encode;

class RabbitMqPublishingHelperTest extends TestCase
{
    private RabbitMqPublishingHelper $helper;
    private MockObject & AMQPStreamConnection $connection;
    private MockObject & AMQPChannel $channel;

    public function setUp(): void
    {
        $this->channel = $this->createMock(AMQPChannel::class);
        $this->connection = $this->createMock(AMQPStreamConnection::class);
        $this->connection->method('isConnected')->willReturn(false);

        $this->helper = new RabbitMqPublishingHelper($this->connection);
    }

    /** @test */
    public function expectedChannelsAreNotified(): void
    {
        $channel = 'foobar';
        $payload = ['some' => 'thing'];
        $this->channel->expects($this->once())->method(
            'exchange_declare',
        )->with($channel, AMQPExchangeType::DIRECT, false, true, false);
        $this->channel->expects($this->once())->method('queue_declare')->with($channel, false, true, false, false);
        $this->channel->expects($this->once())->method('queue_bind')->with($channel, $channel);
        $this->channel->expects($this->once())->method('basic_publish')->with(new AMQPMessage(json_encode($payload), [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ]), $channel);
        $this->channel->expects($this->once())->method('close');
        $this->connection->expects($this->once())->method('channel')->willReturn($this->channel);
        $this->connection->expects($this->once())->method('reconnect');
        $this->connection->expects($this->once())->method('close');

        $this->helper->publishUpdate(Update::forTopicAndPayload($channel, $payload));
    }

    /** @test */
    public function connectionIsClosedEvenInCaseOfError(): void
    {
        $channel = 'foobar';
        $payload = ['some' => 'thing'];
        $expectedError = new Exception('Error!');

        $this->connection->expects($this->once())->method('channel')->willThrowException($expectedError);
        $this->connection->expects($this->once())->method('close');

        try {
            $this->helper->publishUpdate(Update::forTopicAndPayload($channel, $payload));
        } catch (Exception $e) {
            self::assertSame($expectedError, $e);
        }
    }
}
