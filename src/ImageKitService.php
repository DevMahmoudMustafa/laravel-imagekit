<?php

namespace DevMahmoudMustafa\ImageKit;

use DevMahmoudMustafa\ImageKit\Events\ImageDeleted;
use DevMahmoudMustafa\ImageKit\Processors\ImageHandler;
use Illuminate\Support\Facades\Event;

class ImageKitService extends ImageHandler
{
    /**
     * Create a fresh instance of ImageKitService.
     * Useful for avoiding state pollution when using singleton.
     *
     * @return static
     */
    public static function make(): self
    {
        return app(self::class);
    }

    /**
     * Reset the service to initial state.
     * 
     * @return $this
     */
    public function reset(): self
    {
        parent::reset();
        return $this;
    }

    /**
     * Set the storage disk to use.
     *
     * @param string $disk
     * @return $this
     */
    public function setDisk(string $disk): self
    {
        $this->storageService->setDisk($disk);
        $this->resizeService->setDisk($disk);
        $this->compressionService->setDisk($disk);
        $this->watermarkService->setDisk($disk);
        return $this;
    }

    /**
     * Get the current storage disk.
     *
     * @return string
     */
    public function getDisk(): string
    {
        return $this->storageService->getDisk();
    }

    /**
     * Set the image to be processed.
     *
     * @param mixed $image UploadedFile or image resource
     * @return $this
     */
    public function setImage($image): self
    {
        $this->validationService->validateImage($image); // Validate the image
        $this->image = $image;
        return $this;
    }

    /**
     * Alias for setImage() - more fluent API.
     *
     * @param mixed $image
     * @return $this
     */
    public function image($image): self
    {
        return $this->setImage($image);
    }

    /**
     * Set the images to be processed.
     *
     * @param array $images Array of UploadedFile objects
     * @return $this
     */
    public function setImages(array $images): self
    {
        foreach ($images as $image) {
            $this->validationService->validateImage($image); // Validate the images
        }
        $this->images = $images;
        return $this;
    }

    /**
     * Alias for setImages() - more fluent API.
     *
     * @param array $images
     * @return $this
     */
    public function images(array $images): self
    {
        return $this->setImages($images);
    }


    /**
     * Save the image after applying all modifications.
     * Automatically resets state after saving to prevent state pollution.
     *
     * @return string
     */
    public function saveImage(): string
    {
        try {
            return $this->processingImage();
        } finally {
            // Reset state after processing to avoid state pollution in singleton
            $this->resetSingleImageState();
        }
    }

    /**
     * Alias for saveImage() - more fluent API.
     *
     * @return string
     */
    public function save(): string
    {
        return $this->saveImage();
    }

    /**
     * Reset state after processing single image.
     *
     * @return void
     */
    protected function resetSingleImageState(): void
    {
        $this->image = null;
        $this->imageName = null;
        $this->extension = null;
        // Keep other settings as they might be reused
    }


    /**
     * Save multiple images as a gallery.
     *
     * @param string|null $imageColumnName
     * @param string|null $fkColumnName
     * @param int|null $fkId
     * @param string|array|null $altText Can be a single string for all images, or array of strings for each image
     * @return array
     */
    public function saveGallery(
        ?string $imageColumnName = null,
        ?string $fkColumnName = null,
        ?int $fkId = null,
        string|array|null $altText = null
    ): array {
        if (empty($this->images)) {
            throw new \InvalidArgumentException('No images provided. Use setImages() first.');
        }

        $data = [];
        $altTexts = is_array($altText) ? $altText : null;
        
        try {
        foreach ($this->images as $index => $image) {
                $this->image = $image;
                $this->imageName = null; // Reset name for each image

                $imageName = $this->processingImage();

            if ($imageColumnName) {
                $row = [
                    $imageColumnName => $imageName
                ];

                if ($fkColumnName && $fkId) {
                    $row[$fkColumnName] = $fkId;
                }

                    // Support both single altText and array of altTexts
                    $currentAltText = $altTexts ? ($altTexts[$index] ?? null) : $altText;
                    if ($currentAltText) {
                        $row['alt'] = $currentAltText;
                }

                $data[] = $row;
            } else {
                $data[] = $imageName;
            }
            }
        } finally {
            // Reset state after processing
            $this->resetGalleryState();
        }

        return $data;
    }

