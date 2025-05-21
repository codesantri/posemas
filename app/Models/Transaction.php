<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory;
    protected $guarded = [''];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function getRouteKeyName()
    {
        return 'invoice';
    }

    protected static function booted()
    {
        static::creating(function ($transaction) {
            if (!$transaction->invoice) {
                $latestId = static::max('id') + 1;
                $transaction->invoice = 'INV-' . now()->format('Ymd') . '-' . str_pad($latestId, 3, '0', STR_PAD_LEFT);
            }
        });
    }
}
