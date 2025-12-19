<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Doctrine;

use Doctrine\DBAL\Driver\PDO\SQLite\Driver as SQLiteDriver;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use Shlinkio\Shlink\Common\Doctrine\EntityManagerFactory;
use Shlinkio\Shlink\Common\Doctrine\Type\ChronosDateTimeType;
use ShlinkioTest\Shlink\Common\Repository\CustomRepository;
use stdClass;

use function array_merge;
use function array_merge_recursive;
use function count;

class EntityManagerFactoryTest extends TestCase
{
    private EntityManagerFactory $factory;

    public function setUp(): void
    {
        $this->factory = new EntityManagerFactory();
    }

    #[Test, DataProvider('provideConfig')]
    public function serviceIsCreated(
        array $config,
        int $expectedListeners,
    ): void {
        $ormConfig = new Configuration();
        $ormConfig->setMetadataDriverImpl($this->createStub(MappingDriver::class));
        $ormConfig->setProxyDir(__DIR__);
        $ormConfig->setProxyNamespace('DoctrineProxies');
        $ormConfig->enableNativeLazyObjects(true);

        $sm = new ServiceManager(['services' => [
            'config' => $config,
            'foo_listener' => new stdClass(),
            'bar_listener' => new stdClass(),
            'baz_listener' => new stdClass(),
            Configuration::class => $ormConfig,
        ]]);

        $em = ($this->factory)($sm);
        self::assertInstanceOf(SQLiteDriver::class, $em->getConnection()->getDriver());

        $events = $em->getEventManager();
        $ref = new ReflectionObject($events);
        $prop = $ref->getProperty('listeners');
        $prop->setAccessible(true);
        $listeners = $prop->getValue($events);

        $listenersCount = 0;
        foreach ($listeners as $list) {
            $listenersCount += count($list);
        }

        self::assertEquals($expectedListeners, $listenersCount);
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

        yield [array_merge($baseConfig, ['debug' => true]), 0];
        yield [array_merge($baseConfig, ['debug' => '1']), 0];
        yield [array_merge($baseConfig, ['debug' => 'true']), 0];
        yield [array_merge($baseConfig, ['debug' => false]), 0];
        yield [array_merge($baseConfig, ['debug' => null]), 0];
        yield [array_merge($baseConfig, ['debug' => null]), 0];
        yield [
            array_merge_recursive($baseConfig, [
                'entity_manager' => [
                    'orm' => ['default_repository_classname' => CustomRepository::class],
                ],
            ]),
            0,
        ];
        yield [
            array_merge_recursive($baseConfig, [
                'entity_manager' => [
                    'orm' => [
                        'listeners' => [
                            Events::postFlush => ['foo_listener', 'bar_listener'],
                            Events::prePersist => ['foo_listener', 'bar_listener', 'baz_listener'],
                        ],
                    ],
                ],
            ]),
            5,
        ];
    }
}
