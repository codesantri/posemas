<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pawning extends Model
{
    /** @use HasFactory<\Database\Factories\PawningFactory> */
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
        return $this->hasMany(PawningDetail::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
