<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Doctrine\Mapping;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\FileLocator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Doctrine\Mapping\EnhancedPHPDriver;
use stdClass;

class EnhancedPHPDriverTest extends TestCase
{
    private Stub & FileLocator $loader;
    /** @var MockObject & ClassMetadata<stdClass> */
    private MockObject & ClassMetadata $meta;

    public function setUp(): void
    {
        $this->loader = $this->createStub(FileLocator::class);
        $this->loader->method('findMappingFile')->willReturn(
            __DIR__ . '/../../../test-resources/mapping/fake.mapping.php',
        );
        $this->meta = $this->createMock(ClassMetadata::class);
    }

    #[Test, DataProvider('provideFuncStyle')]
    public function internalFunctionIsInvokedBasedOnFunctionalStyle(array $args, InvokedCount $metaExpectedCalls): void
    {
        $this->meta->expects($metaExpectedCalls)->method('getFieldNames');

        $driver = new EnhancedPHPDriver($this->loader, [], ...$args);
        $driver->loadMetadataForClass(stdClass::class, $this->meta);
    }

    public static function provideFuncStyle(): iterable
    {
        yield 'func style' => [[true], new InvokedCount(1)];
        yield 'no func style' => [[false], new InvokedCount(0)];
        yield 'default func style' => [[], new InvokedCount(0)];
    }
}
