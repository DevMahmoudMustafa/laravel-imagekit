<?php

namespace DevMahmoudMustafa\ImageKit\Tests\Unit;

use DevMahmoudMustafa\ImageKit\Services\ValidationService;
use DevMahmoudMustafa\ImageKit\Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class ValidationServiceTest extends TestCase
{
    protected ValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->service = new ValidationService();
    }

    /** @test */
    public function it_validates_valid_image()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        
        // Should not throw exception
        $this->service->validateImage($image);
        
        $this->assertTrue(true);
    }

    /** @test */
    public function it_rejects_invalid_image()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid image file provided.');
        
        $this->service->validateImage(null);
    }

    /** @test */
    public function it_rejects_invalid_extension()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported file extension');
        
        $file = UploadedFile::fake()->create('test.pdf', 100);
        $this->service->validateImage($file);
    }

    /** @test */
    public function it_validates_allowed_extensions()
    {
        $extensions = ['jpg', 'jpeg', 'png', 'webp'];
        
        foreach ($extensions as $ext) {
            $image = UploadedFile::fake()->image("test.{$ext}");
            $this->service->validateImage($image);
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function it_uses_allowed_extensions_from_config()
    {
        // Set custom allowed extensions in config
        config(['imagekit.allowed_extensions' => ['jpg', 'png']]);
        
        // JPG should be allowed
        $jpgImage = UploadedFile::fake()->image('test.jpg');
        $this->service->validateImage($jpgImage);
        $this->assertTrue(true);
        
        // PNG should be allowed
        $pngImage = UploadedFile::fake()->image('test.png');
        $this->service->validateImage($pngImage);
        $this->assertTrue(true);
        
        // WebP should be rejected (not in custom config)
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported file extension');
        
        $webpImage = UploadedFile::fake()->image('test.webp');
        $this->service->validateImage($webpImage);
    }

    /** @test */
    public function it_validates_file_size_limit()
    {
        config(['imagekit.max_file_size' => 100]); // 100KB
        
        $largeImage = UploadedFile::fake()->image('test.jpg')->size(200 * 1024); // 200KB
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('File size exceeds maximum');
        
        $this->service->validateImage($largeImage);
    }

    /** @test */
    public function it_allows_file_within_size_limit()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        $actualSizeKB = ceil($image->getSize() / 1024);
        
        // Set limit to be larger than the actual file size
        config(['imagekit.max_file_size' => $actualSizeKB + 100]);
        
        // Should not throw exception
        $this->service->validateImage($image);
        $this->assertTrue(true);
    }

    /** @test */
    public function it_validates_max_dimensions()
    {
        $largeImage = UploadedFile::fake()->image('test.jpg', 200, 200);
        
        // Get actual dimensions from the fake image
        $imageInfo = @getimagesize($largeImage->getRealPath());
        
        if ($imageInfo === false) {
            // If we can't read dimensions from fake image, skip this test
            $this->markTestSkipped('Cannot read dimensions from fake image (expected in test environment)');
            return;
        }
        
        [$actualWidth, $actualHeight] = $imageInfo;
        
        // Set limits smaller than actual dimensions to trigger validation
        config(['imagekit.max_dimensions' => [
            'width' => max(1, $actualWidth - 10),
            'height' => max(1, $actualHeight - 10),
        ]]);
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exceeds maximum allowed');
        
        $this->service->validateImage($largeImage);
    }

    /** @test */
    public function it_allows_image_within_dimension_limits()
    {
        config(['imagekit.max_dimensions' => ['width' => 2000, 'height' => 2000]]);
        
        $image = UploadedFile::fake()->image('test.jpg', 800, 600);
        
        $this->service->validateImage($image);
        $this->assertTrue(true);
    }

    /** @test */
    public function it_validates_image_name()
    {
        $this->service->validateImageName('test-image');
        $this->assertTrue(true);
    }

    /** @test */
    public function it_rejects_empty_image_name()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Image name cannot be empty');
        
        $this->service->validateImageName('');
    }

    /** @test */
    public function it_validates_extension()
    {
        $validExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        
        foreach ($validExtensions as $ext) {
            $this->service->validateExtension($ext);
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function it_rejects_invalid_file_extension()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid file extension');
        
        $this->service->validateExtension('pdf');
    }

    /** @test */
    public function it_rejects_empty_extension()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Extension cannot be empty');
        
        $this->service->validateExtension('');
    }

    /** @test */
    public function it_validates_path()
    {
        $this->service->validatePath('uploads/images');
        $this->assertTrue(true);
    }

    /** @test */
    public function it_rejects_empty_path()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Path is invalid');
        
        $this->service->validatePath('');
    }

    /** @test */
    public function it_validates_dimensions_with_null_values()
    {
        // Both null should be allowed (no resizing)
        $this->service->validateDimensions(null, null);
        $this->assertTrue(true);
    }

    /** @test */
    public function it_validates_dimensions_with_numeric_values()
    {
        $this->service->validateDimensions(800, 600);
        $this->service->validateDimensions(800, null);
        $this->service->validateDimensions(null, 600);
        $this->assertTrue(true);
    }

    /** @test */
    public function it_rejects_non_numeric_width()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Width must be a numeric value');
        
        $this->service->validateDimensions('invalid', 600);
    }

    /** @test */
    public function it_rejects_non_numeric_height()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Height must be a numeric value');
        
        $this->service->validateDimensions(800, 'invalid');
    }

    /** @test */
    public function it_validates_resize_options()
    {
        $resizeOptions = [
            'small' => ['width' => 300, 'height' => 300],
            'medium' => ['width' => 600, 'height' => 600],
        ];
        
        $this->service->validateResizeOptions(['small', 'medium'], $resizeOptions);
        $this->assertTrue(true);
    }

    /** @test */
    public function it_rejects_invalid_resize_option()
    {
        $resizeOptions = [
            'small' => ['width' => 300, 'height' => 300],
        ];
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Size 'large' is not defined");
        
        $this->service->validateResizeOptions(['small', 'large'], $resizeOptions);
    }

    /** @test */
    public function it_rejects_null_resize_options()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Resize options are not defined');
        
        $this->service->validateResizeOptions(['small'], null);
    }

    /** @test */
    public function it_validates_watermark_with_relative_path()
    {
        Storage::disk('public')->put('watermark.png', 'fake content');
        
        $watermark = [
            'image' => 'watermark.png',
            'position' => 'bottom-right',
            'opacity' => 50,
        ];
        
        $this->service->validateWatermark($watermark, 'public');
        $this->assertTrue(true);
    }

    /** @test */
    public function it_validates_watermark_with_absolute_path()
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'watermark');
        file_put_contents($tempFile, 'fake content');
        
        $watermark = [
            'image' => $tempFile,
            'position' => 'bottom-right',
        ];
        
        $this->service->validateWatermark($watermark);
        
        unlink($tempFile);
        $this->assertTrue(true);
    }

    /** @test */
    public function it_rejects_watermark_without_image()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Watermark image file is required');
        
        $watermark = ['position' => 'bottom-right'];
        $this->service->validateWatermark($watermark);
    }

    /** @test */
    public function it_rejects_invalid_watermark_position()
    {
        Storage::disk('public')->put('watermark.png', 'fake content');
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid watermark position');
        
        $watermark = [
            'image' => 'watermark.png',
            'position' => 'invalid-position',
        ];
        
        $this->service->validateWatermark($watermark, 'public');
    }

    /** @test */
    public function it_validates_valid_watermark_positions()
    {
        Storage::disk('public')->put('watermark.png', 'fake content');
        
        $positions = ['top-left', 'top-right', 'bottom-left', 'bottom-right', 'center'];
        
        foreach ($positions as $position) {
            $watermark = [
                'image' => 'watermark.png',
                'position' => $position,
            ];
            
            $this->service->validateWatermark($watermark, 'public');
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function it_rejects_invalid_watermark_opacity()
    {
        Storage::disk('public')->put('watermark.png', 'fake content');
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Watermark opacity must be between 0 and 100');
        
        $watermark = [
            'image' => 'watermark.png',
            'opacity' => 150,
        ];
        
        $this->service->validateWatermark($watermark, 'public');
    }

    /** @test */
    public function it_allows_empty_watermark_array()
    {
        // Empty watermark array should be allowed (watermark disabled)
        $this->service->validateWatermark([]);
        $this->assertTrue(true);
    }
}
