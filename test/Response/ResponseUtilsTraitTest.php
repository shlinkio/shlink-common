<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Response;

use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Response\ResponseUtilsTrait;

class ResponseUtilsTraitTest extends TestCase
{
    use ResponseUtilsTrait;

    /**
     * @test
     * @dataProvider provideFiles
     */
    public function expectedBinaryResponsesAreGenerated(
        string $expectedType,
        string $expectedLength,
        string $path
    ): void {
        $resp = $this->generateBinaryResponse($path);

        self::assertStringContainsString($expectedType, $resp->getHeaderLine('Content-Type'));
        self::assertStringContainsString($expectedLength, $resp->getHeaderLine('Content-Length'));
    }

    public function provideFiles(): iterable
    {
        yield ['image/png', '2433', __DIR__ . '/../../test-resources/shlink-logo.png'];
        yield ['text/plain', '20', __DIR__ . '/../../test-resources/text-file.txt'];
    }

    /** @test */
    public function binaryResponsesIncludeExtraHeaders(): void
    {
        $resp = $this->generateBinaryResponse(__DIR__ . '/../../test-resources/shlink-logo.png', [
            'foo' => 'bar',
            'baz' => 'foo',
        ]);
        $headers = $resp->getHeaders();

        self::assertArrayHasKey('foo', $headers);
        self::assertArrayHasKey('baz', $headers);
    }
}
