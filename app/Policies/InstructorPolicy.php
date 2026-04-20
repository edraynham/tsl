<?php

namespace App\Policies;

use App\Models\Instructor;
use App\Models\User;

class InstructorPolicy
{
    public function update(User $user, Instructor $instructor): bool
    {
        return $instructor->user_id !== null && (int) $instructor->user_id === (int) $user->id;
    }
}
