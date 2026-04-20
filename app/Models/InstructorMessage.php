<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorMessage extends Model
{
    protected $fillable = [
        'instructor_id',
        'sender_name',
        'sender_email',
        'sender_phone',
        'subject',
        'skill_level',
        'message',
    ];

    /**
     * @return BelongsTo<Instructor, $this>
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }
}
