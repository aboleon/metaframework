<?php

namespace MetaFramework\Mediaclass\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use MetaFramework\Accessors\Locale;
use MetaFramework\Traits\Responses;
use ReflectionClass;
use Throwable;

trait Mediaclass
{
    use Responses;

    public ?object $instance = null;


    /**
     * Retourne tous les médias du model,
     * si elles existent
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function media(): MorphMany
    {
        return $this->morphMany(\MetaFramework\Mediaclass\Models\Mediaclass::class, 'model');
    }

    public function model(): static
    {
        $this->instance = $this;

        return $this;
    }


    /**
     * Mets à jour les infos relatives aux médias rattachés
     * au Meta model
     */
    public function processMedia(): static
    {
        if (request()->has('mediaclass')) {
            foreach (request('mediaclass') as $key => $value) {
                \MetaFramework\Mediaclass\Models\Mediaclass::where('id', $key)->update([
                    'description' => $value['description'],
                    'position' => $value['position']
                ]);
            }
        }

        if (request()->has('mediaclass_temp_id')) {
            \MetaFramework\Mediaclass\Models\Mediaclass::where('temp', request('mediaclass_temp_id'))->update([
                'model_id' => $this->model()->id,
                'temp' => null
            ]);
        }

        return $this;
    }

    public function deleteModelMedia(): static
    {

        if ($this->model()->media->isEmpty()) {
            return $this;
        }

        try {
            foreach ($this->model()->media as $media) {
                File::delete(File::glob(Storage::disk('media')->path($this->accessKey()) . DIRECTORY_SEPARATOR . '*' . $media->filename . '*'));
                $media->delete();
            }
        } catch (Throwable $e) {
            $this->responseException($e);
            report($e);
        }

        return $this;

    }

    /**
     * @throws \ReflectionException
     */
    public function accessKey(): string
    {
        return $this->model()->access_key ?: Str::snake((new ReflectionClass($this->model()))->getShortName());
    }

    public function mediaLocales(): array
    {
        return Locale::projectLocales();
    }

}
