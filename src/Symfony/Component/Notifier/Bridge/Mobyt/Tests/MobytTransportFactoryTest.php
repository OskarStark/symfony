<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Notifier\Bridge\Mobyt\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Notifier\Bridge\Mobyt\MobytTransportFactory;
use Symfony\Component\Notifier\Exception\IncompleteDsnException;
use Symfony\Component\Notifier\Exception\InvalidArgumentException;
use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\Dsn;

/**
 * @author Oskar Stark <oskarstark@googlemail.com>
 */
final class MobytTransportFactoryTest extends TestCase
{
    public function testCreateWithDsn()
    {
        $factory = $this->createFactory();

        $transport = $factory->create(Dsn::fromString('mobyt://accountSid:authToken@host.test?from=testFrom'));

        $this->assertSame('mobyt://host.test?from=testFrom&message_type=LL', (string) $transport);
    }

    public function testCreateWithDsnAndDifferentMessageType()
    {
        $factory = $this->createFactory();

        $transport = $factory->create(Dsn::fromString('mobyt://accountSid:authToken@host.test?from=testFrom&message_type=N'));

        $this->assertSame('mobyt://host.test?from=testFrom&message_type=N', (string) $transport);
    }

    public function testCreateWithDsnAndUnknownMessageTypeThrowsInvalidArgumentException()
    {
        $factory = $this->createFactory();

        $this->expectException(InvalidArgumentException::class);

        $factory->create(Dsn::fromString('mobyt://accountSid:authToken@host.test?from=testFrom&message_type=foo'));
    }

    public function testCreateWithOldTypeQualityParameterNameInDsn()
    {
        $factory = $this->createFactory();

        $this->expectException(IncompleteDsnException::class);
        $this->expectExceptionMessage('Mobyt DSN has changed since 5.3, use "message_type" instead of "type_quality" parameter.');

        $factory->create(Dsn::fromString('mobyt://accountSid:authToken@host.test?from=testFrom&type_quality=N'));
    }

    public function testCreateWithMissingOptionFromThrowsIncompleteDsnException()
    {
        $factory = $this->createFactory();

        $this->expectException(IncompleteDsnException::class);

        $factory->create(Dsn::fromString('mobyt://accountSid:authToken@host'));
    }

    public function testCreateWithNoTokenThrowsIncompleteDsnException()
    {
        $factory = $this->createFactory();

        $this->expectException(IncompleteDsnException::class);

        $factory->create(Dsn::fromString('mobyt://host.test?from=testFrom'));
    }

    public function testSupportsReturnsTrueWithSupportedScheme()
    {
        $factory = $this->createFactory();

        $this->assertTrue($factory->supports(Dsn::fromString('mobyt://accountSid:authToken@host.test?from=testFrom')));
    }

    public function testSupportsReturnsFalseWithUnsupportedScheme()
    {
        $factory = $this->createFactory();

        $this->assertFalse($factory->supports(Dsn::fromString('somethingElse://accountSid:authToken@host.test?from=testFrom')));
    }

    public function testUnsupportedSchemeThrowsUnsupportedSchemeException()
    {
        $factory = $this->createFactory();

        $this->expectException(UnsupportedSchemeException::class);

        $factory->create(Dsn::fromString('somethingElse://accountSid:authToken@host.test?from=testFrom'));
    }

    public function testUnsupportedSchemeThrowsUnsupportedSchemeExceptionEvenIfRequiredOptionIsMissing()
    {
        $factory = $this->createFactory();

        $this->expectException(UnsupportedSchemeException::class);

        // unsupported scheme and missing "from" option
        $factory->create(Dsn::fromString('somethingElse://accountSid:authToken@host.test'));
    }

    private function createFactory(): MobytTransportFactory
    {
        return new MobytTransportFactory();
    }
}
