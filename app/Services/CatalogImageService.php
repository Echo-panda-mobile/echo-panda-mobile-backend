<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CatalogImageService
{
    public function keyFromUrlOrKey(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (! preg_match('#^https?://#i', $value)) {
            return ltrim($value, '/');
        }

        $path = parse_url($value, PHP_URL_PATH);

        return $path ? ltrim(rawurldecode($path), '/') : null;
    }

    public function buildImageKey(Model $model, string $extension): string
    {
        $uuid = (string) Str::uuid();
        $prefix = method_exists($model, 'catalogImagePrefix')
            ? $model->catalogImagePrefix()
            : 'images/catalog/';

        return $prefix."{$uuid}.{$extension}";
    }

    public function assertImageBelongsToModel(Model $model, string $imageKey): void
    {
        if (! method_exists($model, 'catalogImagePrefix')) {
            abort(422, 'This resource does not support catalog images.');
        }

        if (! str_starts_with($imageKey, $model->catalogImagePrefix())) {
            abort(422, 'The provided image does not belong to this record.');
        }

        if (! Storage::disk('s3')->exists($imageKey)) {
            abort(422, 'The uploaded image could not be found in storage.');
        }
    }

    public function attachImage(Model $model, ?string $imageValue): void
    {
        if (! method_exists($model, 'deleteStoredImage') || ! method_exists($model, 'storedImageKey')) {
            return;
        }

        $imageKey = $this->keyFromUrlOrKey($imageValue);

        if ($imageKey === null) {
            return;
        }

        if ($imageKey === $model->storedImageKey()) {
            return;
        }

        $this->assertImageBelongsToModel($model, $imageKey);
        $model->deleteStoredImage();
        $model->update(['image_url' => $imageKey]);
    }
}
