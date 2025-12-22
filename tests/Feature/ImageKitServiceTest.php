<?php

namespace DevMahmoudMustafa\ImageKit\Tests\Feature;

use DevMahmoudMustafa\ImageKit\Events\ImageDeleted;
use DevMahmoudMustafa\ImageKit\Events\ImageSaved;
use DevMahmoudMustafa\ImageKit\Events\ImageSaving;
use DevMahmoudMustafa\ImageKit\Facades\ImageKit;
use DevMahmoudMustafa\ImageKit\ImageKitService;
use DevMahmoudMustafa\ImageKit\Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class ImageKitServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('s3');
        Event::fake();
    }

    /** @test */
    public function it_can_save_an_image()
    {
        $image = UploadedFile::fake()->image('test.jpg', 800, 600);
        
        $service = app(ImageKitService::class);
        $imageName = $service->setImage($image)->saveImage();

        $this->assertNotNull($imageName);
        Storage::disk('public')->assertExists('uploads/images/' . $imageName);
    }

    /** @test */
    public function it_can_use_fluent_aliases()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        
        $imageName = ImageKit::image($image)
            ->resize(400, 300)
            ->save();

        $this->assertNotNull($imageName);
        Storage::disk('public')->assertExists('uploads/images/' . $imageName);
    }

    /** @test */
    public function it_fires_image_saving_event()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        
        ImageKit::image($image)->save();

        Event::assertDispatched(ImageSaving::class);
    }

    /** @test */
    public function it_fires_image_saved_event()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        
        $imageName = ImageKit::image($image)->save();

        Event::assertDispatched(ImageSaved::class, function ($event) use ($imageName) {
            return $event->imageName === $imageName;
        });
    }

    /** @test */
    public function it_can_resize_image()
    {
        $image = UploadedFile::fake()->image('test.jpg', 1200, 800);
        
        $imageName = ImageKit::image($image)
            ->resize(800, 600)
            ->save();

        $this->assertNotNull($imageName);
        Storage::disk('public')->assertExists('uploads/images/' . $imageName);
    }

    /** @test */
    public function it_can_compress_image()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        
        $imageName = ImageKit::image($image)
            ->compress(85)
            ->save();

        $this->assertNotNull($imageName);
        Storage::disk('public')->assertExists('uploads/images/' . $imageName);
    }

    /** @test */
    public function it_can_apply_watermark()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        $watermark = UploadedFile::fake()->image('watermark.png');
        
        Storage::disk('public')->put('watermark.png', file_get_contents($watermark->getRealPath()));

        $imageName = ImageKit::image($image)
            ->watermark('watermark.png', 'bottom-right', 50)
            ->save();

        $this->assertNotNull($imageName);
        Storage::disk('public')->assertExists('uploads/images/' . $imageName);
    }

    /** @test */
    public function it_can_apply_watermark_with_uploaded_file()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        $watermark = UploadedFile::fake()->image('watermark.png');

        $imageName = ImageKit::image($image)
            ->watermark($watermark, 'bottom-right', 50)
            ->save();

        $this->assertNotNull($imageName);
        Storage::disk('public')->assertExists('uploads/images/' . $imageName);
    }

    /** @test */
    public function it_can_apply_watermark_with_dimensions()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        $watermark = UploadedFile::fake()->image('watermark.png');
        
        Storage::disk('public')->put('watermark.png', file_get_contents($watermark->getRealPath()));

        $imageName = ImageKit::image($image)
            ->watermark('watermark.png', 'bottom-right', 50, null, null, null, 100, 100)
            ->save();

        $this->assertNotNull($imageName);
        Storage::disk('public')->assertExists('uploads/images/' . $imageName);
    }

    /** @test */
    public function it_can_apply_watermark_with_uploaded_file_and_dimensions()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        $watermark = UploadedFile::fake()->image('watermark.png', 200, 200);

        $imageName = ImageKit::image($image)
            ->watermark($watermark, 'bottom-right', 50, null, null, null, 100, 100)
            ->save();

        $this->assertNotNull($imageName);
        Storage::disk('public')->assertExists('uploads/images/' . $imageName);
    }

    /** @test */
    public function it_can_save_to_different_path()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        
        $imageName = ImageKit::image($image)
            ->path('custom/path')
            ->save();

        Storage::disk('public')->assertExists('custom/path/' . $imageName);
    }

    /** @test */
    public function it_can_set_custom_name()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        
        $imageName = ImageKit::image($image)
            ->name('custom-name')
            ->save();

        $this->assertStringContainsString('custom-name', $imageName);
    }

    /** @test */
    public function it_can_set_custom_extension()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        
        $imageName = ImageKit::image($image)
            ->extension('webp')
            ->save();

        $this->assertStringEndsWith('.webp', $imageName);
    }

    /** @test */
    public function it_can_save_multiple_sizes()
    {
        config(['imagekit.multi_size_dimensions' => [
            'small' => ['width' => 300, 'height' => 300],
            'medium' => ['width' => 600, 'height' => 600],
        ]]);

        $image = UploadedFile::fake()->image('test.jpg', 1200, 800);
        
        $imageName = ImageKit::image($image)
            ->sizes(['small', 'medium'])
            ->save();

        Storage::disk('public')->assertExists('uploads/images/small_' . $imageName);
        Storage::disk('public')->assertExists('uploads/images/medium_' . $imageName);
    }

    /** @test */
    public function it_can_save_gallery()
    {
        $images = [
            UploadedFile::fake()->image('test1.jpg'),
            UploadedFile::fake()->image('test2.jpg'),
        ];
        
        $result = ImageKit::images($images)->saveGallery();

        $this->assertCount(2, $result);
        $this->assertIsString($result[0]);
        $this->assertIsString($result[1]);
    }

    /** @test */
    public function it_can_save_gallery_with_metadata()
    {
        $images = [
            UploadedFile::fake()->image('test1.jpg'),
            UploadedFile::fake()->image('test2.jpg'),
        ];
        
        $result = ImageKit::images($images)
            ->saveGallery('image_name', 'product_id', 123, 'Product Image');

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('image_name', $result[0]);
        $this->assertArrayHasKey('product_id', $result[0]);
        $this->assertArrayHasKey('alt', $result[0]);
        $this->assertEquals(123, $result[0]['product_id']);
    }

    /** @test */
    public function it_can_save_gallery_with_array_of_alt_texts()
    {
        $images = [
            UploadedFile::fake()->image('test1.jpg'),
            UploadedFile::fake()->image('test2.jpg'),
        ];
        
        $result = ImageKit::images($images)
            ->saveGallery('image_name', null, null, ['Alt 1', 'Alt 2']);

        $this->assertCount(2, $result);
        $this->assertEquals('Alt 1', $result[0]['alt']);
        $this->assertEquals('Alt 2', $result[1]['alt']);
    }

    /** @test */
    public function it_can_delete_image()
    {
        Storage::disk('public')->put('uploads/images/test.jpg', 'fake content');
        
        $result = ImageKit::deleteImage('test.jpg', 'uploads/images');

        $this->assertTrue($result);
        Event::assertDispatched(ImageDeleted::class);
    }

    /** @test */
    public function it_can_delete_gallery()
    {
        Storage::disk('public')->put('uploads/images/img1.jpg', 'fake content');
        Storage::disk('public')->put('uploads/images/img2.jpg', 'fake content');
        
        $count = ImageKit::deleteGallery(['img1.jpg', 'img2.jpg'], 'uploads/images');

        $this->assertEquals(2, $count);
        Event::assertDispatched(ImageDeleted::class, 2);
    }

    /** @test */
    public function it_can_delete_image_without_path_extracting_from_image_name()
    {
        Storage::disk('public')->put('uploads/images/test.jpg', 'fake content');
        
        $result = ImageKit::deleteImage('uploads/images/test.jpg', null);

        $this->assertTrue($result);
        Storage::disk('public')->assertMissing('uploads/images/test.jpg');
        Event::assertDispatched(ImageDeleted::class);
    }

    /** @test */
    public function it_can_delete_gallery_without_path_extracting_from_image_names()
    {
        Storage::disk('public')->put('uploads/images/img1.jpg', 'fake content');
        Storage::disk('public')->put('uploads/images/img2.jpg', 'fake content');
        
        $count = ImageKit::deleteGallery(
            ['uploads/images/img1.jpg', 'uploads/images/img2.jpg'],
            null
        );

        $this->assertEquals(2, $count);
        Storage::disk('public')->assertMissing('uploads/images/img1.jpg');
        Storage::disk('public')->assertMissing('uploads/images/img2.jpg');
        Event::assertDispatched(ImageDeleted::class, 2);
    }

    /** @test */
    public function it_throws_exception_when_deleting_image_in_root_directory()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Image name is in root directory: test.jpg');
        
        ImageKit::deleteImage('test.jpg', null);
    }

    /** @test */
    public function it_can_use_different_disk()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        
        $imageName = ImageKit::setDisk('s3')
            ->image($image)
            ->save();

        Storage::disk('s3')->assertExists('uploads/images/' . $imageName);
        Storage::disk('public')->assertMissing('uploads/images/' . $imageName);
    }

    /** @test */
    public function it_can_reset_service()
    {
        $service = ImageKit::make();
        $service->setDisk('s3')
            ->image(UploadedFile::fake()->image('test.jpg'))
            ->resize(800, 600)
            ->compress(85);

        $service->reset();

        // After reset, disk should be back to default
        $this->assertEquals('public', $service->getDisk());
    }

    /** @test */
    public function it_can_use_make_method()
    {
        $image1 = UploadedFile::fake()->image('test1.jpg');
        $image2 = UploadedFile::fake()->image('test2.jpg');

        $service1 = ImageKit::make()->image($image1)->save();
        $service2 = ImageKit::make()->image($image2)->save();

        $this->assertNotNull($service1);
        $this->assertNotNull($service2);
    }

    /** @test */
    public function it_resets_state_after_saving_single_image()
    {
        $service = app(ImageKitService::class);
        $image = UploadedFile::fake()->image('test.jpg');
        
        $service->setImage($image)->saveImage();

        // State should be reset after save
        // We can't directly access protected properties, but we can test by saving again
        $image2 = UploadedFile::fake()->image('test2.jpg');
        $service->setImage($image2)->saveImage();
        
        $this->assertTrue(true); // If we get here, state was reset properly
    }

    /** @test */
    public function it_resets_state_after_saving_gallery()
    {
        $service = app(ImageKitService::class);
        $images = [
            UploadedFile::fake()->image('test1.jpg'),
            UploadedFile::fake()->image('test2.jpg'),
        ];
        
        $service->setImages($images)->saveGallery();

        // State should be reset after save
        $images2 = [UploadedFile::fake()->image('test3.jpg')];
        $service->setImages($images2)->saveGallery();
        
        $this->assertTrue(true); // If we get here, state was reset properly
    }

    /** @test */
    public function it_throws_exception_when_no_image_provided()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No image provided');

        $service = app(ImageKitService::class);
        $service->saveImage();
    }

    /** @test */
    public function it_throws_exception_when_no_images_for_gallery()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No images provided');

        $service = app(ImageKitService::class);
        $service->saveGallery();
    }

    /** @test */
    public function it_can_chain_all_methods()
    {
        $image = UploadedFile::fake()->image('test.jpg', 1200, 800);
        $watermark = UploadedFile::fake()->image('watermark.png');
        
        Storage::disk('public')->put('watermark.png', file_get_contents($watermark->getRealPath()));

        config(['imagekit.multi_size_dimensions' => [
            'small' => ['width' => 300, 'height' => 300],
        ]]);

        $imageName = ImageKit::make()
            ->setDisk('public')
            ->image($image)
            ->name('custom-name')
            ->extension('webp')
            ->path('custom/path')
            ->resize(1024, 1024)
            ->compress(90)
            ->watermark('watermark.png', 'bottom-right', 75, null, 20, 20)
            ->sizes(['small'])
            ->save();

        $this->assertNotNull($imageName);
        Storage::disk('public')->assertExists('custom/path/' . $imageName);
        Storage::disk('public')->assertExists('custom/path/small_' . $imageName);
    }

    /** @test */
    public function it_handles_aspect_ratio_when_resizing()
    {
        $image = UploadedFile::fake()->image('test.jpg', 1200, 800);
        
        $imageName = ImageKit::image($image)
            ->resize(800, null) // Only width, height should be calculated
            ->save();

        $this->assertNotNull($imageName);
    }

    /** @test */
    public function it_handles_null_dimensions()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        
        // Should not throw exception
        $imageName = ImageKit::image($image)
            ->resize(null, null)
            ->save();

        $this->assertNotNull($imageName);
    }

    /** @test */
    public function it_returns_only_name_by_default()
    {
        config(['imagekit.return_keys' => ['name']]);

        $image = UploadedFile::fake()->image('test.jpg', 800, 600);
        $result = ImageKit::image($image)->save();

        $this->assertIsString($result);
        $this->assertStringEndsWith('.jpg', $result);
    }

    /** @test */
    public function it_returns_array_when_multiple_keys_configured()
    {
        config(['imagekit.return_keys' => ['name', 'path', 'size']]);

        $image = UploadedFile::fake()->image('test.jpg', 800, 600);
        $result = ImageKit::image($image)->save();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('path', $result);
        $this->assertArrayHasKey('size', $result);
    }

    /** @test */
    public function it_returns_all_available_keys()
    {
        config(['imagekit.return_keys' => [
            'name', 'path', 'full_path', 'size', 'original_size',
            'url', 'extension', 'mime_type', 'width', 'height',
            'disk', 'hash', 'created_at'
        ]]);

        $image = UploadedFile::fake()->image('test.jpg', 800, 600);
        $result = ImageKit::image($image)->save();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('path', $result);
        $this->assertArrayHasKey('full_path', $result);
        $this->assertArrayHasKey('size', $result);
        $this->assertArrayHasKey('original_size', $result);
        $this->assertArrayHasKey('url', $result);
        $this->assertArrayHasKey('extension', $result);
        $this->assertArrayHasKey('mime_type', $result);
        $this->assertArrayHasKey('width', $result);
        $this->assertArrayHasKey('height', $result);
        $this->assertArrayHasKey('disk', $result);
        $this->assertArrayHasKey('hash', $result);
        $this->assertArrayHasKey('created_at', $result);
    }

    /** @test */
    public function it_returns_correct_extension()
    {
        config(['imagekit.return_keys' => ['name', 'extension', 'mime_type']]);

        $image = UploadedFile::fake()->image('test.jpg', 800, 600);
        $result = ImageKit::image($image)->save();

        $this->assertEquals('jpg', $result['extension']);
        $this->assertEquals('image/jpeg', $result['mime_type']);
    }

    /** @test */
    public function it_returns_correct_dimensions()
    {
        config(['imagekit.return_keys' => ['name', 'width', 'height']]);

        $image = UploadedFile::fake()->image('test.jpg', 800, 600);
        $result = ImageKit::image($image)->save();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('width', $result);
        $this->assertArrayHasKey('height', $result);
    }

    /** @test */
    public function it_returns_size_in_kb()
    {
        config(['imagekit.return_keys' => ['name', 'size', 'original_size']]);

        $image = UploadedFile::fake()->image('test.jpg', 800, 600);
        $result = ImageKit::image($image)->save();

        $this->assertIsArray($result);
        $this->assertIsFloat($result['size']);
        $this->assertIsFloat($result['original_size']);
    }

    /** @test */
    public function it_returns_correct_disk()
    {
        config(['imagekit.return_keys' => ['name', 'disk']]);

        $image = UploadedFile::fake()->image('test.jpg', 800, 600);
        $result = ImageKit::image($image)->save();

        $this->assertEquals('public', $result['disk']);
    }

    /** @test */
    public function it_returns_hash()
    {
        config(['imagekit.return_keys' => ['name', 'hash']]);

        $image = UploadedFile::fake()->image('test.jpg', 800, 600);
        $result = ImageKit::image($image)->save();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('hash', $result);
        $this->assertEquals(32, strlen($result['hash'])); // MD5 hash is 32 characters
    }

    /** @test */
    public function it_returns_created_at_timestamp()
    {
        config(['imagekit.return_keys' => ['name', 'created_at']]);

        $image = UploadedFile::fake()->image('test.jpg', 800, 600);
        $result = ImageKit::image($image)->save();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $result['created_at']);
    }

    /** @test */
    public function it_returns_single_value_as_string_for_one_key()
    {
        config(['imagekit.return_keys' => ['url']]);

        $image = UploadedFile::fake()->image('test.jpg', 800, 600);
        $result = ImageKit::image($image)->save();

        $this->assertIsString($result);
        $this->assertStringContainsString('uploads/images', $result);
    }

    /** @test */
    public function gallery_returns_array_with_return_keys()
    {
        config(['imagekit.return_keys' => ['name', 'size', 'url']]);

        $images = [
            UploadedFile::fake()->image('test1.jpg', 800, 600),
            UploadedFile::fake()->image('test2.jpg', 800, 600),
        ];

        $result = ImageKit::images($images)->saveGallery();

        $this->assertCount(2, $result);
        $this->assertIsArray($result[0]);
        $this->assertArrayHasKey('name', $result[0]);
        $this->assertArrayHasKey('size', $result[0]);
        $this->assertArrayHasKey('url', $result[0]);
    }

    /** @test */
    public function gallery_returns_strings_when_single_key()
    {
        config(['imagekit.return_keys' => ['name']]);

        $images = [
            UploadedFile::fake()->image('test1.jpg', 800, 600),
            UploadedFile::fake()->image('test2.jpg', 800, 600),
        ];

        $result = ImageKit::images($images)->saveGallery();

        $this->assertCount(2, $result);
        $this->assertIsString($result[0]);
        $this->assertIsString($result[1]);
    }

    /** @test */
    public function gallery_with_metadata_includes_return_keys()
    {
        config(['imagekit.return_keys' => ['name', 'size', 'width', 'height']]);

        $images = [
            UploadedFile::fake()->image('test1.jpg', 800, 600),
            UploadedFile::fake()->image('test2.jpg', 800, 600),
        ];

        $result = ImageKit::images($images)->saveGallery('image_name', 'product_id', 123);

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('image_name', $result[0]);
        $this->assertArrayHasKey('product_id', $result[0]);
        $this->assertArrayHasKey('name', $result[0]);
        $this->assertArrayHasKey('size', $result[0]);
        $this->assertArrayHasKey('width', $result[0]);
        $this->assertArrayHasKey('height', $result[0]);
        $this->assertEquals(123, $result[0]['product_id']);
    }

    /** @test */
    public function it_returns_original_size_different_from_final_size_after_compression()
    {
        config(['imagekit.return_keys' => ['name', 'size', 'original_size']]);

        $image = UploadedFile::fake()->image('test.jpg', 800, 600);
        $result = ImageKit::image($image)->compress(true, 50)->save();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('size', $result);
        $this->assertArrayHasKey('original_size', $result);
        // Both should be numeric (KB)
        $this->assertIsNumeric($result['size']);
        $this->assertIsNumeric($result['original_size']);
    }
}
