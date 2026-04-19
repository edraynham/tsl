<?php

namespace App\Policies;

use App\Models\ShootingGround;
use App\Models\User;

class ShootingGroundPolicy
{
    public function manage(User $user, ShootingGround $shootingGround): bool
    {
        return $user->managesShootingGround($shootingGround);
    }
}
