<?php

declare(strict_types=1);

namespace App\Enums\Goals;

abstract class KrCurrencyType
{
    const TYPE_USD = 0; // US Dollar
    const TYPE_JPY = 1; // Japanese Yen
    const TYPE_KRW = 2; // South Korean Won
    const TYPE_CNY = 3; // Chinese Yuan
    const TYPE_EUR = 4; // Euro
}
