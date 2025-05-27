<?php

namespace MetaFramework\Mediaclass\Controllers;

use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Interfaces\ImageInterface;
use MetaFramework\Mediaclass\Config;
use MetaFramework\Mediaclass\Cropable;
use MetaFramework\Mediaclass\Interfaces\MediaclassInterface;
use MetaFramework\Mediaclass\Models\Media;
use MetaFramework\Mediaclass\Path;
use MetaFramework\Traits\Responses;
use ReflectionClass;
use Throwable;

class FileUploadImages
{
    use Responses;

    protected ImageInterface $image;
    protected MediaclassInterface $model;
    protected array $dimensions;
    protected array $urls = [];

    protected string $mime_type;
    protected string $filename;
    protected object $uploadedFile;

    private Media $media;
    private ?string $temp;
    private ?int $model_id;
    private string $folder_name = '';

    private Filesystem $disk;
    private ImageManager $imageManager;

    public function __construct()
    {
        $this->model_id = (int)request('model_id') ?: null;
        $this->temp = request('mediaclass_temp_id') ?: null;

        $this->response['filetype'] = 'image';

        $this->disk = Config::getDisk();

        // Initialize ImageManager with GD driver (you can switch to ImagickDriver if needed)
        $this->imageManager = new ImageManager(new GdDriver());
    }

    public function setModel(?string $model = null): static
    {
        if (!$model) {
            $this->responseError(__('mediaclass.missing_model'));
            return $this;
        }

        try {
            $this->model = (new ReflectionClass($model))->newInstance();

            if ($this->model_id) {
                $this->model = $this->model->find($this->model_id);
            }
            $this->folder_name = Path::mediaFolderName($this->model);

        } catch (Throwable $e) {
            $this->responseException($e, "Unknown " . $model . " class in " . static::class);
        }

        return $this;
    }

    /**
     * Deletes a Mediaclass record and all its files
     * Deletes the relative directory if it is empty
     * @return $this
     */
    public function delete(): static
    {
        try {
            $media = Media::query()->find(request('id'));
            $path = Path::mediaFolderName($media->model);
            File::delete(
                File::glob(
                    $this->disk->path($path . DIRECTORY_SEPARATOR . '*' . $media->filename . '*')
                )
            );

            if (count($this->disk->files($path)) === 0) {
                $this->disk->deleteDirectory($path);
            }

            $media->delete();
        } catch (Throwable $e) {
            $this->responseException($e);
            report($e);
        }
        return $this;
    }

    public function upload(): static
    {
        $this->filename = Str::random(6);
        $this->uploadedFile = request()->file('files')[0];

        if ($this->hasErrors()) {
            return $this;
        }

        // documents
        if (strstr($this->uploadedFile->getMimeType(), '/', true) != 'image') {
            // Move non-image files
            // TODO: controls
            return $this->uploadFiles();
        }

        $this->response['has_positions'] = (bool)request('positions');

        // svg
        if (str_contains($this->uploadedFile->getMimeType(), 'svg')) {
            return $this->uploadSvg();
        }

        if (strstr($this->uploadedFile->getMimeType(), '/', true) != 'image') {
            $this->responseAbort(trans('mediaclass.errors.mustBeImage'));
            return $this;
        }

        return $this->processImage();
    }

