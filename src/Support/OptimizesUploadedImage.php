<?php

namespace VlastimilVasek\LivewireAdvancedMediaLibrary\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\Image\Image;

class OptimizesUploadedImage
{
    public function handle(UploadedFile $file, int $maxWidth, int $quality, string $format): string
    {
        $directory = storage_path('framework/cache/livewire-advanced-media-library');
        File::ensureDirectoryExists($directory);

        $path = $directory.'/'.Str::uuid().'.'.$format;

        $image = Image::load($file->getRealPath());

        if ($image->getWidth() > $maxWidth) {
            $image->width($maxWidth);
        }

        $image
            ->format($format)
            ->quality($quality)
            ->optimize()
            ->save($path);

        return $path;
    }
}
