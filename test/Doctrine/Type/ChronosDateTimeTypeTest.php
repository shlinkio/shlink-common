<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Doctrine\Type;

use Cake\Chronos\Chronos;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Common\Doctrine\Type\ChronosDateTimeType;
use stdClass;

class ChronosDateTimeTypeTest extends TestCase
{
    private ChronosDateTimeType $type;

    protected function setUp(): void
    {
        if (! Type::hasType(ChronosDateTimeType::CHRONOS_DATETIME)) {
            Type::addType(ChronosDateTimeType::CHRONOS_DATETIME, ChronosDateTimeType::class);
        } else {
            Type::overrideType(ChronosDateTimeType::CHRONOS_DATETIME, ChronosDateTimeType::class);
        }

        $this->type = Type::getType(ChronosDateTimeType::CHRONOS_DATETIME); // @phpstan-ignore-line
    }

    #[Test]
    public function nameIsReturned(): void
    {
        self::assertEquals(ChronosDateTimeType::CHRONOS_DATETIME, $this->type->getName());
    }

    /**
     * @param class-string<Chronos>|null $expected
     */
    #[Test, DataProvider('provideValues')]
    public function valueIsConverted(string|null $value, string|null $expected): void
    {
        $platform = $this->createStub(AbstractPlatform::class);
        $platform->method('getDateTimeFormatString')->willReturn('Y-m-d H:i:s');

        $result = $this->type->convertToPHPValue($value, $platform);

        if ($expected === null) {
            self::assertNull($result);
        } else {
            self::assertInstanceOf($expected, $result);
        }
    }

    public static function provideValues(): iterable
    {
        yield 'null date' => [null, null];
        yield 'human friendly date' => ['now', Chronos::class];
        yield 'numeric date' => ['2017-01-01', Chronos::class];
    }

    #[Test, DataProvider('providePhpValues')]
    public function valueIsConvertedToDatabaseFormat(DateTimeInterface|null $value, string|null $expected): void
    {
        $platform = $this->createStub(AbstractPlatform::class);
        $platform->method('getDateTimeFormatString')->willReturn('Y-m-d');

        self::assertEquals($expected, $this->type->convertToDatabaseValue($value, $platform));
    }

    public static function providePhpValues(): iterable
    {
        yield 'null date' => [null, null];
        yield 'DateTimeImmutable date' => [new DateTimeImmutable('2017-01-01'), '2017-01-01'];
        yield 'Chronos date' => [Chronos::parse('2017-02-01'), '2017-02-01'];
        yield 'DateTime date' => [new DateTime('2017-03-01'), '2017-03-01'];
    }

    #[Test]
    public function exceptionIsThrownIfInvalidValueIsParsedToDatabase(): void
    {
        $this->expectException(ConversionException::class);
        $this->type->convertToDatabaseValue(new stdClass(), $this->createStub(AbstractPlatform::class));
    }
}
