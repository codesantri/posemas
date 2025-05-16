<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PawningPayment extends Model
{
    /** @use HasFactory<\Database\Factories\PawningPaymentFactory> */
    use HasFactory;
    protected $guarded = [''];

    public function pawning()
    {
        return $this->belongsTo(Pawning::class);
    }
}
