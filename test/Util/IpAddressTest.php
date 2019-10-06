<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\Util;

use PHPUnit\Framework\TestCase;
use ReflectionObject;
use Shlinkio\Shlink\Common\Exception\InvalidArgumentException;
use Shlinkio\Shlink\Common\Util\IpAddress;

use function sprintf;

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
        string $fourthOctet
    ): void {
        $address = IpAddress::fromString($validAddress);

        $this->assertEquals($firstOctet, $this->getPropFromIpAddress($address, 'firstOctet'));
        $this->assertEquals($secondOctet, $this->getPropFromIpAddress($address, 'secondOctet'));
        $this->assertEquals($thirdOctet, $this->getPropFromIpAddress($address, 'thirdOctet'));
        $this->assertEquals($fourthOctet, $this->getPropFromIpAddress($address, 'fourthOctet'));
        $this->assertEquals($validAddress, (string) $address);
    }

    /**
     * @test
     * @dataProvider provideValidAddresses
     */
    public function addressesRemoveLastOctetWhenObfuscated(
        string $validAddress,
        string $firstOctet,
        string $secondOctet,
        string $thirdOctet
    ): void {
        $obfuscatedAddress = IpAddress::fromString($validAddress)->getObfuscatedCopy();

        $this->assertEquals($firstOctet, $this->getPropFromIpAddress($obfuscatedAddress, 'firstOctet'));
        $this->assertEquals($secondOctet, $this->getPropFromIpAddress($obfuscatedAddress, 'secondOctet'));
        $this->assertEquals($thirdOctet, $this->getPropFromIpAddress($obfuscatedAddress, 'thirdOctet'));
        $this->assertEquals(0, $this->getPropFromIpAddress($obfuscatedAddress, 'fourthOctet'));
    }

    public function provideValidAddresses(): iterable
    {
        yield ['1.1.1.1', '1', '1', '1', '1'];
        yield ['2.2.2.2', '2', '2', '2', '2'];
        yield ['1.2.3.4', '1', '2', '3', '4'];
        yield ['192.168.1.254', '192', '168', '1', '254'];
        yield ['8.8.8.8', '8', '8', '8', '8'];
        yield ['8.8.4.4', '8', '8', '4', '4'];
    }

    private function getPropFromIpAddress(IpAddress $address, string $propName)
    {
        $ref = new ReflectionObject($address);
        $prop = $ref->getProperty($propName);
        $prop->setAccessible(true);

        return $prop->getValue($address);
    }
}
