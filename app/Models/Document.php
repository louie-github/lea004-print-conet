<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'url',
        'name',
        'color',
        'page_range_start',
        'page_range_end',
        'amount_paid',
        'no_of_copies',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
