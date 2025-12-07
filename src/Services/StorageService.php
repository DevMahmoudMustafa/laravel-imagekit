<?php

namespace DevMahmoudMustafa\ImageKit\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid as UuidGenerator;

class StorageService
{
    protected string $disk;

    public function __construct()
    {
        $this->disk = config('imagekit.disk', 'public');
    }

    /**
     * Save the original image.
     *
     * @param mixed $image UploadedFile
     * @param string $path Relative path on the storage disk
     * @param string|null $name Custom name for the image
     * @param string|null $extension Custom extension
     * @return array
     */
    public function saveOriginal($image, string $path, string $name = null, string $extension = null): array
    {
        $name = $name ?? $this->generateFileName($image);
        $ext = '.' . ($extension ?? $image->extension());
        $imageName = $name . $ext;

        // Ensure path ends with / for proper storage path
        $path = rtrim($path, '/') . '/';
        $fullPath = $path . $imageName;

        // Store the image using Laravel Storage
        Storage::disk($this->disk)->put($fullPath, file_get_contents($image->getRealPath()));

        return [
            'image' => $image,
            'imageName' => $imageName,
            'imagePath' => $path,
            'fullPath' => $fullPath,
        ];
    }

    /**
     * Save watermark image file.
     *
     * @param mixed $watermark UploadedFile
     * @param string|null $name Custom name for the watermark
     * @return string Full path to saved watermark
     */
    public function saveWatermark($watermark, ?string $name = null): string
    {
        $watermarkPath = config('imagekit.watermark_storage_path', 'watermarks');
        $name = $name ?? 'watermark_' . time() . '_' . \Illuminate\Support\Str::random(10);
        $ext = '.' . ($watermark->extension() ?? 'png');
        $watermarkName = $name . $ext;
        
        $path = rtrim($watermarkPath, '/') . '/';
        $fullPath = $path . $watermarkName;
        
        Storage::disk($this->disk)->put($fullPath, file_get_contents($watermark->getRealPath()));
        
        return $fullPath;
    }

    /**
     * Get the full URL for an image.
     *
     * @param string $path Relative path on the storage disk
     * @param string|null $disk Optional disk name. If not provided, uses current disk.
     * @return string
     */
    public function url(string $path, ?string $disk = null): string
    {
        $disk = $disk ?? $this->disk;
        return Storage::disk($disk)->url($path);
    }

    /**
     * Get the full path for an image (for local disk operations).
     *
     * @param string $path Relative path on the storage disk
     * @param string|null $disk Optional disk name. If not provided, uses current disk.
     * @return string
     */
    public function path(string $path, ?string $disk = null): string
    {
        $disk = $disk ?? $this->disk;
        return Storage::disk($disk)->path($path);
    }

    /**
     * Check if file exists.
     *
     * @param string $path Relative path on the storage disk
     * @param string|null $disk Optional disk name. If not provided, uses current disk.
     * @return bool
     */
    public function exists(string $path, ?string $disk = null): bool
    {
        $disk = $disk ?? $this->disk;
        return Storage::disk($disk)->exists($path);
    }

    /**
     * Get image content from storage.
     *
     * @param string $path Relative path on the storage disk
     * @param string|null $disk Optional disk name. If not provided, uses current disk.
     * @return string|null Image content or null if file doesn't exist
     */
    public function getImage(string $path, ?string $disk = null): ?string
    {
        $disk = $disk ?? $this->disk;

        if (!Storage::disk($disk)->exists($path)) {
            return null;
        }

        return Storage::disk($disk)->get($path);
    }

    /**
     * Get HTTP response for image (for displaying in browser).
     *
     * @param string $path Relative path on the storage disk
     * @param string|null $disk Optional disk name. If not provided, uses current disk.
     * @param array $options Additional options (headers, cache, etc.)
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\StreamedResponse
     * @throws \InvalidArgumentException
     */
    public function response(string $path, ?string $disk = null, array $options = [])
    {
        $disk = $disk ?? $this->disk;

        if (!Storage::disk($disk)->exists($path)) {
            throw new \InvalidArgumentException("Image file does not exist: {$path}");
        }

        // Get file content
        $content = Storage::disk($disk)->get($path);

        // Determine MIME type from extension
        $mimeType = $this->getMimeType($path);

        // Get content type from options or use detected MIME type
        $contentType = $options['contentType'] ?? $mimeType;

        // Default headers
        $headers = $options['headers'] ?? [];
        $headers['Content-Type'] = $contentType;
        $headers['Content-Length'] = strlen($content);

        // Add cache headers if specified
        if (isset($options['cache'])) {
            if ($options['cache'] === false) {
                $headers['Cache-Control'] = 'no-cache, no-store, must-revalidate';
                $headers['Pragma'] = 'no-cache';
                $headers['Expires'] = '0';
            } elseif (is_numeric($options['cache'])) {
                $headers['Cache-Control'] = 'public, max-age=' . $options['cache'];
            }
        }

        // Handle inline vs attachment
        $disposition = $options['disposition'] ?? 'inline';
        $filename = $options['filename'] ?? basename($path);
        
        $headers['Content-Disposition'] = $disposition . '; filename="' . $filename . '"';

        return response($content, 200, $headers);
    }

