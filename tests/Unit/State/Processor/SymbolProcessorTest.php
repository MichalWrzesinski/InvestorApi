<?php

declare(strict_types=1);

namespace App\Tests\Unit\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Symbol;
use App\Service\ExchangeRateSynchronizerInterface;
use App\State\Processor\SymbolProcessor;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

final class SymbolProcessorTest extends TestCase
{
    private ProcessorInterface&MockObject $persistProcessor;
    private ExchangeRateSynchronizerInterface&MockObject $synchronizer;
    private SymbolProcessor $processor;

    protected function setUp(): void
    {
        $this->persistProcessor = $this->createMock(ProcessorInterface::class);
        $this->synchronizer = $this->createMock(ExchangeRateSynchronizerInterface::class);

        $this->processor = new SymbolProcessor(
            $this->persistProcessor,
            $this->synchronizer
        );
    }

    public function testProcessWithSymbolCallsSynchronizerAndReturnsResult(): void
    {
        $symbol = new Symbol();
        $operation = $this->createMock(Operation::class);
        $expectedResult = new Symbol();

        $this->persistProcessor
            ->expects($this->once())
            ->method('process')
            ->with($symbol, $operation, [], [])
            ->willReturn($expectedResult);

        $this->synchronizer
            ->expects($this->once())
            ->method('synchronizeFor')
            ->with($symbol);

        $result = $this->processor->process($symbol, $operation);

        $this->assertSame($expectedResult, $result);
    }

    public function testProcessWithNonSymbolDelegatesOnly(): void
    {
        $nonSymbol = new stdClass();
        $operation = $this->createMock(Operation::class);
        $expectedResult = new stdClass();

        $this->persistProcessor
            ->expects($this->once())
            ->method('process')
            ->with($nonSymbol, $operation, [], [])
            ->willReturn($expectedResult);

        $this->synchronizer
            ->expects($this->never())
            ->method('synchronizeFor');

        $result = $this->processor->process($nonSymbol, $operation);

        $this->assertSame($expectedResult, $result);
    }
}
