<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Doctrine;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\Mapping\Driver\PHPDriver;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use ReflectionObject;
use Shlinkio\Shlink\Common\Doctrine\ConfigurationFactory;
use Shlinkio\Shlink\Common\Doctrine\Type\ChronosDateTimeType;
use ShlinkioTest\Shlink\Common\Repository\CustomRepository;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

use function array_filter;
use function array_merge;
use function array_merge_recursive;

use const ARRAY_FILTER_USE_KEY;

class ConfigurationFactoryTest extends TestCase
{
    private ConfigurationFactory $factory;

    public function setUp(): void
    {
        if (Type::hasType(ChronosDateTimeType::CHRONOS_DATETIME)) {
            $typeRegistry = Type::getTypeRegistry();
            $ref = new ReflectionObject($typeRegistry);
            $instancesProp = $ref->getProperty('instances');
            $withoutChronosType = array_filter(
                $typeRegistry->getMap(),
                fn (string $key): bool => $key !== ChronosDateTimeType::CHRONOS_DATETIME,
                ARRAY_FILTER_USE_KEY,
            );
            $instancesProp->setValue($typeRegistry, $withoutChronosType);
        }

        $this->factory = new ConfigurationFactory();
    }

    #[Test, DataProvider('provideConfig')]
    public function serviceIsCreated(
        array $config,
        int $expectedAutoGenerateProxies,
        string $expectedDefaultRepo,
    ): void {
        $sm = new ServiceManager(['services' => [
            'config' => $config,
            CacheItemPoolInterface::class => new ArrayAdapter(),
        ]]);

        self::assertFalse(Type::hasType(ChronosDateTimeType::CHRONOS_DATETIME));
        $config = ($this->factory)($sm);

        self::assertTrue(Type::hasType(ChronosDateTimeType::CHRONOS_DATETIME));
        self::assertEquals($expectedAutoGenerateProxies, $config->getAutoGenerateProxyClasses());
        self::assertEquals(__DIR__, $config->getProxyDir());
        self::assertEquals($expectedDefaultRepo, $config->getDefaultRepositoryClassName());

        /** @var PHPDriver $metaDriver */
        $metaDriver = $config->getMetadataDriverImpl();
        self::assertEquals([__FILE__], $metaDriver->getLocator()->getPaths());
    }

    public static function provideConfig(): iterable
    {
        $baseConfig = [
            'entity_manager' => [
                'orm' => [
                    'types' => [
                        ChronosDateTimeType::CHRONOS_DATETIME => ChronosDateTimeType::class,
                    ],
                    'proxies_dir' => __DIR__,
                    'entities_mappings' => [__FILE__],
                ],
                'connection' => [
                    'driver' => 'pdo_sqlite',
                ],
            ],
        ];

        yield [array_merge($baseConfig, ['debug' => true]), 1, EntityRepository::class];
        yield [array_merge($baseConfig, ['debug' => '1']), 1, EntityRepository::class];
        yield [array_merge($baseConfig, ['debug' => 'true']), 1, EntityRepository::class];
        yield [array_merge($baseConfig, ['debug' => false]), 0, EntityRepository::class];
        yield [array_merge($baseConfig, ['debug' => null]), 0, EntityRepository::class];
        yield [array_merge($baseConfig, ['debug' => null]), 0, EntityRepository::class];
        yield [
            array_merge_recursive($baseConfig, [
                'entity_manager' => [
                    'orm' => ['default_repository_classname' => CustomRepository::class],
                ],
            ]),
            0,
            CustomRepository::class,
        ];
    }
}
