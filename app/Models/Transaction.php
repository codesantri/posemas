<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded = [''];


    public function sale()
    {
        return $this->hasOne(Sale::class);
    }

    public function purchase()
    {
        return $this->hasOne(Purchase::class);
    }

    public function pawning()
    {
        return $this->hasOne(Pawning::class);
    }

    public function change()
    {
        return $this->hasOne(Change::class);
    }


    public function getRouteKeyName()
    {
        return 'invoice';
    }

    protected static function booted()
    {
        static::creating(function ($transaction) {
            if (!$transaction->invoice) {
                $today = now()->format('Ymd');
                $countToday = static::whereDate('created_at', now()->toDateString())->count() + 1;
                $prefixMap = [
                    'sale'     => 'SL',
                    'purchase' => 'PCS',
                    'pawning'  => 'PW',
                    'change'   => 'CHG',
                ];
                $prefix = $prefixMap[$transaction->transaction_type] ?? 'TRX';
                $transaction->invoice = $prefix . $today . str_pad($countToday, 3, '0', STR_PAD_LEFT);
            }
        });
    }
}
