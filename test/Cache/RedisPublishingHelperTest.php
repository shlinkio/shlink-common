<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Cache;

use PHPUnit\Framework\TestCase;
use Predis\ClientInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Shlinkio\Shlink\Common\Cache\RedisPublishingHelper;
use Shlinkio\Shlink\Common\UpdatePublishing\Update;

class RedisPublishingHelperTest extends TestCase
{
    use ProphecyTrait;

    private RedisPublishingHelper $helper;
    private ObjectProphecy $predis;

    public function setUp(): void
    {
        $this->predis = $this->prophesize(ClientInterface::class);
        $this->helper = new RedisPublishingHelper($this->predis->reveal());
    }

    /** @test */
    public function publishingIsForwardedToRedisClient(): void
    {
        $this->helper->publishUpdate(Update::forTopicAndPayload('foo', ['bar' => 'baz']));
        $this->predis->publish('foo', '{"bar":"baz"}')->shouldHaveBeenCalledOnce();
    }
}
