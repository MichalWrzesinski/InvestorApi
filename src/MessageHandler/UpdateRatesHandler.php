<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\UpdateRatesMessage;
use App\State\Processor\ExchangeRate\ProcessorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use RuntimeException;
use Throwable;

#[AsMessageHandler]
class UpdateRatesHandler
{
    /** @param iterable<ProcessorInterface> $processors */
    public function __construct(
        private iterable $processors,
        private MessageBusInterface $bus,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(UpdateRatesMessage $message): void
    {
        $processorEnum = $message->getProcessorEnum();

        foreach ($this->processors as $processor) {
            if ($processorEnum && $processor->supports($processorEnum)) {
                try {
                    $processor->update(
                        array_map(
                            fn(array $pair) => [$pair['base'], $pair['quote']],
                            $message->pairs
                        )
                    );
                } catch (Throwable $throwable) {
                    $this->logger->error('Error processing currency rate processor', [
                        'exception' => $throwable,
                        'processor' => $message->processor,
                        'pairs' => $message->pairs,
                    ]);
                }

                $this->bus->dispatch(
                    new UpdateRatesMessage($message->processor, $message->pairs),
                    [new DelayStamp(60_000)]
                );

                return;
            }
        }

        throw new RuntimeException(
            sprintf('No processor support: %s', $message->processor)
        );
    }
}
