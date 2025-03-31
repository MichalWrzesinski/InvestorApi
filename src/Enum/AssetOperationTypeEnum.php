<?php

declare(strict_types=1);

namespace App\Enum;

enum AssetOperationTypeEnum: string
{
    case CREDIT = 'CREDIT';
    case DEBIT = 'DEBIT';
    case INTEREST = 'INTEREST';
    case DIVIDEND = 'DIVIDEND';
    case INSTALLMENT = 'INSTALLMENT';
    case FEE = 'FEE';
    case TRANSFER = 'TRANSFER';
    case CORRECTION = 'CORRECTION';
}
