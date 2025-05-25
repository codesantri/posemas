<?php

namespace App\Traits;

use Illuminate\Support\Facades\Blade;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasPublicImage
{
    protected function image(): Attribute
    {
        return Attribute::make(
            get: function ($image) {
                if ($image) {
                    return asset('/storage/' . $image);
                }

                // Render Blade Icon as SVG
                $svgFallback = Blade::render('<x-heroicon-o-photo class="w-36 h-36" />');

                // Encode jadi data URI
                $base64 = 'data:image/svg+xml;base64,' . base64_encode($svgFallback);

                return $base64;
            },
            set: fn($image) => $image,
        );
    }
}
