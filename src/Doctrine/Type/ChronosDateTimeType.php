<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Doctrine\Type;

use Cake\Chronos\Chronos;
use DateTimeInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Doctrine\DBAL\Types\Exception\InvalidType;

class ChronosDateTimeType extends DateTimeImmutableType
{
    public const CHRONOS_DATETIME = 'chronos_datetime';

    public function getName(): string
    {
        return self::CHRONOS_DATETIME;
    }

    /**
     * @throws ConversionException
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Chronos
    {
        if ($value === null) {
            return null;
        }

        $dateTime = parent::convertToPHPValue($value, $platform);
        return Chronos::instance($dateTime);
    }

    /**
     * @param T $value
     * @return (T is null ? null : string)
     * @template T
     * @throws ConversionException
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format($platform->getDateTimeFormatString());
        }

        throw InvalidType::new($value, $this->getName(), ['null', DateTimeInterface::class]);
    }
}
