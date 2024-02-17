<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Doctrine;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Common\Doctrine\EntityRepositoryFactory;
use Shlinkio\Shlink\Common\Exception\InvalidArgumentException;
use ShlinkioTest\Shlink\Common\Mock\ExtendedMockRepository;
use ShlinkioTest\Shlink\Common\Mock\MockRepository;
use stdClass;

use function sprintf;

class EntityRepositoryFactoryTest extends TestCase
{
    private MockObject & ContainerInterface $container;
    private MockObject & EntityManagerInterface $em;

    public function setUp(): void
    {
        $configuration = $this->createMock(Configuration::class);
        $configuration->expects($this->once())->method('getDefaultRepositoryClassName')->willReturn(
            MockRepository::class,
        );

        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->em->expects($this->once())->method('getConfiguration')->willReturn($configuration);

        $this->container = $this->createMock(ContainerInterface::class);
        $this->container->expects($this->once())->method('get')->with(EntityManager::class)->willReturn($this->em);
    }

    /**
     * @param class-string<object> $repoClass
     */
    #[Test, DataProvider('provideValidRepoNames')]
    public function createsRequestedRepositoryClass(string $repoClass): void
    {
        $this->em->expects($this->once())->method('getClassMetadata')->with(stdClass::class)->willReturn(
            new ClassMetadata(stdClass::class),
        );

        $repoInstance = EntityRepositoryFactory::{stdClass::class}($this->container, $repoClass);

        self::assertInstanceOf($repoClass, $repoInstance);
    }

    public static function provideValidRepoNames(): iterable
    {
        yield [MockRepository::class];
        yield [ExtendedMockRepository::class];
    }

    #[Test]
    public function throwsExceptionWhenInvalidRepoIsRequested(): void
    {
        $this->em->expects($this->never())->method('getClassMetadata');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            '"%s" cannot create an instance of "%s", as it is neither an instance of or extends from "%s"',
            EntityRepositoryFactory::class,
            stdClass::class,
            MockRepository::class,
        ));

        EntityRepositoryFactory::{stdClass::class}($this->container, stdClass::class);
    }
}
