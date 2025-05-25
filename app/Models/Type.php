<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    /** @use HasFactory<\Database\Factories\TypeFactory> */
    use HasFactory;
    protected $guarded = [''];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function pawningDetails()
    {
        return $this->hasMany(PawningDetail::class);
    }
}
