<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Doctrine\Mapping;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\FileLocator;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Shlinkio\Shlink\Common\Doctrine\Mapping\EnhancedPHPDriver;

class EnhancedPHPDriverTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $loader;
    private ObjectProphecy $meta;

    public function setUp(): void
    {
        $this->loader = $this->prophesize(FileLocator::class);
        $this->loader->findMappingFile(Argument::any())->willReturn(
            __DIR__ . '/../../../test-resources/mapping/fake.mapping.php',
        );
        $this->meta = $this->prophesize(ClassMetadata::class);
    }

    /**
     * @test
     * @dataProvider provideFuncStyle
     */
    public function internalFunctionIsInvokedBasedOnFunctionalStyle(array $args, int $metaExpectedCalls): void
    {
        $metaMethod = $this->meta->getFieldNames();

        $driver = new EnhancedPHPDriver($this->loader->reveal(), [], ...$args);
        $driver->loadMetadataForClass('', $this->meta->reveal());

        $metaMethod->shouldHaveBeenCalledTimes($metaExpectedCalls);
    }

    public function provideFuncStyle(): iterable
    {
        yield 'func style' => [[true], 1];
        yield 'no func style' => [[false], 0];
        yield 'default func style' => [[], 0];
    }
}
