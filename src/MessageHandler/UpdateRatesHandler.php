<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\UpdateRatesMessage;
use App\Service\ExchangeRate\ProcessorInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use RuntimeException;
use Throwable;

#[AsMessageHandler]
class UpdateRatesHandler
{
    public function __construct(
        private iterable $processors,
        private MessageBusInterface $bus
    ) {}

    public function __invoke(UpdateRatesMessage $message): void
    {
        foreach ($this->processors as $processor) {
            if ($processor->supports($message->processor)) {
                try {
                    $processor->update($message->pairs);
                } catch (Throwable $e) {
                    // TODO: logowanie błędu
                }

                $this->bus->dispatch(
                    new UpdateRatesMessage($message->processor, $message->pairs),
                    [new DelayStamp(60_000)]
                );

                return;
            }
        }

        throw new RuntimeException("Brak obsługi procesora: {$message->processor}");
    }
}
