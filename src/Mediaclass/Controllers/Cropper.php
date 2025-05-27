<?php

namespace MetaFramework\Mediaclass\Controllers;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use MetaFramework\Mediaclass\Config;
use MetaFramework\Mediaclass\Cropable;
use MetaFramework\Mediaclass\Models\Media;
use MetaFramework\Mediaclass\Path;
use MetaFramework\Traits\Responses;
use Throwable;

class Cropper
{
    use Responses;

    private ImageManager $imageManager;

    public function __construct()
    {
        // Initialize ImageManager with GD driver (you can switch to ImagickDriver if needed)
        $this->imageManager = new ImageManager(new GdDriver());
    }

    public static function crop(): array
    {
        $cropper = new Cropper;

        try {
            $media = Media::query()->findOrFail(request('object_id'));
            $file = $media->file('xl');

            // V3: Use ImageManager->read() instead of Image::make()
            $image = $cropper->imageManager->read($file);

            $filename = Path::mediaFolderName($media->bindedModel()) . '/cropped_' . $media->filename . '.' . $media->extension();

            // V3: Chain crop and resize operations, then encode
            $processedImage = $image
                ->crop(
                    (int)request('wimage'),
                    (int)request('himage'),
                    (int)request('x1image'),
                    (int)request('y1image')
                )
                ->resize(
                    request('wiimage'),
                    request('heimage'),
                    function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    }
                );

            // V3: Use the appropriate encoder method instead of encode() with string
            $encodedImage = str_contains($media->mime, 'png')
                ? $processedImage->toPng()
                : $processedImage->toJpeg(80);

            Config::getDisk()->put($filename, $encodedImage);

            $cropable = new Cropable($media);
            $cropable->setWidth((int)request('wiimage'));
            $cropable->setHeight((int)request('heimage'));

            $img = Storage::disk('media')->url($filename);
            $cropper->responseElement('sizes', $cropable->printSizes());
            $cropper->responseElement('cropable_link', $cropable->link());
            $cropper->responseElement('uploaded', $media);
            $cropper->responseElement('callback', 'cropped');
            $cropper->responseElement('urls', ['xl' => $img, 'sm' => $img]);

        } catch (Throwable $e) {
            $cropper->responseException($e);

        } finally {
            return $cropper->fetchResponse();
        }
    }
}