    private function uploadFiles(): static
    {
        $this->response['filetype'] = 'file';
        $this->response['filename'] = $this->filename . '.' . $this->uploadedFile->guessExtension();
        $this->response['link'] = $this->disk->url($this->folder_name.'/'.$this->response['filename'] . '?' . time());;
        $this->response['fileicon'] = asset('vendor/mfw/mediaclass/images/files/' . $this->uploadedFile->guessExtension() . '.png');
        $this->response['preview'] = $this->response['fileicon'];

        try {
            $this->media = $this->store();
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        $this->uploadedFile->move($this->disk->path($this->folder_name), $this->response['filename']);

        $this->mediaResponse();
        return $this;
    }

    private function uploadSvg(): static
    {
        $this->response['filename'] = $this->filename . '.svg';
        $file = $this->folder_name . '/' . $this->response['filename'];
        $img = $this->disk->url($file . '?' . time());
        $this->response['fileicon'] = asset('vendor/mfw/mediaclass/images/files/svg.png');

        try {
            $this->media = $this->store();
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        $this->uploadedFile->move($this->disk->path($this->folder_name), $this->response['filename']);

        $this->responseElement('link', $this->disk->url($file));
        $this->responseElement('preview', $img);

        $this->mediaResponse();

        return $this;
    }

    private function processImage(): static
    {
        $this->dimensions = config('mediaclass.dimensions');

        // V3: Use ImageManager->read() instead of Image::make()
        $this->image = $this->imageManager->read($this->uploadedFile);

        $this->urls = [];

        // V3: Get MIME type from the uploaded file directly
        $mimeType = $this->uploadedFile->getMimeType();

        $this->mime_type = (str_contains($mimeType, 'png') ? 'png' : 'jpg');
        $this->response['fileicon'] = asset('vendor/mfw/mediaclass/images/files/jpg.png');

        // V3: Use width() and height() methods directly
        $ratio = ($this->image->width() / $this->image->height()) > 1 ? 'h' : 'v';
        $this->response['ratio'] = $ratio;

        foreach ($this->dimensions as $key => $dimensions) {
            $file = $this->folder_name . '/' . $dimensions['width'] . '_' . $this->filename . '.' . $this->mime_type;

            // V3: Updated resize method - no closure callback needed for basic aspect ratio preservation
            $resizedImage = $this->image->resize(
                $dimensions['width'],
                $dimensions['height'],
                function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                }
            );

            // V3: Use the appropriate encoder method instead of encode() with string
            $encodedImage = $this->mime_type === 'png'
                ? $resizedImage->toPng()
                : $resizedImage->toJpeg(75);

            $this->disk->put($file, $encodedImage);

            if (in_array($key, ['xl', 'sm'])) {
                $this->urls[$key] = $this->disk->url($file . '?' . time());
            }
        }

        $this->responseElement('link', $this->urls['xl'] ?? Config::defaultImgUrl());
        $this->responseElement('preview', $this->urls['sm'] ?? Config::defaultImgUrl());
        $this->responseElement('urls', $this->urls);

        try {
            $this->media = $this->store();
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        $this->media->model = $this->model;
        $cropable = new Cropable($this->media);
        $cropable->setCropableFromComponent((string)request('cropable'));
        $this->responseElement('sizes', $cropable->printSizes());
        $this->responseElement('cropable_link', $cropable->link());

        $this->mediaResponse();

        return $this;
    }

    /**
     * @throws \Exception
     */
    private function store(): Media
    {
        if (config()->has('app.cacheables') & in_array($this->model->type, (array)config('app.cacheables'))) {
            cache()->forget($this->model->type);
        }

        $morphable = Relation::morphMap() ? array_key_first(array_filter(Relation::morphMap(), fn($item) => $item == get_class($this->model))) : get_class($this->model);
        $path = Path::mediaFolderName($this->model);

        if (!$morphable) {
            File::delete(File::glob($this->disk->path($path . DIRECTORY_SEPARATOR . '*' . $this->filename . '*')));
            throw new Exception("Invalid Media morphable");
        }

        return Media::query()->create([
            'model_type' => $morphable,
            'model_id' => $this->model_id,
            'group' => request('group') ?: Config::defaultGroup(),
            'subgroup' => request('subgroup') ?: null,
            'description' => request('description'),
            'position' => request('position') ?: 'left',
            'mime' => $this->uploadedFile->getMimeType(),
            'original_filename' => $this->uploadedFile->getClientOriginalName(),
            'filename' => $this->filename,
            'temp' => $this->temp
        ]);
    }

    private function mediaResponse(): static
    {
        $this->responseElement('uploaded', $this->media);
        $this->responseElement('temp', $this->temp);
        $this->responseElement('count_files', request('count_files'));
        return $this;
    }
}