<?php

namespace Symfony\Component\Notifier\Bridge\GatewayApi\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\Notifier\Bridge\GatewayApi\GatewayApiTransport;
use Symfony\Component\Notifier\Exception\LogicException;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author Piergiuseppe Longo <piergiuseppe.longo@gmail.com>
 */
final class GatewayApiTransportTest extends TestCase
{
    public function testToStringContainsProperties()
    {
        $transport = $this->createTransport();

        $this->assertSame('gatewayapi://host.test?from=Symfony', (string) $transport);
    }

    public function testSupportsMessageInterface()
    {
        $transport = $this->createTransport();

        $this->assertTrue($transport->supports(new SmsMessage('3333333333', 'Hello!')));
        $this->assertFalse($transport->supports(new ChatMessage('Hello!')));
        $this->assertFalse($transport->supports($this->createMock(MessageInterface::class)));
    }

    public function testSendNonSmsMessageThrowsLogicException()
    {
        $transport = $this->createTransport();

        $this->expectException(LogicException::class);

        $transport->send($this->createMock(MessageInterface::class));
    }

    public function testSend()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->exactly(2))
            ->method('getStatusCode')
            ->willReturn(200);
        $response->expects($this->once())
            ->method('toArray')
            ->willReturn(['content' => ['ids' => [42]]]);

        $client = new MockHttpClient(static function () use ($response): ResponseInterface {
            return $response;
        });

        $transport = $this->createTransport($client);
        $message = new SmsMessage('3333333333', 'Hello!');
        $sentMessage = $transport->send($message);

        $this->assertInstanceOf(SentMessage::class, $sentMessage);
        $this->assertSame('42', $sentMessage->getMessageId());
    }

    private function createTransport(?HttpClientInterface $client = null): GatewayApiTransport
    {
        return (new GatewayApiTransport('authtoken', 'Symfony', $client))->setHost('host.test');
    }
}
