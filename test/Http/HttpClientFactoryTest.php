<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Http;

use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionObject;
use Shlinkio\Shlink\Common\Http\Exception\InvalidHttpMiddlewareException;
use Shlinkio\Shlink\Common\Http\HttpClientFactory;
use stdClass;

use function fopen;

class HttpClientFactoryTest extends TestCase
{
    private const BASE_HANDLERS_COUNT = 4;

    private HttpClientFactory $factory;
    private MockObject & ContainerInterface $container;

    public function setUp(): void
    {
        $this->factory = new HttpClientFactory();
        $this->container = $this->createMock(ContainerInterface::class);
    }

    #[Test, DataProvider('provideConfig')]
    public function properlyCreatesAFactoryWithExpectedNumberOfMiddlewares(
        array $config,
        int $expectedMiddlewaresAmount,
        InvocationOrder $amountOfServiceGets,
    ): void {
        $this->container->expects($amountOfServiceGets)->method('get')->willReturnMap([
            ['some_middleware', static function (): void {
            }],
            ['config', ['http_client' => $config]],
        ]);

        $client = ($this->factory)($this->container);
        /** @var HandlerStack $handler */
        $handler = $client->getConfig('handler');
        $ref = new ReflectionObject($handler);
        $stack = $ref->getProperty('stack');
        $stack->setAccessible(true);

        self::assertCount($expectedMiddlewaresAmount + self::BASE_HANDLERS_COUNT, $stack->getValue($handler));
    }

    public static function provideConfig(): iterable
    {
        $staticMiddleware = static function (): void {
        };

        yield [[], 0, new InvokedCount(1)];
        yield [['request_middlewares' => []], 0, new InvokedCount(1)];
        yield [['response_middlewares' => []], 0, new InvokedCount(1)];
        yield [[
            'request_middlewares' => [],
            'response_middlewares' => [],
        ], 0, new InvokedCount(1)];
        yield [[
            'request_middlewares' => ['some_middleware'],
            'response_middlewares' => [],
        ], 1, new InvokedCount(2)];
        yield [[
            'request_middlewares' => [],
            'response_middlewares' => ['some_middleware'],
        ], 1, new InvokedCount(2)];
        yield [[
            'request_middlewares' => ['some_middleware'],
            'response_middlewares' => ['some_middleware'],
        ], 2, new InvokedCount(3)];
        yield [[
            'request_middlewares' => [$staticMiddleware],
            'response_middlewares' => ['some_middleware'],
        ], 2, new InvokedCount(2)];
        yield [[
            'request_middlewares' => ['some_middleware', $staticMiddleware],
            'response_middlewares' => [$staticMiddleware, 'some_middleware'],
        ], 4, new InvokedCount(3)];
    }

    #[Test, DataProvider('provideInvalidMiddlewares')]
    public function exceptionIsThrownWhenNonCallableStaticMiddlewaresAreProvided(mixed $middleware): void
    {

        $this->container->expects($this->once())->method('get')->with('config')->willReturn(['http_client' => [
            'request_middlewares' => [$middleware],
        ]]);

        $this->expectException(InvalidHttpMiddlewareException::class);

        ($this->factory)($this->container);
    }

    #[Test, DataProvider('provideInvalidMiddlewares')]
    public function exceptionIsThrownWhenNonCallableServiceMiddlewaresAreProvided(mixed $middleware): void
    {
        $this->container->expects($this->exactly(2))->method('get')->willReturnMap([
            ['some_middleware', $middleware],
            ['config', ['http_client' => [
                'response_middlewares' => ['some_middleware'],
            ]]],
        ]);

        $this->expectException(InvalidHttpMiddlewareException::class);

        ($this->factory)($this->container);
    }

    public static function provideInvalidMiddlewares(): iterable
    {
        yield [1234];
        yield [new stdClass()];
        yield [[]];
        yield [fopen('php://memory', 'rb+')];
    }
}
