<?php

namespace DevMahmoudMustafa\ImageKit\Processors;

use DevMahmoudMustafa\ImageKit\Contracts\ImageHandlerInterface;
use DevMahmoudMustafa\ImageKit\Events\ImageSaving;
use DevMahmoudMustafa\ImageKit\Events\ImageSaved;
use DevMahmoudMustafa\ImageKit\Services\CompressionService;
use DevMahmoudMustafa\ImageKit\Services\ResizeService;
use DevMahmoudMustafa\ImageKit\Services\StorageService;
use DevMahmoudMustafa\ImageKit\Services\ValidationService;
use DevMahmoudMustafa\ImageKit\Services\WatermarkService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class ImageHandler implements ImageHandlerInterface
{
    protected $image = null;
    protected array $images = [];
    protected ?string $imageName = null;
    protected string $savedPath;
    protected ?string $extension = null;
    protected bool $compress = true;
    protected ?int $compressionRatio = null;
    protected ?array $resize = null;
    protected array $resizeOptions = [];
    protected ?array $dimensions = null;
    protected bool $aspectRatio = true;
    protected ?array $watermark = null;
    protected ValidationService $validationService;
    protected StorageService $storageService;
    protected WatermarkService $watermarkService;
    protected ResizeService $resizeService;
    protected CompressionService $compressionService;

    public function __construct(
        ValidationService $validationService,
        StorageService $storageService,
        WatermarkService $watermarkService,
        ResizeService $resizeService,
        CompressionService $compressionService
    ) {
        $this->validationService = $validationService;
        $this->storageService = $storageService;
        $this->watermarkService = $watermarkService;
        $this->resizeService = $resizeService;
        $this->compressionService = $compressionService;

        // Set default values from the config file
        $defaultPath = config('imagekit.default_saved_path', 'uploads/images');
        $this->savedPath = $this->normalizePath($defaultPath);
        $this->dimensions = (array) config('imagekit.dimensions');
        $this->aspectRatio = config('imagekit.aspectRatio', true);
        $this->resize = config('imagekit.enable_multi_size') ? config('imagekit.multi_size_options') : null;
        $this->resizeOptions = (array) config('imagekit.multi_size_dimensions') ;
        $this->watermark = config('imagekit.enable_watermark') ? config('imagekit.watermark') : null;
    }



    /**
     * Set the name of the image.
     *
     * @param string $name
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setImageName(string $name): self
    {
        $this->validationService->validateImageName($name); // Validate the image name
        $this->imageName = Str::slug($name);
        return $this;
    }

    /**
     * Alias for setImageName() - more fluent API.
     *
     * @param string $name
     * @return $this
     */
    public function name(string $name): self
    {
        return $this->setImageName($name);
    }

    /**
     * Set the file extension of the image.
     *
     * @param string $extension
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setExtension(string $extension): self
    {
        $this->validationService->validateExtension($extension); // Validate the extension
        $this->extension = $extension;
        return $this;
    }

    /**
     * Alias for setExtension() - more fluent API.
     *
     * @param string $extension
     * @return $this
     */
    public function extension(string $extension): self
    {
        return $this->setExtension($extension);
    }

    /**
     * Set the path where the image will be saved.
     *
     * @param string $path
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setImagePath(string $path): self
    {
        // Handle URL paths
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $parsedUrl = parse_url($path);
            $path = $parsedUrl['path'] ?? $path;
        }
        
        // Reject absolute paths before processing
        if (str_starts_with($path, '/') || preg_match('/^[a-zA-Z]:\\\\/', $path)) {
            throw new \InvalidArgumentException("Absolute paths are not allowed. Please use a relative path instead. Provided path: {$path}");
        }
        
        // Remove leading slashes for relative paths
        $path = ltrim($path, '/');
        
        // Normalize path - remove any '..' or dangerous patterns
        $path = str_replace('//', '/', $path);
        $path = str_replace('\\', '/', $path);
        
        // Validate the path
        $this->validationService->validatePath($path);
        
        // Store the path (keep it relative for flexibility)
        $this->savedPath = rtrim($path, '/');
        return $this;
    }

    /**
     * Alias for setImagePath() - more fluent API.
     *
     * @param string $path
     * @return $this
     */
    public function path(string $path): self
    {
        return $this->setImagePath($path);
    }

    /**
     * Alias for setImagePath() - more fluent API.
     *
     * @param string $path
     * @return $this
     */
    public function saveTo(string $path): self
    {
        return $this->setImagePath($path);
    }

    /**
     * Set the dimensions for resizing the image.
     *
     * @param int|null $width
     * @param int|null $height
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setDimensions(?int $width = null, ?int $height = null): self
    {
        $this->validationService->validateDimensions($width, $height); // Validate dimensions
        $this->dimensions = ['width' => $width, 'height' => $height];
        return $this;
    }

    /**
     * Alias for setDimensions() - more fluent API.
     *
     * @param int|null $width
     * @param int|null $height
     * @return $this
     */
    public function resize(?int $width = null, ?int $height = null): self
    {
        return $this->setDimensions($width, $height);
    }

    /**
     * Alias for setDimensions() - more fluent API.
     *
     * @param int|null $width
     * @param int|null $height
     * @return $this
     */
    public function dimensions(?int $width = null, ?int $height = null): self
    {
        return $this->setDimensions($width, $height);
    }

    /**
     * Set the watermark settings for the image.
     *
     * @param string|\Illuminate\Http\UploadedFile|null $imagePathOrFile Path to watermark image or UploadedFile (can be absolute path or relative to storage disk)
     * @param string|null $position Position (top-left, top-right, bottom-left, bottom-right, center)
     * @param int|null $opacity Opacity (0-100)
     * @param array|null $offset Array with 'x' and 'y' keys, or use individual x and y parameters
     * @param int|null $x Horizontal offset (used if offset array not provided)
     * @param int|null $y Vertical offset (used if offset array not provided)
     * @param int|null $width Watermark width in pixels (null = use original size)
     * @param int|null $height Watermark height in pixels (null = use original size)
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setWatermark(
        string|\Illuminate\Http\UploadedFile|null $imagePathOrFile = null,
        ?string $position = null,
        ?int $opacity = null,
        ?array $offset = null,
        ?int $x = null,
        ?int $y = null,
        ?int $width = null,
        ?int $height = null
    ): self {
        $watermark = [];
        
        if ($imagePathOrFile) {
            // Check if it's an UploadedFile instance
            if ($imagePathOrFile instanceof \Illuminate\Http\UploadedFile) {
                // Validate the uploaded watermark image
                $this->validationService->validateImage($imagePathOrFile);
                
                // Save the watermark image to storage
                $watermarkPath = config('imagekit.watermark_storage_path', 'watermarks');
                $savedWatermark = $this->storageService->saveWatermark($imagePathOrFile);
                
                // If width or height provided, resize the watermark
                if ($width !== null || $height !== null) {
                    $savedWatermark = $this->watermarkService->resizeWatermarkImage($savedWatermark, $width, $height);
                }
                
                $watermark['image'] = $savedWatermark;
            } else {
                // It's a string path
                // Only parse URL if it's actually a URL, otherwise treat as path
                if (filter_var($imagePathOrFile, FILTER_VALIDATE_URL)) {
                    $parsedUrl = parse_url($imagePathOrFile);
                    $path = $parsedUrl['path'] ?? $imagePathOrFile;
                } else {
                    $path = $imagePathOrFile;
                }
                
                $watermarkImagePath = ltrim($path, '/');
                
                // If width or height provided, create a resized copy
                if ($width !== null || $height !== null) {
                    $watermarkImagePath = $this->watermarkService->resizeWatermarkImage($watermarkImagePath, $width, $height);
                }
                
                $watermark['image'] = $watermarkImagePath;
            }
        }
        
        if ($position) {
            $watermark['position'] = $position;
        }
        
        if ($opacity !== null) {
            $watermark['opacity'] = $opacity;
        }
        
        // Handle offset - prefer array, then individual parameters
        if ($offset && isset($offset['x']) && isset($offset['y'])) {
            $watermark['x'] = (int) $offset['x'];
            $watermark['y'] = (int) $offset['y'];
        } elseif ($x !== null || $y !== null) {
            $watermark['x'] = $x ?? 10;
            $watermark['y'] = $y ?? 10;
        }
        
        // Add watermark dimensions if provided
        if ($width !== null) {
            $watermark['width'] = $width;
        }
        if ($height !== null) {
            $watermark['height'] = $height;
        }

        // Only validate if watermark has image (otherwise it's disabled)
        if (!empty($watermark)) {
            // Pass current disk to validation service to ensure correct disk is used
            $currentDisk = $this->storageService->getDisk();
            $this->validationService->validateWatermark($watermark, $currentDisk);
            $this->watermark = $watermark;
        } else {
            $this->watermark = null;
        }
        
        return $this;
    }

    /**
     * Alias for setWatermark() - more fluent API.
     *
     * @param string|\Illuminate\Http\UploadedFile|null $imagePathOrFile
     * @param string|null $position
     * @param int|null $opacity
     * @param array|null $offset
     * @param int|null $x
     * @param int|null $y
     * @param int|null $width
     * @param int|null $height
     * @return $this
     */
    public function watermark(
        string|\Illuminate\Http\UploadedFile|null $imagePathOrFile = null,
        ?string $position = null,
        ?int $opacity = null,
        ?array $offset = null,
        ?int $x = null,
        ?int $y = null,
        ?int $width = null,
        ?int $height = null
    ): self {
        return $this->setWatermark($imagePathOrFile, $position, $opacity, $offset, $x, $y, $width, $height);
    }

    /**
     * Enable or disable image compression.
     *
     * @param bool $compress
     * @param int|null $compressionRatio
     * @return $this
     */
    public function compressImage(bool $compress = true, ?int $compressionRatio = null): self
    {
        $this->compress = $compress;
        $this->compressionRatio = $compressionRatio;
        return $this;
    }

    /**
     * Alias for compressImage() - more fluent API.
     *
     * @param bool $compress
     * @param int|null $quality
     * @return $this
     */
    public function compress(bool $compress = true, ?int $quality = null): self
    {
        return $this->compressImage($compress, $quality);
    }

    /**
     * Set the multi size options for the image.
     *
     * @param array $resize
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setMultiSizeOptions(?array $resize = null): self
    {
        if ($resize !== null) {
            $this->validationService->validateResizeOptions($resize, $this->resizeOptions); // Validate resize options
            $this->resize = $resize;
        } else {
            // Reset to default based on config
            $this->resize = config('imagekit.enable_multi_size') ? config('imagekit.multi_size_options') : null;
        }

        return $this;
    }

    /**
     * Alias for setMultiSizeOptions() - more fluent API.
     *
     * @param array|null $sizes
     * @return $this
     */
    public function sizes(?array $sizes = null): self
    {
        return $this->setMultiSizeOptions($sizes);
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

    protected function processingImage()
    {
        if (empty($this->image)) {
            throw new \InvalidArgumentException('No image provided. Use setImage() first.');
        }

        // Validate the image
        $this->validationService->validateImage($this->image);

        // Fire ImageSaving event
        Event::dispatch(new ImageSaving(
            $this->image,
            $this->savedPath,
            [
                'dimensions' => $this->dimensions,
                'watermark' => $this->watermark,
                'compress' => $this->compress,
                'resize' => $this->resize,
            ]
        ));

        // Save the original image
        ['image' => $image, 'imageName' => $imageName, 'fullPath' => $fullPath] = $this->storageService->saveOriginal($this->image, $this->savedPath, $this->imageName, $this->extension);

        // Get original size before any modifications
        $originalSize = $this->storageService->getFileSize($fullPath);

        // Resize the image if dimensions are specified
        if (is_array($this->dimensions) && count($this->dimensions) > 0 && ($this->dimensions['width'] || $this->dimensions['height'])) {
            $this->resizeService->setDimensionsImage($fullPath, $this->dimensions, $this->aspectRatio);
        }

        // Apply watermark if specified
        if (is_array($this->watermark) && count($this->watermark) > 0 && isset($this->watermark['image'])) {
            $this->watermarkService->applyWatermark($fullPath, $this->watermark);
        }

        // Compress the image if compression is enabled
        if ($this->compress) {
            $this->compressionService->compressImage($fullPath, $this->compressionRatio);
        }

        // Resize the image to multiple sizes if specified
        if (is_array($this->resize) && count($this->resize) > 0) {
            $this->resizeService->resizeImage($this->savedPath, $imageName, $this->resize);
        }

        // Fire ImageSaved event
        Event::dispatch(new ImageSaved($imageName, $this->savedPath, $fullPath ?? ($this->savedPath . '/' . $imageName)));

        return $this->buildReturnData($imageName, $this->savedPath, $fullPath, $originalSize);
    }

    /**
     * Build return data based on config return_keys.
     *
     * @param string $imageName
     * @param string $path
     * @param string $fullPath
     * @param int $originalSize
     * @return string|array
     */
    protected function buildReturnData(string $imageName, string $path, string $fullPath, int $originalSize): string|array
    {
        $returnKeys = config('imagekit.return_keys', ['name']);

        // Get final file size (after all modifications)
        $size = $this->storageService->getFileSize($fullPath);

        // Get URL
        $url = $this->storageService->url($fullPath);

        // Get extension
        $extension = pathinfo($imageName, PATHINFO_EXTENSION);

        // Get mime type
        $mimeType = $this->getMimeType($extension);

        // Get image dimensions
        $imageDimensions = $this->getImageDimensions($fullPath);

        // Get disk
        $disk = $this->storageService->getDisk();

        // Get hash
        $hash = $this->getFileHash($fullPath);

        // Available data
        $availableData = [
            'name' => $imageName,
            'path' => $path,
            'full_path' => $fullPath,
            'size' => round($size / 1024, 2),
            'original_size' => round($originalSize / 1024, 2),
            'url' => $url,
            'extension' => $extension,
            'mime_type' => $mimeType,
            'width' => $imageDimensions['width'],
            'height' => $imageDimensions['height'],
            'disk' => $disk,
            'hash' => $hash,
            'created_at' => now()->toDateTimeString(),
        ];

        // If only one key, return string
        if (count($returnKeys) === 1) {
            $key = $returnKeys[0];
            return $availableData[$key] ?? $imageName;
        }

        // If multiple keys, return array
        $result = [];
        foreach ($returnKeys as $key) {
            if (isset($availableData[$key])) {
                $result[$key] = $availableData[$key];
            }
        }

        return $result;
    }

    /**
     * Get MIME type from extension.
     *
     * @param string $extension
     * @return string
     */
    protected function getMimeType(string $extension): string
    {
        $extension = strtolower($extension);

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
     * Get image dimensions (width and height).
     *
     * @param string $fullPath
     * @return array
     */
    protected function getImageDimensions(string $fullPath): array
    {
        $absolutePath = $this->storageService->path($fullPath);

        if (file_exists($absolutePath)) {
            $imageInfo = @getimagesize($absolutePath);
            if ($imageInfo) {
                return [
                    'width' => $imageInfo[0],
                    'height' => $imageInfo[1],
                ];
            }
        }

        return ['width' => null, 'height' => null];
    }

    /**
     * Get file MD5 hash.
     *
     * @param string $fullPath
     * @return string|null
     */
    protected function getFileHash(string $fullPath): ?string
    {
        $absolutePath = $this->storageService->path($fullPath);

        if (file_exists($absolutePath)) {
            return md5_file($absolutePath);
        }

        return null;
    }

    /**
     * Reset the handler state to default values.
     * This is important when using singleton pattern to avoid state pollution.
     *
     * @return $this
     */
    public function reset(): self
    {
        $this->image = null;
        $this->images = [];
        $this->imageName = null;
        $this->extension = null;
        $this->compress = true;
        $this->compressionRatio = null;
        $this->resize = null;
        $this->dimensions = null;
        $this->aspectRatio = config('imagekit.aspectRatio', true);
        $this->watermark = config('imagekit.enable_watermark') ? config('imagekit.watermark') : null;
        $defaultPath = config('imagekit.default_saved_path', 'uploads/images');
        $this->savedPath = $this->normalizePath($defaultPath);
        $this->resizeOptions = (array) config('imagekit.multi_size_dimensions');
        
        // Reset disk on all services to default
        $defaultDisk = config('imagekit.disk', 'public');
        $this->storageService->setDisk($defaultDisk);
        $this->resizeService->setDisk($defaultDisk);
        $this->compressionService->setDisk($defaultDisk);
        $this->watermarkService->setDisk($defaultDisk);
        
        return $this;
    }

    /**
     * Normalize path to ensure it's always a relative path.
     * Converts absolute paths to relative paths or throws exception.
     *
     * @param string $path
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function normalizePath(string $path): string
    {
        // If path is empty, return default
        if (empty($path)) {
            return 'uploads/images';
        }

        // Check if it's an absolute path
        $isAbsolute = str_starts_with($path, '/') || preg_match('/^[a-zA-Z]:\\\\/', $path);
        
        if ($isAbsolute) {
            // Try to convert absolute path to relative
            // Extract only the relative part from common Laravel paths
            $basePath = base_path();
            $storagePath = storage_path('app/public');
            $publicPath = public_path();
            
            // Remove base path if found
            if (str_starts_with($path, $basePath)) {
                $path = str_replace($basePath . '/', '', $path);
                // If it starts with public/, remove it
                if (str_starts_with($path, 'public/')) {
                    $path = str_replace('public/', '', $path);
                }
            } elseif (str_starts_with($path, $storagePath)) {
                // If it's in storage/app/public, extract the relative part
                $path = str_replace($storagePath . '/', '', $path);
            } elseif (str_starts_with($path, $publicPath)) {
                // If it's in public/, extract the relative part
                $path = str_replace($publicPath . '/', '', $path);
            } else {
                // Cannot convert, throw exception
                throw new \InvalidArgumentException(
                    "Absolute paths are not allowed in config. Please use a relative path instead. " .
                    "Provided path: {$path}. Use 'uploads/images' instead of an absolute path."
                );
            }
        }
        
        // Remove leading slashes
        $path = ltrim($path, '/');
        
        // Normalize path separators
        $path = str_replace('\\', '/', $path);
        $path = str_replace('//', '/', $path);
        
        // Validate and return
        $this->validationService->validatePath($path);
        
        return rtrim($path, '/');
    }

}
