<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Http\Exception;

use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Http\Exception\InvalidHttpMiddlewareException;
use stdClass;

class InvalidHttpMiddlewareExceptionTest extends TestCase
{
    /**
     * @param mixed $middleware
     * @test
     * @dataProvider provideMessages
     */
    public function exceptionIsCreatedAsExpected($middleware, string $expectedMessage): void
    {
        $e = InvalidHttpMiddlewareException::fromMiddleware($middleware);
        $this->assertEquals($expectedMessage, $e->getMessage());
    }

    public function provideMessages(): iterable
    {
        yield [new stdClass(), 'Provided middleware does not have a valid type. Expected callable, stdClass provided'];
        yield ['foobar', 'Provided middleware does not have a valid type. Expected callable, string provided'];
        yield [23, 'Provided middleware does not have a valid type. Expected callable, integer provided'];
    }
}
