<?php

namespace DevMahmoudMustafa\ImageKit\Tests\Unit;

use DevMahmoudMustafa\ImageKit\Services\WatermarkService;
use DevMahmoudMustafa\ImageKit\Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class WatermarkServiceTest extends TestCase
{
    protected WatermarkService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->service = new WatermarkService();
    }

    /** @test */
    public function it_can_apply_watermark_from_storage()
    {
        $image = UploadedFile::fake()->image('test.jpg', 800, 600);
        $watermark = UploadedFile::fake()->image('watermark.png', 100, 100);
        
        Storage::disk('public')->put('uploads/images/test.jpg', file_get_contents($image->getRealPath()));
        Storage::disk('public')->put('watermark.png', file_get_contents($watermark->getRealPath()));

        $watermarkConfig = [
            'image' => 'watermark.png',
            'position' => 'bottom-right',
            'opacity' => 50,
        ];

        $this->service->applyWatermark('uploads/images/test.jpg', $watermarkConfig);

        $this->assertTrue(Storage::disk('public')->exists('uploads/images/test.jpg'));
    }

    /** @test */
    public function it_can_apply_watermark_from_absolute_path()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        $watermark = UploadedFile::fake()->image('watermark.png');
        
        Storage::disk('public')->put('uploads/images/test.jpg', file_get_contents($image->getRealPath()));
        
        $tempWatermark = tempnam(sys_get_temp_dir(), 'watermark');
        file_put_contents($tempWatermark, file_get_contents($watermark->getRealPath()));

        $watermarkConfig = [
            'image' => $tempWatermark,
            'position' => 'center',
        ];

        $this->service->applyWatermark('uploads/images/test.jpg', $watermarkConfig);

        unlink($tempWatermark);
        $this->assertTrue(Storage::disk('public')->exists('uploads/images/test.jpg'));
    }

    /** @test */
    public function it_throws_exception_when_watermark_image_missing()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        Storage::disk('public')->put('uploads/images/test.jpg', file_get_contents($image->getRealPath()));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Watermark image file does not exist');

        $watermarkConfig = [
            'image' => 'nonexistent.png',
            'position' => 'bottom-right',
        ];

        $this->service->applyWatermark('uploads/images/test.jpg', $watermarkConfig);
    }

    /** @test */
    public function it_throws_exception_when_watermark_path_not_provided()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        Storage::disk('public')->put('uploads/images/test.jpg', file_get_contents($image->getRealPath()));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Watermark image path is required');

        $this->service->applyWatermark('uploads/images/test.jpg', []);
    }

    /** @test */
    public function it_throws_exception_when_main_image_not_found()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Image file does not exist');

        $watermarkConfig = [
            'image' => 'watermark.png',
            'position' => 'bottom-right',
        ];

        $this->service->applyWatermark('uploads/images/nonexistent.jpg', $watermarkConfig);
    }

    /** @test */
    public function it_can_set_disk()
    {
        Storage::fake('s3');
        
        $this->service->setDisk('s3');
        
        $this->assertEquals('s3', $this->service->getDisk());
    }

    /** @test */
    public function it_can_apply_watermark_with_dimensions()
    {
        $image = UploadedFile::fake()->image('test.jpg', 800, 600);
        $watermark = UploadedFile::fake()->image('watermark.png', 200, 200);
        
        Storage::disk('public')->put('uploads/images/test.jpg', file_get_contents($image->getRealPath()));
        Storage::disk('public')->put('watermark.png', file_get_contents($watermark->getRealPath()));

        $watermarkConfig = [
            'image' => 'watermark.png',
            'position' => 'bottom-right',
            'width' => 100,
            'height' => 100,
        ];

        $this->service->applyWatermark('uploads/images/test.jpg', $watermarkConfig);

        $this->assertTrue(Storage::disk('public')->exists('uploads/images/test.jpg'));
    }

    /** @test */
    public function it_can_resize_watermark_image()
    {
        $watermark = UploadedFile::fake()->image('watermark.png', 200, 200);
        Storage::disk('public')->put('watermark.png', file_get_contents($watermark->getRealPath()));

        $resizedPath = $this->service->resizeWatermarkImage('watermark.png', 100, 100);

        $this->assertNotEquals('watermark.png', $resizedPath);
        $this->assertStringContainsString('100x100', $resizedPath);
        $this->assertTrue(Storage::disk('public')->exists($resizedPath));
    }

    /** @test */
    public function it_returns_same_path_when_resizing_with_null_dimensions()
    {
        $watermark = UploadedFile::fake()->image('watermark.png');
        Storage::disk('public')->put('watermark.png', file_get_contents($watermark->getRealPath()));

        $path = $this->service->resizeWatermarkImage('watermark.png', null, null);

        $this->assertEquals('watermark.png', $path);
    }
}

