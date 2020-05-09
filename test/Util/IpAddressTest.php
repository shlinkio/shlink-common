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
    public function addressesRemoveLastOctetWhenAnonymized(
        string $validAddress,
        string $firstOctet,
        string $secondOctet,
        string $thirdOctet
    ): void {
        $anonymizedAddress = IpAddress::fromString($validAddress)->getAnonymizedCopy();

        $this->assertEquals($firstOctet, $this->getPropFromIpAddress($anonymizedAddress, 'firstOctet'));
        $this->assertEquals($secondOctet, $this->getPropFromIpAddress($anonymizedAddress, 'secondOctet'));
        $this->assertEquals($thirdOctet, $this->getPropFromIpAddress($anonymizedAddress, 'thirdOctet'));
        $this->assertEquals(0, $this->getPropFromIpAddress($anonymizedAddress, 'fourthOctet'));
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

    /**
     * @return mixed
     */
    private function getPropFromIpAddress(IpAddress $address, string $propName)
    {
        $ref = new ReflectionObject($address);
        $prop = $ref->getProperty($propName);
        $prop->setAccessible(true);

        return $prop->getValue($address);
    }
}
