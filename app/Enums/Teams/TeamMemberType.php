<?php

declare(strict_types=1);

namespace App\Enums\Teams;

abstract class TeamMemberType
{
    const OWNER = 0;
    const TEAM_ADMIN = 1;
    const REGULAR = 2;
}
