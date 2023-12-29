<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Notifier\Tests\Message;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Message\SmsMessage;

/**
 * @author Oskar Stark <oskarstark@googlemail.com>
 */
class SentMessageTest extends TestCase
{
    public function testCanBeConstructed()
    {
        $sms = new SmsMessage('+3312345678', 'subject', 'from');

        $sentMessage = new SentMessage($sms, 'transport');

        $this->assertSame($sms, $sentMessage->getOriginalMessage());
        $this->assertSame('transport', $sentMessage->getTransport());
        $this->assertNull($sentMessage->getMessageId());
    }

    public function testCanBeConstructedWithMessageId()
    {
        $sms = new SmsMessage('+3312345678', 'subject', 'from');

        $sentMessage = new SentMessage($sms, 'transport', 'id');

        $this->assertSame($sms, $sentMessage->getOriginalMessage());
        $this->assertSame('transport', $sentMessage->getTransport());
        $this->assertSame('id', $sentMessage->getMessageId());
    }

    public function testMessageIdCanBeOverwritten()
    {
        $sms = new SmsMessage('+3312345678', 'subject', 'from');

        $sentMessage = new SentMessage($sms, 'transport', 'id');

        $this->assertSame($sms, $sentMessage->getOriginalMessage());
        $this->assertSame('transport', $sentMessage->getTransport());
        $this->assertSame('id', $sentMessage->getMessageId());

        $sentMessage->setMessageId('new-id');

        $this->assertSame('new-id', $sentMessage->getMessageId());
    }
}
