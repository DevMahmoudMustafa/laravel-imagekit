<?php

namespace DevMahmoudMustafa\ImageKit\Tests\Unit;

use DevMahmoudMustafa\ImageKit\Services\ResizeService;
use DevMahmoudMustafa\ImageKit\Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class ResizeServiceTest extends TestCase
{
    protected ResizeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->service = new ResizeService();
    }

    /** @test */
    public function it_can_resize_image_to_specific_dimensions()
    {
        $image = UploadedFile::fake()->image('test.jpg', 1200, 800);
        Storage::disk('public')->put('uploads/images/test.jpg', file_get_contents($image->getRealPath()));

        $this->service->setDimensionsImage('uploads/images/test.jpg', ['width' => 800, 'height' => 600], true);

        $this->assertTrue(Storage::disk('public')->exists('uploads/images/test.jpg'));
    }

    /** @test */
    public function it_throws_exception_when_image_not_found()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Image file does not exist');

        $this->service->setDimensionsImage('uploads/images/nonexistent.jpg', ['width' => 800, 'height' => 600], true);
    }

    /** @test */
    public function it_can_resize_image_to_multiple_sizes()
    {
        config(['imagekit.multi_size_dimensions' => [
            'small' => ['width' => 300, 'height' => 300],
            'medium' => ['width' => 600, 'height' => 600],
        ]]);

        $image = UploadedFile::fake()->image('test.jpg', 1200, 800);
        Storage::disk('public')->put('uploads/images/test.jpg', file_get_contents($image->getRealPath()));

        $this->service->resizeImage('uploads/images', 'test.jpg', ['small', 'medium']);

        Storage::disk('public')->assertExists('uploads/images/small_test.jpg');
        Storage::disk('public')->assertExists('uploads/images/medium_test.jpg');
    }

    /** @test */
    public function it_throws_exception_for_invalid_size_in_multiple_resize()
    {
        config(['imagekit.multi_size_dimensions' => [
            'small' => ['width' => 300, 'height' => 300],
        ]]);

        $image = UploadedFile::fake()->image('test.jpg');
        Storage::disk('public')->put('uploads/images/test.jpg', file_get_contents($image->getRealPath()));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Size 'large' is not defined");

        $this->service->resizeImage('uploads/images', 'test.jpg', ['small', 'large']);
    }

    /** @test */
    public function it_can_set_disk()
    {
        Storage::fake('s3');
        
        $this->service->setDisk('s3');
        
        $this->assertEquals('s3', $this->service->getDisk());
    }
}

