<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    /** @use HasFactory<\Database\Factories\PurchaseFactory> */
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

    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    protected static function booted()
    {
        static::creating(function ($purchase) {
            if (!$purchase->invoice) {
                $latestId = static::max('id') + 1;
                $purchase->invoice = 'INV-' . now()->format('Ymd') . '-' . str_pad($latestId, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'invoice';
    }
}
