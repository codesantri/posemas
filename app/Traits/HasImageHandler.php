<?php

namespace App\Traits;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait HasImageHandler
{
    public function onShow(bool $show = false): string
    {
        if ($show && $this->image) {
            return asset('storage/' . $this->image);
        }

        $svgFallback = Blade::render('<x-heroicon-o-photo class="w-36 h-36 text-gray-400" />');
        return 'data:image/svg+xml;base64,' . base64_encode($svgFallback);
    }

    public function onUpdate(bool $update = false): void
    {
        if (!$update) return;

        $oldImage = $this->getOriginal('image');
        $newImage = $this->image;

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

        if ($this->image && Storage::disk('public')->exists($this->image)) {
            Storage::disk('public')->delete($this->image);
            Log::info("Deleted public image: {$this->image}");
        } else {
            Log::warning("Image not found in public storage: {$this->image}");
        }
    }
}
