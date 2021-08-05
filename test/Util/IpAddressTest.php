<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Util;

use PHPUnit\Framework\TestCase;
use ReflectionObject;
use Shlinkio\Shlink\Common\Exception\InvalidArgumentException;
use Shlinkio\Shlink\Common\Util\IpAddress;

use function sprintf;
use function trim;

class IpAddressTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideInvalidAddresses
     */
    public function exceptionIsThrownWhenTryingToParseInvalidIp(string $invalidAddress): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Provided IP "%s" is invalid', $invalidAddress));

        IpAddress::fromString($invalidAddress);
    }

    public function provideInvalidAddresses(): iterable
    {
        yield [''];
        yield ['1'];
        yield ['1.1'];
        yield ['1.1.1'];
        yield ['1.1.1.1.1'];
        yield ['1.1.1.1.1.1'];
    }

    /**
     * @test
     * @dataProvider provideValidAddresses
     */
    public function validAddressesAreProperlyParsed(
        string $validAddress,
        string $firstOctet,
        string $secondOctet,
        string $thirdOctet,
        string $fourthOctet,
    ): void {
        $address = IpAddress::fromString($validAddress);

        self::assertEquals($firstOctet, $this->getPropFromIpAddress($address, 'firstOctet'));
        self::assertEquals($secondOctet, $this->getPropFromIpAddress($address, 'secondOctet'));
        self::assertEquals($thirdOctet, $this->getPropFromIpAddress($address, 'thirdOctet'));
        self::assertEquals($fourthOctet, $this->getPropFromIpAddress($address, 'fourthOctet'));
        self::assertEquals(trim($validAddress), (string) $address);
    }

    /**
     * @test
     * @dataProvider provideValidAddresses
     */
    public function addressesRemoveLastOctetWhenAnonymized(
        string $validAddress,
        string $firstOctet,
        string $secondOctet,
        string $thirdOctet,
    ): void {
        $anonymizedAddress = IpAddress::fromString($validAddress)->getAnonymizedCopy();

        self::assertEquals($firstOctet, $this->getPropFromIpAddress($anonymizedAddress, 'firstOctet'));
        self::assertEquals($secondOctet, $this->getPropFromIpAddress($anonymizedAddress, 'secondOctet'));
        self::assertEquals($thirdOctet, $this->getPropFromIpAddress($anonymizedAddress, 'thirdOctet'));
        self::assertEquals(0, $this->getPropFromIpAddress($anonymizedAddress, 'fourthOctet'));
    }

    public function provideValidAddresses(): iterable
    {
        yield ['1.1.1.1', '1', '1', '1', '1'];
        yield ['2.2.2.2', '2', '2', '2', '2'];
        yield ['1.2.3.4', '1', '2', '3', '4'];
        yield ['192.168.1.254', '192', '168', '1', '254'];
        yield ['8.8.8.8', '8', '8', '8', '8'];
        yield ['8.8.4.4', '8', '8', '4', '4'];
        yield ['  8.8.4.4   ', '8', '8', '4', '4'];
    }

    private function getPropFromIpAddress(IpAddress $address, string $propName): mixed
    {
        $ref = new ReflectionObject($address);
        $prop = $ref->getProperty($propName);
        $prop->setAccessible(true);

        return $prop->getValue($address);
    }
}
