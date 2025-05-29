<?php

namespace App\Traits;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait HasImageHandler
{
    // public function onShow(bool $show = false): string
    // {
    //     if ($show && $this->getImagePath()) {
    //         return asset('storage/' . $this->getImagePath());
    //     }

    //     $svgFallback = Blade::render('<x-heroicon-o-photo class="w-36 h-36 text-gray-400" />');
    //     return 'data:image/svg+xml;base64,' . base64_encode($svgFallback);
    // }

    public function onUpdate(bool $update = false): void
    {
        if (!$update) return;

        $oldImage = $this->getOriginal('image');
        $newImage = $this->getImagePath();

        if ($newImage && $oldImage && $newImage !== $oldImage) {
            if (Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
                Log::info("Deleted old public image: {$oldImage}");
            } else {
                Log::warning("Old image not found in public storage: {$oldImage}");
            }
        }
    }

    public function onDelete(bool $delete = false): void
    {
        if (!$delete) return;

        $image = $this->getImagePath();

        if ($image && Storage::disk('public')->exists($image)) {
            Storage::disk('public')->delete($image);
            Log::info("Deleted public image: {$image}");
        } else {
            Log::warning("Image not found in public storage: {$image}");
        }
    }

    /**
     * Method default, override kalau nama field image beda
     */
    public function getImagePath(): ?string
    {
        return $this->image ?? null;
    }
}
