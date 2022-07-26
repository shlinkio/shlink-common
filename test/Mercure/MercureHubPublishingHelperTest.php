<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Mercure;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Shlinkio\Shlink\Common\Mercure\MercureHubPublishingHelper;
use Shlinkio\Shlink\Common\UpdatePublishing\Update;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update as MercureUpdate;

class MercureHubPublishingHelperTest extends TestCase
{
    use ProphecyTrait;

    private MercureHubPublishingHelper $helper;
    private ObjectProphecy $hub;

    public function setUp(): void
    {
        $this->hub = $this->prophesize(HubInterface::class);
        $this->helper = new MercureHubPublishingHelper($this->hub->reveal());
    }

    /** @test */
    public function updatesAreForwardedToMercure(): void
    {
        $mercurePublish = $this->hub->publish(new MercureUpdate('foo', '{"bar":"baz"}'))->willReturn('');

        $this->helper->publishUpdate(Update::forTopicAndPayload('foo', ['bar' => 'baz']));

        $mercurePublish->shouldHaveBeenCalledOnce();
    }
}
