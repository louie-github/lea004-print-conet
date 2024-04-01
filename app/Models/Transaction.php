<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'total_pages', //total print pages
        'amount_to_be_paid',
        'status',
        'user_id',
        'no_copies',
        'is_colored',
        'uuid',
    ];
    
    //transaction status
    const TS_PENDING = 'Pending';
    const TS_SUCCESS = 'Success';
    const TS_FAILED = 'Failed';
    const TS_IN_PROCESS= 'In Process';
    const TS_CANCELLED= 'Cancelled';
    

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function document(): BelongsTo{
        return $this->belongsTo(Document::class);
    }

    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Carbon::parse($value)->diffForHumans(),
        );
    }

    public function scopeSuccess(Builder $query): void
    {
        $query->where('status', Transaction::TS_SUCCESS );
    }

    public function scopeSuccessTransactionToday(Builder $query): void
    {
        $query->whereDate('created_at', now()->toDateString());
    }
}
