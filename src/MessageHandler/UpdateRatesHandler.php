<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\UpdateRatesMessage;
use App\State\Processor\ExchangeRate\ProcessorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
final readonly class UpdateRatesHandler
{
    /** @param iterable<ProcessorInterface> $processors */
    public function __construct(
        private iterable $processors,
        private MessageBusInterface $bus,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(UpdateRatesMessage $message): void
    {
        $processorEnum = $message->getProcessorEnum();
        $typeEnum = $message->getTypeEnum();

        if (null === $processorEnum || null === $typeEnum) {
            throw new \InvalidArgumentException('Message must contain both type and processor enums.');
        }

        foreach ($this->processors as $processor) {
            if ($processor->supports($processorEnum)) {
                try {
                    $processor->update($typeEnum, $message->base, $message->quote);
                } catch (\Throwable $exception) {
                    $this->logger->error('Error processing currency rate processor', [
                        'exception' => $exception,
                        'type' => $message->type,
                        'processor' => $message->processor,
                        'base' => $message->base,
                        'quote' => $message->quote,
                    ]);
                }

                $this->bus->dispatch(
                    new UpdateRatesMessage(
                        $message->type,
                        $message->processor,
                        $message->base,
                        $message->quote
                    ),
                    [new DelayStamp(60_000)]
                );

                return;
            }
        }

        throw new \RuntimeException(sprintf('No processor support: %s', $message->processor));
    }
}
