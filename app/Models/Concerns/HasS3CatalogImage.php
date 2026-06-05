<?php

namespace App\Models\Concerns;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasS3CatalogImage
{
    abstract protected function catalogImageFolder(): string;

    public function catalogSlug(): string
    {
        $slug = $this->slug ?? null;

        if ($slug) {
            return Str::slug($slug);
        }

        $name = $this->name ?? null;

        return Str::slug($name ?: (class_basename(static::class).'-'.$this->id));
    }

    public function catalogImagePrefix(): string
    {
        return rtrim($this->catalogImageFolder(), '/').'/'.$this->catalogSlug().'/';
    }

    public function storedImageKey(): ?string
    {
        $value = $this->attributes['image_url'] ?? null;

        if (! $value) {
            return null;
        }

        if (! preg_match('#^https?://#i', $value)) {
            return ltrim($value, '/');
        }

        $path = parse_url($value, PHP_URL_PATH);

        return $path ? ltrim(rawurldecode($path), '/') : null;
    }

    public function deleteStoredImage(): void
    {
        $key = $this->storedImageKey();
        $folder = rtrim($this->catalogImageFolder(), '/').'/';

        if (! $key || ! str_starts_with($key, $folder)) {
            return;
        }

        Storage::disk('s3')->delete($key);
    }

    public function getImageUrlAttribute(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        /** @var FilesystemAdapter $publicDisk */
        $publicDisk = Storage::disk('public');
        if ($publicDisk->exists($value)) {
            return $publicDisk->url($value);
        }

        try {
            /** @var FilesystemAdapter $s3Disk */
            $s3Disk = Storage::disk('s3');

            return $s3Disk->temporaryUrl($value, now()->addMinutes(60));
        } catch (\Exception $e) {
            /** @var FilesystemAdapter $s3Disk */
            $s3Disk = Storage::disk('s3');

            return $s3Disk->url($value);
        }
    }
}
