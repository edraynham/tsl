<?php

namespace App\Policies;

use App\Models\Competition;
use App\Models\ShootingGround;
use App\Models\User;

class CompetitionPolicy
{
    public function create(User $user, ShootingGround $shootingGround): bool
    {
        return $user->managesShootingGround($shootingGround);
    }

    public function update(User $user, Competition $competition): bool
    {
        return $user->managesShootingGround($competition->shootingGround);
    }

    public function delete(User $user, Competition $competition): bool
    {
        return $user->managesShootingGround($competition->shootingGround);
    }
}
