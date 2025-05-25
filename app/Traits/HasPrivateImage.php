<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;


trait HasPrivateImage
{
    protected function image(): Attribute
    {
        return Attribute::make(
            get: function ($image) {
                if ($image) {
                    return route('private', ['path' => $image]);
                }

                $svgFallback = Blade::render('<x-heroicon-o-photo class="w-36 h-36 text-gray-400" />');
                return 'data:image/svg+xml;base64,' . base64_encode($svgFallback);
            },
            set: fn($image) => $image,
        );
    }

    protected static function bootHasPrivateImage()
    {
        static::deleting(function ($model) {
            if ($model->image) {
                $path = parse_url($model->image, PHP_URL_PATH);
                $relativePath = str_replace('/private/storage/', '', $path);

                if (Storage::disk('private')->exists($relativePath)) {
                    Storage::disk('private')->delete($relativePath);
                    Log::info("Deleted image file: {$relativePath}");
                } else {
                    Log::warning("Image file not found for delete: {$relativePath}");
                }
            }
        });

        static::updating(function ($model) {
            $oldImage = $model->getOriginal('image');
            $newImage = $model->image;

            if ($newImage && $oldImage && $newImage !== $oldImage) {
                $oldPath = parse_url($oldImage, PHP_URL_PATH);
                $relativeOldPath = str_replace('/private/storage/', '', $oldPath);

                if (Storage::disk('private')->exists($relativeOldPath)) {
                    Storage::disk('private')->delete($relativeOldPath);
                    Log::info("Deleted old image file: {$relativeOldPath}");
                } else {
                    Log::warning("Old image file not found for delete: {$relativeOldPath}");
                }
            }
        });
    }
}
