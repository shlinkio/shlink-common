<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\RabbitMq;

use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Shlinkio\Shlink\Common\RabbitMq\RabbitMqPublishingHelper;

use function Shlinkio\Shlink\Common\json_encode;

class RabbitMqPublishingHelperTest extends TestCase
{
    use ProphecyTrait;

    private RabbitMqPublishingHelper $helper;
    private ObjectProphecy $connection;
    private ObjectProphecy $channel;

    public function setUp(): void
    {
        $this->channel = $this->prophesize(AMQPChannel::class);
        $this->connection = $this->prophesize(AMQPStreamConnection::class);
        $this->connection->isConnected()->willReturn(false);
        $this->connection->reconnect()->will(function (): void {
        });
        $this->connection->channel()->willReturn($this->channel->reveal());

        $this->helper = new RabbitMqPublishingHelper($this->connection->reveal());
    }

    /** @test */
    public function expectedChannelsAreNotified(): void
    {
        $channel = 'foobar';
        $payload = ['some' => 'thing'];

        $this->helper->publishPayloadInQueue($payload, $channel);

        $this->channel->exchange_declare($channel, AMQPExchangeType::DIRECT, false, true, false)
                      ->shouldHaveBeenCalledOnce();
        $this->channel->queue_declare($channel, false, true, false, false)->shouldHaveBeenCalledOnce();
        $this->channel->queue_bind($channel, $channel)->shouldHaveBeenCalledOnce();
        $this->channel->basic_publish(new AMQPMessage(json_encode($payload), [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ]), $channel)->shouldHaveBeenCalledOnce();
        $this->channel->close()->shouldHaveBeenCalledOnce();
        $this->connection->reconnect()->shouldHaveBeenCalledOnce();
        $this->connection->close()->shouldHaveBeenCalledOnce();
    }

    /** @test */
    public function connectionIsClosedEvenInCaseOfError(): void
    {
        $channel = 'foobar';
        $payload = ['some' => 'thing'];
        $expectedError = new Exception('Error!');

        $this->connection->channel()->willThrow($expectedError);
        $this->connection->close()->shouldBeCalledOnce();

        try {
            $this->helper->publishPayloadInQueue($payload, $channel);
        } catch (Exception $e) {
            self::assertSame($expectedError, $e);
        }
    }
}
