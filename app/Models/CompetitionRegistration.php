<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionRegistration extends Model
{
    protected $fillable = [
        'competition_id',
        'competition_squad_id',
        'cpsa_number',
        'entrant_name',
        'email',
        'telephone',
    ];

    /**
     * @return BelongsTo<Competition, $this>
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /**
     * @return BelongsTo<CompetitionSquad, $this>
     */
    public function squad(): BelongsTo
    {
        return $this->belongsTo(CompetitionSquad::class, 'competition_squad_id');
    }
}
