<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Facility extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * @return BelongsToMany<ShootingGround, $this>
     */
    public function shootingGrounds(): BelongsToMany
    {
        return $this->belongsToMany(ShootingGround::class);
    }
}
