<?php declare(strict_types=1);

namespace App\Enums\Teams;

abstract class TeamServiceUseType
{
    const FREE_TRIAL = 0;
    const BASIC_PLAN = 1;
    const PREMIUM_PLAN = 2;
    const GOLD_PLAN = 3;
}