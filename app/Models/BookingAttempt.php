<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'attempt_key',
        'status',
        'booking_id',
        'supplier_book_id',
        'supplier_book_guid',
        'started_at',
        'completed_at',
        'last_error_code',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
