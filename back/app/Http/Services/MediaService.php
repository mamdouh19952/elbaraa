<?php

namespace App\Http\Services;

use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\HasMedia;

class MediaService
{
    /**
     * @param  UploadedFile|array<UploadedFile>  $files
     */
    public function upload(HasMedia $model, UploadedFile|array $files, string $collection): void
    {
        foreach (is_array($files) ? $files : [$files] as $file) {
            $model->addMedia($file)->toMediaCollection($collection);
        }
    }

    /**
     * @param  UploadedFile|array<UploadedFile>  $files
     */
    public function update(HasMedia $model, UploadedFile|array $files, string $collection): void
    {
        $this->deleteMedia($model, $collection);
        $this->upload($model, $files, $collection);
    }

    public function deleteMedia(HasMedia $model, string $collection): void
    {
        $model->clearMediaCollection($collection);
    }

    public function deleteMediaItem(HasMedia $model, int $mediaId, string $collection): void
    {
        $model->getMedia($collection)
            ->firstWhere('id', $mediaId)
            ?->delete();
    }

    /**
     * @param  array<int>  $mediaIds
     */
    public function deleteMediaItems(HasMedia $model, array $mediaIds, string $collection): void
    {
        foreach ($mediaIds as $mediaId) {
            $this->deleteMediaItem($model, (int) $mediaId, $collection);
        }
    }
}
