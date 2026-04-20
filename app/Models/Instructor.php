<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /**
     * @return HasMany<InstructorMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(InstructorMessage::class);
    }

    public function locationLabel(): string
    {
        return collect([$this->city, $this->county])->filter()->implode(', ');
    }
}
