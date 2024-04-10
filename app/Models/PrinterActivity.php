<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrinterActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'isSuccess',
        'description',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
