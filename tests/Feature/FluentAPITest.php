<?php

namespace DevMahmoudMustafa\ImageKit\Tests\Feature;

use DevMahmoudMustafa\ImageKit\Facades\ImageKit;
use DevMahmoudMustafa\ImageKit\Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FluentAPITest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_supports_all_fluent_aliases()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        $watermark = UploadedFile::fake()->image('watermark.png');
        
        // Save actual image content instead of 'fake content'
        Storage::disk('public')->put('watermark.png', file_get_contents($watermark->getRealPath()));

        config(['imagekit.multi_size_dimensions' => [
            'small' => ['width' => 300, 'height' => 300],
        ]]);

        $imageName = ImageKit::make()
            ->image($image)              // Alias for setImage
            ->name('custom')             // Alias for setImageName
            ->extension('webp')          // Alias for setExtension
            ->path('custom/path')        // Alias for setImagePath
            ->resize(800, 600)           // Alias for setDimensions
            ->compress(85)               // Alias for compressImage
            ->watermark('watermark.png', 'bottom-right', 50)  // Alias for setWatermark
            ->sizes(['small'])           // Alias for setMultiSizeOptions
            ->save();                    // Alias for saveImage

        $this->assertNotNull($imageName);
        Storage::disk('public')->assertExists('custom/path/' . $imageName);
    }

    /** @test */
    public function it_supports_saveTo_alias()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        
        $imageName = ImageKit::image($image)
            ->saveTo('custom/path')
            ->save();

        Storage::disk('public')->assertExists('custom/path/' . $imageName);
    }

    /** @test */
    public function it_supports_dimensions_alias()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        
        $imageName = ImageKit::image($image)
            ->dimensions(800, 600)
            ->save();

        $this->assertNotNull($imageName);
    }

    /** @test */
    public function it_supports_images_alias_for_gallery()
    {
        $images = [
            UploadedFile::fake()->image('test1.jpg'),
            UploadedFile::fake()->image('test2.jpg'),
        ];
        
        $result = ImageKit::images($images)->saveGallery();

        $this->assertCount(2, $result);
    }
}

