<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Doctrine;

use Doctrine\DBAL\Driver\PDO\SQLite\Driver as SQLiteDriver;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\Mapping\Driver\PHPDriver;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use Shlinkio\Shlink\Common\Doctrine\EntityManagerFactory;
use Shlinkio\Shlink\Common\Doctrine\Type\ChronosDateTimeType;
use ShlinkioTest\Shlink\Common\Repository\CustomRepository;

use function array_filter;
use function array_merge;
use function array_merge_recursive;

use const ARRAY_FILTER_USE_KEY;

class EntityManagerFactoryTest extends TestCase
{
    private EntityManagerFactory $factory;

    public function setUp(): void
    {
        if (Type::hasType(ChronosDateTimeType::CHRONOS_DATETIME)) {
            $typeRegistry = Type::getTypeRegistry();
            $ref = new ReflectionObject($typeRegistry);
            $instancesProp = $ref->getProperty('instances');
            $instancesProp->setAccessible(true);
            $withoutChronosType = array_filter(
                $typeRegistry->getMap(),
                fn (string $key): bool => $key !== ChronosDateTimeType::CHRONOS_DATETIME,
                ARRAY_FILTER_USE_KEY,
            );
            $instancesProp->setValue($typeRegistry, $withoutChronosType);
        }

        $this->factory = new EntityManagerFactory();
    }

    /**
     * @test
     * @dataProvider provideConfig
     */
    public function serviceIsCreated(array $config, int $expectedAutoGenerateProxies, string $expectedDefaultRepo): void
    {
        $sm = new ServiceManager(['services' => [
            'config' => $config,
        ]]);

        self::assertFalse(Type::hasType(ChronosDateTimeType::CHRONOS_DATETIME));
        $em = ($this->factory)($sm);

        self::assertTrue(Type::hasType(ChronosDateTimeType::CHRONOS_DATETIME));
        self::assertEquals($expectedAutoGenerateProxies, $em->getConfiguration()->getAutoGenerateProxyClasses());
        self::assertEquals(__DIR__, $em->getConfiguration()->getProxyDir());
        self::assertInstanceOf(SQLiteDriver::class, $em->getConnection()->getDriver());
        self::assertEquals($expectedDefaultRepo, $em->getConfiguration()->getDefaultRepositoryClassName());

        /** @var PHPDriver $metaDriver */
        $metaDriver = $em->getConfiguration()->getMetadataDriverImpl();
        self::assertEquals([__FILE__], $metaDriver->getLocator()->getPaths());
    }

    public function provideConfig(): iterable
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
