<?php

namespace SlackPhp\Framework\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use SlackPhp\Framework\StderrLogger;

class StderrLoggerTest extends TestCase
{
    public function testStringableMessageIsRenderedAsStringInJsonLog(): void
    {
        $stream = fopen('php://temp', 'a+');
        $logger = new StderrLogger(LogLevel::DEBUG, $stream);

        $message = new class() {
            public function __toString(): string
            {
                return 'stringable-message';
            }
        };

        $logger->log(LogLevel::ERROR, $message, ['foo' => 'bar']);

        rewind($stream);
        $line = trim((string) stream_get_contents($stream));
        $payload = json_decode($line, true);

        $this->assertSame('stringable-message', $payload['message']);
        $this->assertSame(LogLevel::ERROR, $payload['level']);
        $this->assertSame(['foo' => 'bar'], $payload['context']);
    }

    public function testNonStringLevelThrowsInvalidArgumentException(): void
    {
        $stream = fopen('php://temp', 'a+');
        $logger = new StderrLogger(LogLevel::DEBUG, $stream);

        $this->expectException(InvalidArgumentException::class);
        $logger->log([], 'hello');
    }

    public function testNonStringMessageThrowsInvalidArgumentException(): void
    {
        $stream = fopen('php://temp', 'a+');
        $logger = new StderrLogger(LogLevel::DEBUG, $stream);

        $this->expectException(InvalidArgumentException::class);
        $logger->log(LogLevel::ERROR, []);
    }
}