    /**
     * Reset state after processing gallery.
     *
     * @return void
     */
    protected function resetGalleryState(): void
    {
        $this->images = [];
        $this->image = null;
        $this->imageName = null;
        $this->extension = null;
    }


    /**
     * Delete an image.
     *
     * @param string $imageName
     * @param string $path
     * @param array|null $sizes Optional sizes array. If not provided, uses current resize settings.
     * @return bool
     */
    public function deleteImage(string $imageName, string $path, ?array $sizes = null): bool
    {
        $sizesToDelete = $sizes ?? ($this->resize ?? []);
        $success = $this->storageService->deleteImage($imageName, $path, $sizesToDelete);
        
        // Fire ImageDeleted event
        Event::dispatch(new ImageDeleted($imageName, $path, $success));
        
        return $success;
    }


    /**
     * Delete multiple images.
     *
     * @param array $imageNames
     * @param string $path
     * @param array|null $sizes Optional sizes array. If not provided, uses current resize settings.
     * @return int Number of successfully deleted images
     */
    public function deleteGallery(array $imagesNames, string $path, ?array $sizes = null): int
    {
        $sizesToDelete = $sizes ?? ($this->resize ?? []);
        $countDelete = 0;
        
        foreach ($imagesNames as $imageName) {
            $success = $this->storageService->deleteImage($imageName, $path, $sizesToDelete);
            if ($success) {
                $countDelete++;
            }
            // Fire ImageDeleted event for each image
            Event::dispatch(new ImageDeleted($imageName, $path, $success));
        }

        return $countDelete;
    }

    /**
     * Get image content from storage.
     *
     * @param string $path Relative path on the storage disk
     * @param string|null $disk Optional disk name. If not provided, uses current disk from config.
     * @return string|null Image content or null if file doesn't exist
     */
    public function getImage(string $path, ?string $disk = null): ?string
    {
        return $this->storageService->getImage($path, $disk);
    }

    /**
     * Get the full URL for an image.
     *
     * @param string $path Relative path on the storage disk
     * @param string|null $disk Optional disk name. If not provided, uses current disk from config.
     * @return string
     */
    public function getImageUrl(string $path, ?string $disk = null): string
    {
        return $this->storageService->url($path, $disk);
    }

    /**
     * Get the full path for an image (for local disk operations).
     *
     * @param string $path Relative path on the storage disk
     * @param string|null $disk Optional disk name. If not provided, uses current disk from config.
     * @return string
     */
    public function getImagePath(string $path, ?string $disk = null): string
    {
        return $this->storageService->path($path, $disk);
    }

    /**
     * Check if image file exists.
     *
     * @param string $path Relative path on the storage disk
     * @param string|null $disk Optional disk name. If not provided, uses current disk from config.
     * @return bool
     */
    public function imageExists(string $path, ?string $disk = null): bool
    {
        return $this->storageService->exists($path, $disk);
    }

    /**
     * Get HTTP response for image (for displaying in browser).
     *
     * @param string $path Relative path on the storage disk
     * @param string|null $disk Optional disk name. If not provided, uses current disk from config.
     * @param array $options Additional options:
     *   - 'contentType': MIME type (auto-detected if not provided)
     *   - 'headers': Custom headers array
     *   - 'cache': Cache control (false = no cache, or number = max-age in seconds)
     *   - 'disposition': 'inline' (default) or 'attachment'
     *   - 'filename': Custom filename for Content-Disposition
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function response(string $path, ?string $disk = null, array $options = [])
    {
        return $this->storageService->response($path, $disk, $options);
    }

    /**
     * Get download response for image.
     *
     * @param string $path Relative path on the storage disk
     * @param string|null $name Optional download filename
     * @param string|null $disk Optional disk name. If not provided, uses current disk from config.
     * @param array $options Additional options (headers, etc.)
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(string $path, ?string $name = null, ?string $disk = null, array $options = [])
    {
        return $this->storageService->download($path, $name, $disk, $options);
    }

    /**
     * Get temporary URL for image (useful for cloud storage like S3).
     *
     * @param string $path Relative path on the storage disk
     * @param \DateTimeInterface|\DateInterval|int $expiration Expiration time
     * @param string|null $disk Optional disk name. If not provided, uses current disk from config.
     * @param array $options Additional options
     * @return string Temporary URL
     */
    public function temporaryUrl(string $path, $expiration, ?string $disk = null, array $options = []): string
    {
        return $this->storageService->temporaryUrl($path, $expiration, $disk, $options);
    }


}
