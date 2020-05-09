<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Util;

use Shlinkio\Shlink\Common\Exception\InvalidArgumentException;

use function count;
use function explode;
use function implode;
use function sprintf;
use function trim;

final class IpAddress
{
    private const IPV4_PARTS_COUNT = 4;
    private const ANONYMIZED_OCTET = '0';
    public const LOCALHOST = '127.0.0.1';

    private string $firstOctet;
    private string $secondOctet;
    private string $thirdOctet;
    private string $fourthOctet;

    private function __construct(string $firstOctet, string $secondOctet, string $thirdOctet, string $fourthOctet)
    {
        $this->firstOctet = $firstOctet;
        $this->secondOctet = $secondOctet;
        $this->thirdOctet = $thirdOctet;
        $this->fourthOctet = $fourthOctet;
    }

    /**
     * @return IpAddress
     * @throws InvalidArgumentException
     */
    public static function fromString(string $address): self
    {
        $address = trim($address);
        $parts = explode('.', $address);
        if (count($parts) !== self::IPV4_PARTS_COUNT) {
            throw new InvalidArgumentException(sprintf('Provided IP "%s" is invalid', $address));
        }

        return new self(...$parts);
    }

    public function getAnonymizedCopy(): self
    {
        return new self(
            $this->firstOctet,
            $this->secondOctet,
            $this->thirdOctet,
            self::ANONYMIZED_OCTET,
        );
    }

    /** @deprecated Use getAnonymizedCopy instead */
    public function getObfuscatedCopy(): self
    {
        return $this->getAnonymizedCopy();
    }

    public function __toString(): string
    {
        return implode('.', [
            $this->firstOctet,
            $this->secondOctet,
            $this->thirdOctet,
            $this->fourthOctet,
        ]);
    }
}
