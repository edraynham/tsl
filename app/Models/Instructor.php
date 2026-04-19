<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Instructor extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'headline',
        'bio',
        'city',
        'county',
        'photo_url',
        'website',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function locationLabel(): string
    {
        return collect([$this->city, $this->county])->filter()->implode(', ');
    }
}
