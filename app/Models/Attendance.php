<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'attendance_date',
        'clock_in_time',
        'clock_out_time',
        'clock_in_comment',
        'clock_out_comment',
        'clock_in_modified_by',
        'clock_in_modification_reason',
        'clock_out_modified_by',
        'clock_out_modification_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attendance_date' => 'date',
        'clock_in_time' => 'datetime',
        'clock_out_time' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clockInModifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'clock_in_modified_by');
    }

    public function clockOutModifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'clock_out_modified_by');
    }
}
