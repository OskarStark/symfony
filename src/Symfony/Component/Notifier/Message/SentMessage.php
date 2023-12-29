<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Notifier\Message;

/**
 * @author Jérémy Romey <jeremy@free-agent.fr>
 */
class SentMessage
{
    private MessageInterface $original;
    private string $transport;
    private ?string $messageId = null;
    private array $info = [];

    public function __construct(MessageInterface $original, string $transport)
    {
        $this->original = $original;
        $this->transport = $transport;
    }

    public function getOriginalMessage(): MessageInterface
    {
        return $this->original;
    }

    public function getTransport(): string
    {
        return $this->transport;
    }

    public function setMessageId(string $id): void
    {
        $this->messageId = $id;
    }

    public function getMessageId(): ?string
    {
        return $this->messageId;
    }

    public function setInfo(int $responseStatusCode, array $responseHeaders = [], array $body = []): void
    {
        $this->info = [
            'response' => [
                'status_code' => $responseStatusCode,
                'headers' => $responseHeaders,
                'body' => $body,
            ]
        ];
    }

    /**
     * @return array{response: array{status_code: int, headers: array, body: array}
     */
    public function getInfo(): array
    {
        return $this->info;
    }
}
