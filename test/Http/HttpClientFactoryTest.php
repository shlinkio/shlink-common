<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Http;

use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use ReflectionObject;
use Shlinkio\Shlink\Common\Http\Exception\InvalidHttpMiddlewareException;
use Shlinkio\Shlink\Common\Http\HttpClientFactory;
use stdClass;

use function fopen;

class HttpClientFactoryTest extends TestCase
{
    use ProphecyTrait;

    private const BASE_HANDLERS_COUNT = 4;

    private HttpClientFactory $factory;
    private ObjectProphecy $container;

    public function setUp(): void
    {
        $this->factory = new HttpClientFactory();
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    /**
     * @test
     * @dataProvider provideConfig
     */
    public function properlyCreatesAFactoryWithExpectedNumberOfMiddlewares(
        array $config,
        int $expectedMiddlewaresAmount
    ): void {
        $this->container->get('some_middleware')->willReturn(static function (): void {
        });
        $getConfig = $this->container->get('config')->willReturn(['http_client' => $config]);

        $client = ($this->factory)($this->container->reveal());
        /** @var HandlerStack $handler */
        $handler = $client->getConfig('handler');
        $ref = new ReflectionObject($handler);
        $stack = $ref->getProperty('stack');
        $stack->setAccessible(true);

        $this->assertCount($expectedMiddlewaresAmount + self::BASE_HANDLERS_COUNT, $stack->getValue($handler));
        $getConfig->shouldHaveBeenCalledOnce();
    }

    public function provideConfig(): iterable
    {
        $staticMiddleware = static function (): void {
        };

        yield [[], 0];
        yield [['request_middlewares' => []], 0];
        yield [['response_middlewares' => []], 0];
        yield [[
            'request_middlewares' => [],
            'response_middlewares' => [],
        ], 0];
        yield [[
            'request_middlewares' => ['some_middleware'],
            'response_middlewares' => [],
        ], 1];
        yield [[
            'request_middlewares' => [],
            'response_middlewares' => ['some_middleware'],
        ], 1];
        yield [[
            'request_middlewares' => ['some_middleware'],
            'response_middlewares' => ['some_middleware'],
        ], 2];
        yield [[
            'request_middlewares' => [$staticMiddleware],
            'response_middlewares' => ['some_middleware'],
        ], 2];
        yield [[
            'request_middlewares' => ['some_middleware', $staticMiddleware],
            'response_middlewares' => [$staticMiddleware, 'some_middleware'],
        ], 4];
    }

    /**
     * @param mixed $middleware
     * @test
     * @dataProvider provideInvalidMiddlewares
     */
    public function exceptionIsThrownWhenNonCallableStaticMiddlewaresAreProvided($middleware): void
    {
        $getService = $this->container->get('some_middleware')->willReturn(static function (): void {
        });
        $getConfig = $this->container->get('config')->willReturn(['http_client' => [
            'request_middlewares' => [$middleware],
        ]]);

        $this->expectException(InvalidHttpMiddlewareException::class);
        $getService->shouldNotBeCalled();
        $getConfig->shouldBeCalledOnce();

        ($this->factory)($this->container->reveal());
    }

    /**
     * @param mixed $middleware
     * @test
     * @dataProvider provideInvalidMiddlewares
     */
    public function exceptionIsThrownWhenNonCallableServiceMiddlewaresAreProvided($middleware): void
    {
        $getService = $this->container->get('some_middleware')->willReturn($middleware);
        $getConfig = $this->container->get('config')->willReturn(['http_client' => [
            'response_middlewares' => ['some_middleware'],
        ]]);

        $this->expectException(InvalidHttpMiddlewareException::class);
        $getService->shouldBeCalledOnce();
        $getConfig->shouldBeCalledOnce();

        ($this->factory)($this->container->reveal());
    }

    public function provideInvalidMiddlewares(): iterable
    {
        yield [1234];
        yield [new stdClass()];
        yield [[]];
        yield [fopen('php://memory', 'rb+')];
    }
}
