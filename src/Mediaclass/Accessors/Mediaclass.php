<?php

namespace MetaFramework\Mediaclass\Accessors;


use Illuminate\Support\Str;
use MetaFramework\Mediaclass\Interfaces\MediaclassInterface;
use MetaFramework\Mediaclass\Models\Mediaclass as MediaclassModel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use ReflectionClass;

class Mediaclass
{
    public string $group = 'media';
    public MediaclassInterface $object;
    protected array $params = [];
    protected array $image = [];
    protected MediaclassModel|EloquentCollection|null $image_instance;
    protected string $size = 'sm';
    protected string $default_img;
    protected bool $with_default = true;
    protected bool $single = false;

    public function __construct()
    {
        $this->image = [];
        $this->image_instance = null;
        $this->default_img = self::defaultImgUrl();
    }

    public static function defaultImgUrl(): string
    {
        return asset('media/imgholder.png');
    }

    public function group(string $group): static
    {
        $this->group = $group;
        return $this;
    }

    public function size(string $size = 'sm'): static
    {
        $this->size = $size;
        return $this;
    }

    public function on(MediaclassInterface $object): static
    {
        $this->object = $object;
        return $this;
    }

    public function param(string $key, string $value): static
    {
        $this->params[$key] = $value;
        return $this;
    }

    public function first(): static
    {
        $this->fetch();
        $this->image_instance = $this->image_instance->first();
        return $this;
    }

    public function fetch(): static
    {
        $this->image_instance = $this->object->media->where('group', $this->group);
        return $this;
    }

    public function serve(): array
    {
        if (!$this->image_instance) {
            return $this->image;
        }

        if (!is_countable($this->image_instance)) {
            $this->parseImage($this->image_instance);
        } else {
            foreach ($this->image_instance as $item) {
                $this->parseImage($item);
            }
        }
        return $this->image;
    }

    public function get()
    {
        $this->fetch()->serve();
        return $this;
    }

    public function url(string $prefix = 'cropped', ?string $default_img = null): string
    {
        $this->fetch()->serve();

        $media = current($this->image);

        if (empty($media)) {
            return $default_img ?? ($this->with_default ? $this->defaultImgUrl() : '');
        }

        return current(array_filter($media['urls'], fn($item) => $item == $prefix, ARRAY_FILTER_USE_KEY)) ?: end($media['urls']);
    }

    public static function printUrl(array $media, string $prefix = 'cropped'): string
    {
        return current(array_filter($media['urls'], fn($item) => $item == $prefix, ARRAY_FILTER_USE_KEY)) ?: end($media['urls']);
    }

    public function single(): static
    {
        $this->single = true;
        return $this;
    }

    public function render(): string
    {
        if (!$this->image) {
            if ($this->with_default) {
                $this->image[] = [
                    'url' => $this->default_img
                ];
            }
        }

        if ($this->single) {
            $url = $this->image['urls'][$this->size] ?? ($this->image['url'] ?? false);
            if ($url) {
                $html = '<img src="' . $url . '" ';
                foreach ($this->params as $key => $value) {
                    $html .= $key . '="' . $value . '" ';
                }
                $html .= '/>';

                return $html;
            }
            return '';
        }

        $html = '';
        foreach ($this->image as $image) {

            $html .= '<img src="' . $image['url'] . '" ';
            foreach ($this->params as $key => $value) {
                $html .= $key . '="' . $value . '" ';
            }
            $html .= '/>';
        }


        return $html;
    }

    public function noDefault(): static
    {
        $this->with_default = false;
        return $this;
    }

    private function parseImage(MediaclassModel $instance)
    {
        //try {
        $sizes = array_merge(array_keys(config('mediaclass.dimensions')), ['cropped']);
        $urls = [];
        foreach ($sizes as $size) {
            $file = Path::mediaFolderName($this->object) . '/' . $instance->dimensionPrefix($size) . $instance->filename . '.'. $instance->extension();

            if (Storage::disk('media')->exists($file)) {
                $urls[$size] = Storage::disk('media')->url($file);
            }
        }

        $this->image[] = [
            'position' => $instance->position,
            'description' => $instance->description[app()->getLocale()],
            'urls' => $urls,
            'url' => $urls ? end($urls) : ''
        ];
        /*   } catch (Throwable $e) {
               report($e);
           }*/

        return $this;
    }

}
