<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Cache;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Predis\ClientInterface;
use Shlinkio\Shlink\Common\Cache\RedisPublishingHelper;
use Shlinkio\Shlink\Common\UpdatePublishing\Update;

class RedisPublishingHelperTest extends TestCase
{
    private RedisPublishingHelper $helper;
    private MockObject & ClientInterface $predis;

    public function setUp(): void
    {
        $this->predis = $this->createMock(ClientInterface::class);
        $this->helper = new RedisPublishingHelper($this->predis);
    }

    #[Test]
    public function publishingIsForwardedToRedisClient(): void
    {
        $this->predis->expects($this->once())->method('__call')->with('publish', ['foo', '{"bar":"baz"}']);
        $this->helper->publishUpdate(Update::forTopicAndPayload('foo', ['bar' => 'baz']));
    }
}
