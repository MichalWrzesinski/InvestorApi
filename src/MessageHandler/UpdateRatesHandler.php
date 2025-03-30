<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\UpdateRatesMessage;
use Psr\Log\LoggerInterface;
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
        private MessageBusInterface $bus,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(UpdateRatesMessage $message): void
    {
        $processorEnum = $message->getProcessorEnum();

        foreach ($this->processors as $processor) {
            if ($processorEnum && $processor->supports($processorEnum)) {
                try {
                    $processor->update($message->pairs);
                } catch (Throwable $e) {
                    $this->logger->error('Error processing currency rate processor', [
                        'exception' => $e,
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
