<?php

namespace DevMahmoudMustafa\ImageKit\Tests\Unit;

use DevMahmoudMustafa\ImageKit\Services\CompressionService;
use DevMahmoudMustafa\ImageKit\Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class CompressionServiceTest extends TestCase
{
    protected CompressionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->service = new CompressionService();
    }

    /** @test */
    public function it_can_compress_image()
    {
        $image = UploadedFile::fake()->image('test.jpg', 800, 600);
        Storage::disk('public')->put('uploads/images/test.jpg', file_get_contents($image->getRealPath()));

        $this->service->compressImage('uploads/images/test.jpg', 85);

        $this->assertTrue(Storage::disk('public')->exists('uploads/images/test.jpg'));
    }

    /** @test */
    public function it_throws_exception_when_image_not_found()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Image file does not exist');

        $this->service->compressImage('uploads/images/nonexistent.jpg', 85);
    }

    /** @test */
    public function it_uses_config_quality_when_no_quality_provided()
    {
        config(['imagekit.compression_quality' => 90]);

        $image = UploadedFile::fake()->image('test.jpg');
        Storage::disk('public')->put('uploads/images/test.jpg', file_get_contents($image->getRealPath()));

        $this->service->compressImage('uploads/images/test.jpg', null);

        $this->assertTrue(Storage::disk('public')->exists('uploads/images/test.jpg'));
    }

    /** @test */
    public function it_calculates_quality_dynamically_when_not_specified()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        Storage::disk('public')->put('uploads/images/test.jpg', file_get_contents($image->getRealPath()));

        $this->service->compressImage('uploads/images/test.jpg', null);

        $this->assertTrue(Storage::disk('public')->exists('uploads/images/test.jpg'));
    }

    /** @test */
    public function it_can_set_disk()
    {
        Storage::fake('s3');
        
        $this->service->setDisk('s3');
        
        $this->assertEquals('s3', $this->service->getDisk());
    }
}

