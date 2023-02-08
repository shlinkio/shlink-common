<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Mercure;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Mercure\MercureHubPublishingHelper;
use Shlinkio\Shlink\Common\UpdatePublishing\Update;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update as MercureUpdate;

class MercureHubPublishingHelperTest extends TestCase
{
    private MercureHubPublishingHelper $helper;
    private MockObject & HubInterface $hub;

    public function setUp(): void
    {
        $this->hub = $this->createMock(HubInterface::class);
        $this->helper = new MercureHubPublishingHelper($this->hub);
    }

    #[Test]
    public function updatesAreForwardedToMercure(): void
    {
        $this->hub->expects($this->once())->method('publish')->with(
            new MercureUpdate('foo', '{"bar":"baz"}'),
        )->willReturn('');
        $this->helper->publishUpdate(Update::forTopicAndPayload('foo', ['bar' => 'baz']));
    }
}
