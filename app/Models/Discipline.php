<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Discipline extends Model
{
    protected $fillable = [
        'name',
        'code',
    ];

    /**
     * @return BelongsToMany<ShootingGround, $this>
     */
    public function shootingGrounds(): BelongsToMany
    {
        return $this->belongsToMany(ShootingGround::class)->withTimestamps(false, false);
    }
}
