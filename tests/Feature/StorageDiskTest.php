<?php

namespace DevMahmoudMustafa\ImageKit\Tests\Feature;

use DevMahmoudMustafa\ImageKit\Facades\ImageKit;
use DevMahmoudMustafa\ImageKit\Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StorageDiskTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('s3');
        Storage::fake('local');
    }

    /** @test */
    public function it_can_save_to_public_disk()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        
        $imageName = ImageKit::setDisk('public')
            ->image($image)
            ->save();

        Storage::disk('public')->assertExists('uploads/images/' . $imageName);
    }

    /** @test */
    public function it_can_save_to_s3_disk()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        
        $imageName = ImageKit::setDisk('s3')
            ->image($image)
            ->save();

        Storage::disk('s3')->assertExists('uploads/images/' . $imageName);
        Storage::disk('public')->assertMissing('uploads/images/' . $imageName);
    }

    /** @test */
    public function it_can_switch_disks_dynamically()
    {
        $image1 = UploadedFile::fake()->image('test1.jpg');
        $image2 = UploadedFile::fake()->image('test2.jpg');
        
        $name1 = ImageKit::setDisk('public')
            ->image($image1)
            ->save();

        $name2 = ImageKit::setDisk('s3')
            ->image($image2)
            ->save();

        Storage::disk('public')->assertExists('uploads/images/' . $name1);
        Storage::disk('s3')->assertExists('uploads/images/' . $name2);
        Storage::disk('public')->assertMissing('uploads/images/' . $name2);
    }

    /** @test */
    public function it_propagates_disk_to_all_services()
    {
        $image = UploadedFile::fake()->image('test.jpg', 1200, 800);
        $watermark = UploadedFile::fake()->image('watermark.png');
        
        Storage::disk('s3')->put('watermark.png', file_get_contents($watermark->getRealPath()));

        config(['imagekit.multi_size_dimensions' => [
            'small' => ['width' => 300, 'height' => 300],
        ]]);

        $imageName = ImageKit::setDisk('s3')
            ->image($image)
            ->resize(800, 600)
            ->compress(85)
            ->watermark('watermark.png', 'bottom-right', 50)
            ->sizes(['small'])
            ->save();

        // All operations should use s3 disk
        Storage::disk('s3')->assertExists('uploads/images/' . $imageName);
        Storage::disk('s3')->assertExists('uploads/images/small_' . $imageName);
    }

    /** @test */
    public function it_deletes_from_correct_disk()
    {
        Storage::disk('s3')->put('uploads/images/test.jpg', 'fake content');
        Storage::disk('public')->put('uploads/images/test.jpg', 'fake content');
        
        $result = ImageKit::setDisk('s3')
            ->deleteImage('test.jpg', 'uploads/images');

        $this->assertTrue($result);
        Storage::disk('s3')->assertMissing('uploads/images/test.jpg');
        Storage::disk('public')->assertExists('uploads/images/test.jpg'); // Should still exist
    }

    /** @test */
    public function it_resets_disk_on_reset()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        
        // Set disk to s3
        $service = ImageKit::make();
        $service->setDisk('s3');
        
        // Reset service
        $service->reset();
        
        // After reset, should use default disk from config (public)
        $imageName = $service->image($image)->save();
        
        Storage::disk('public')->assertExists('uploads/images/' . $imageName);
        Storage::disk('s3')->assertMissing('uploads/images/' . $imageName);
    }
}

