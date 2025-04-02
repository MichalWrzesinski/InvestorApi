<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\Enum\SymbolTypeEnum;
use App\Message\UpdateRatesMessage;
use App\MessageHandler\UpdateRatesHandler;
use App\State\Processor\ExchangeRate\ProcessorInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

final class UpdateRatesHandlerTest extends TestCase
{
    public function testHandlesSupportedProcessor(): void
    {
        $processor = $this->createMock(ProcessorInterface::class);
        $bus = $this->createMock(MessageBusInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $message = new UpdateRatesMessage('CRYPTO', 'BINANCE', 'BTC', 'USDT');

        $processor->method('supports')->willReturn(true);
        $processor->expects($this->once())
            ->method('update')
            ->with(SymbolTypeEnum::CRYPTO, 'BTC', 'USDT');

        $bus->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(fn (UpdateRatesMessage $m) => 'CRYPTO' === $m->type
                    && 'BINANCE' === $m->processor
                    && 'BTC' === $m->base
                    && 'USDT' === $m->quote
                ),
                $this->callback(fn (array $stamps) => $stamps[0] instanceof DelayStamp
                    && 60000 === $stamps[0]->getDelay()
                )
            )
            ->willReturn(new Envelope(new \stdClass()));

        $handler = new UpdateRatesHandler([$processor], $bus, $logger);
        $handler($message);
    }

    public function testThrowsWhenNoProcessorSupports(): void
    {
        $processor = $this->createMock(ProcessorInterface::class);
        $processor->method('supports')->willReturn(false);

        $bus = $this->createMock(MessageBusInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $handler = new UpdateRatesHandler([$processor], $bus, $logger);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No processor support: BINANCE');

        $message = new UpdateRatesMessage('CRYPTO', 'BINANCE', 'BTC', 'USDT');
        $handler($message);
    }

    public function testThrowsWhenEnumsAreNull(): void
    {
        $bus = $this->createMock(MessageBusInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $handler = new UpdateRatesHandler([], $bus, $logger);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Message must contain both type and processor enums.');

        $message = new UpdateRatesMessage('INVALID', 'INVALID', 'BTC', 'USDT');
        $handler($message);
    }

    public function testLogsErrorWhenUpdateThrows(): void
    {
        $processor = $this->createMock(ProcessorInterface::class);
        $bus = $this->createMock(MessageBusInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $message = new UpdateRatesMessage('CRYPTO', 'BINANCE', 'BTC', 'USDT');

        $processor->method('supports')->willReturn(true);
        $processor->method('update')->willThrowException(new \RuntimeException('test fail'));

        $logger->expects($this->once())
            ->method('error')
            ->with(
                $this->stringContains('Error processing currency rate processor'),
                $this->arrayHasKey('exception')
            );

        $bus->expects($this->once())
            ->method('dispatch')
            ->willReturn(new Envelope(new \stdClass()));

        $handler = new UpdateRatesHandler([$processor], $bus, $logger);
        $handler($message);
    }
}
