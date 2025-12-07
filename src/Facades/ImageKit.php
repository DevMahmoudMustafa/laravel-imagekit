<?php

namespace DevMahmoudMustafa\ImageKit\Facades;

use DevMahmoudMustafa\ImageKit\Contracts\ImageHandlerInterface;
use DevMahmoudMustafa\ImageKit\Processors\SaveGalleryHandler;
use DevMahmoudMustafa\ImageKit\Processors\SaveSingleImageHandler;
use Illuminate\Support\Facades\Facade;


/**
 * @method static ImageKitService setImage(mixed $image)
 * @method static ImageKitService image(mixed $image)
 * @method static ImageKitService setImages(array $images)
 * @method static ImageKitService images(array $images)
 * @method static ImageKitService setImageName(string $name)
 * @method static ImageKitService name(string $name)
 * @method static ImageKitService setExtension(string $extension)
 * @method static ImageKitService extension(string $extension)
 * @method static ImageKitService setImagePath(string $path)
 * @method static ImageKitService path(string $path)
 * @method static ImageKitService saveTo(string $path)
 * @method static ImageKitService setDimensions(int|null $width = null, int|null $height = null)
 * @method static ImageKitService resize(int|null $width = null, int|null $height = null)
 * @method static ImageKitService dimensions(int|null $width = null, int|null $height = null)
 * @method static ImageKitService setWatermark(string|\Illuminate\Http\UploadedFile|null $imagePathOrFile = null, string|null $position = null, int|null $opacity = null, array|null $offset = null, int|null $x = null, int|null $y = null, int|null $width = null, int|null $height = null)
 * @method static ImageKitService watermark(string|\Illuminate\Http\UploadedFile|null $imagePathOrFile = null, string|null $position = null, int|null $opacity = null, array|null $offset = null, int|null $x = null, int|null $y = null, int|null $width = null, int|null $height = null)
 * @method static ImageKitService compressImage(bool $compress = true, int|null $compressionRatio = null)
 * @method static ImageKitService compress(bool $compress = true, int|null $quality = null)
 * @method static ImageKitService setMultiSizeOptions(array|null $resize = null)
 * @method static ImageKitService sizes(array|null $sizes = null)
 * @method static ImageKitService reset()
 * @method static ImageKitService setDisk(string $disk)
 * @method static string getDisk()
 * @method static string saveImage()
 * @method static string save()
 * @method static array saveGallery(string|null $imageColumnName = null, string|null $fkColumnName = null, int|null $fkId = null, string|array|null $altText = null)
 * @method static bool deleteImage(string $imageName, string $path, array|null $sizes = null)
 * @method static int deleteGallery(array $imagesNames, string $path, array|null $sizes = null)
 * @method static string|null getImage(string $path, string|null $disk = null)
 * @method static string getImageUrl(string $path, string|null $disk = null)
 * @method static string getImagePath(string $path, string|null $disk = null)
 * @method static bool imageExists(string $path, string|null $disk = null)
 * @method static \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\StreamedResponse response(string $path, string|null $disk = null, array $options = [])
 * @method static \Symfony\Component\HttpFoundation\StreamedResponse download(string $path, string|null $name = null, string|null $disk = null, array $options = [])
 * @method static string temporaryUrl(string $path, \DateTimeInterface|\DateInterval|int $expiration, string|null $disk = null, array $options = [])
 */
class ImageKit extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'imagekit';
    }
}