    /**
     * Get download response for image.
     *
     * @param string $path Relative path on the storage disk
     * @param string|null $name Optional download filename
     * @param string|null $disk Optional disk name. If not provided, uses current disk.
     * @param array $options Additional options (headers, etc.)
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     * @throws \InvalidArgumentException
     */
    public function download(string $path, ?string $name = null, ?string $disk = null, array $options = [])
    {
        $disk = $disk ?? $this->disk;

        if (!Storage::disk($disk)->exists($path)) {
            throw new \InvalidArgumentException("Image file does not exist: {$path}");
        }

        $downloadName = $name ?? basename($path);

        // Use Laravel's download response
        return Storage::disk($disk)->download($path, $downloadName, $options['headers'] ?? []);
    }

    /**
     * Get temporary URL for image (useful for cloud storage like S3).
     *
     * @param string $path Relative path on the storage disk
     * @param \DateTimeInterface|\DateInterval|int $expiration Expiration time
     * @param string|null $disk Optional disk name. If not provided, uses current disk.
     * @param array $options Additional options
     * @return string Temporary URL
     */
    public function temporaryUrl(string $path, $expiration, ?string $disk = null, array $options = []): string
    {
        $disk = $disk ?? $this->disk;

        if (!Storage::disk($disk)->exists($path)) {
            throw new \InvalidArgumentException("Image file does not exist: {$path}");
        }

        return Storage::disk($disk)->temporaryUrl($path, $expiration, $options);
    }

    /**
     * Get MIME type for image based on extension.
     *
     * @param string $path
     * @return string
     */
    protected function getMimeType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'bmp' => 'image/bmp',
            'ico' => 'image/x-icon',
            default => 'application/octet-stream',
        };
    }

    /**
     * Delete an image.
     *
     * @param string $imageName Image filename
     * @param string $path Relative path on the storage disk
     * @param array|null $sizes Array of size prefixes to delete
     * @return bool
     */
    public function deleteImage(string $imageName, string $path, ?array $sizes = null): bool
    {
        $deleted = false;
        
        // Delete resized versions if sizes array is provided
        if (is_array($sizes) && count($sizes) > 0) {
            foreach ($sizes as $size) {
                $filePath = rtrim($path, '/') . '/' . $size . '_' . $imageName;
                if (Storage::disk($this->disk)->exists($filePath)) {
                    Storage::disk($this->disk)->delete($filePath);
                }
            }
        }
        
        // Delete original image
        $originalFilePath = rtrim($path, '/') . '/' . $imageName;
        if (Storage::disk($this->disk)->exists($originalFilePath)) {
            $deleted = Storage::disk($this->disk)->delete($originalFilePath);
        }
        
        return $deleted;
    }

    /**
     * Set the storage disk.
     *
     * @param string $disk
     * @return $this
     */
    public function setDisk(string $disk): self
    {
        $this->disk = $disk;
        return $this;
    }

    /**
     * Get the current storage disk.
     *
     * @return string
     */
    public function getDisk(): string
    {
        return $this->disk;
    }

    /**
     * Generate a file name based on the configured naming strategy.
     *
     * @param mixed $image
     * @return string
     */
    protected function generateFileName($image): string
    {
        $strategy = config('imagekit.naming_strategy', 'default');

        // Handle built-in strategies first
        $builtInStrategies = ['default', 'uuid', 'hash', 'timestamp'];
        
        // Custom callable (but not a built-in string strategy)
        if (!in_array($strategy, $builtInStrategies) && is_callable($strategy)) {
            return call_user_func($strategy, $image);
        }

        return match ($strategy) {
            'uuid' => $this->generateUuidName(),
            'hash' => $this->generateHashName($image),
            'timestamp' => $this->generateTimestampName(),
            default => $this->generateDefaultName(),
        };
    }

    /**
     * Generate default file name (slug + timestamp + random).
     *
     * @return string
     */
    protected function generateDefaultName(): string
    {
        return Str::slug('image') . '_' . time() . '_' . Str::random(20);
    }

    /**
     * Generate UUID-based file name.
     *
     * @return string
     */
    protected function generateUuidName(): string
    {
        if (class_exists(UuidGenerator::class)) {
            return UuidGenerator::uuid4()->toString();
        }
        
        // Fallback if UUID package not available
        return $this->generateDefaultName();
    }

    /**
     * Generate hash-based file name.
     *
     * @param mixed $image
     * @return string
     */
    protected function generateHashName($image): string
    {
        $content = file_get_contents($image->getRealPath());
        // Use md5 for hash-based naming (simpler and doesn't require bcrypt)
        return md5($content . time());
    }

    /**
     * Generate timestamp-based file name.
     *
     * @return string
     */
    protected function generateTimestampName(): string
    {
        return time() . '_' . Str::random(16);
    }
}
