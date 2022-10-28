<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Doctrine\Mapping;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\FileLocator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Doctrine\Mapping\EnhancedPHPDriver;

class EnhancedPHPDriverTest extends TestCase
{
    private MockObject & FileLocator $loader;
    private MockObject & ClassMetadata $meta;

    public function setUp(): void
    {
        $this->loader = $this->createMock(FileLocator::class);
        $this->loader->method('findMappingFile')->willReturn(
            __DIR__ . '/../../../test-resources/mapping/fake.mapping.php',
        );
        $this->meta = $this->createMock(ClassMetadata::class);
    }

    /**
     * @test
     * @dataProvider provideFuncStyle
     */
    public function internalFunctionIsInvokedBasedOnFunctionalStyle(array $args, InvokedCount $metaExpectedCalls): void
    {
        $this->meta->expects($metaExpectedCalls)->method('getFieldNames');

        $driver = new EnhancedPHPDriver($this->loader, [], ...$args);
        $driver->loadMetadataForClass('', $this->meta);
    }

    public function provideFuncStyle(): iterable
    {
        yield 'func style' => [[true], $this->once()];
        yield 'no func style' => [[false], $this->never()];
        yield 'default func style' => [[], $this->never()];
    }
}
