<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PawningDetail extends Model
{
    /** @use HasFactory<\Database\Factories\PawningDetailFactory> */
    use HasFactory;
    protected $guarded = [''];

    public function pawning()
    {
        return $this->belongsTo(Pawning::class);
    }

    public function karat()
    {
        return $this->belongsTo(Karat::class);
    }
}
