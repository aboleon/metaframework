<?php

namespace MetaFramework\Mediaclass;

use Illuminate\Support\Facades\Route;
use MetaFramework\Mediaclass\Models\Media;
use MetaFramework\Mediaclass\Traits\Accessors;

class Cropable
{
    use Accessors;

    private array $settings = [];
    private array $cropable_settings = [];
    private ?string $current_crop_key = null;
    private int $cropable_width = 0;
    private int $cropable_height = 0;
    public array $croppedImages = [];

    public function __construct(public Media $media)
    {
        $this->settings();
        $this->checkIfCropped();
    }

    public static function form(Media $media)
    {
        $cropKey  = request('crop_key', 'default');
        $cropable = new Cropable($media);
        $cropable->setCurrentCropKey($cropKey);

        return view('mediaclass::cropper')->with([
            'media'    => $media,
            'cropable' => $cropable,
            'crop_key' => $cropKey,
        ]);
    }

    public function links(): string
    {
        if (empty($this->cropable_settings)) {
            return '';
        }

        $html = '<div class="crop-actions-bar">';

        foreach ($this->cropable_settings as $key => $dimensions) {
            $width  = (int)$dimensions[0];
            $height = (int)$dimensions[1];

            if ($width > 0 && $height > 0) {
                $isCropped  = $this->isCroppedForKey($key);
                $cropClass  = $isCropped ? 'crop cropped' : 'crop';
                $iconClass  = $isCropped ? 'fa-solid fa-crop-simple' : 'fa-solid fa-crop';
                $previewUrl = $isCropped ? $this->media->url('xl', $key) : '';

                $html .= '<a class="'.$cropClass.'"
           data-crop-key="'.$key.'"
           data-crop-w="'.$width.'"
           data-crop-h="'.$height.'"
           data-media-id="'.$this->media->id.'"               
           data-preview-url="'.$previewUrl.'"                
           data-bs-toggle="modal"
           data-bs-target="#mediaclass-crop"
           href="'.route('mediaclass.cropable', $this->media)
                    .'?w='.$width.'&h='.$height.'&crop_key='.$key.'"
           title="'.ucfirst($key).' ('.$width.'x'.$height.')">
           <i class="'.$iconClass.'"></i>
           <span class="crop-label">'.ucfirst($key).'</span>
           '.($isCropped ? '<i class="fa-solid fa-circle-check check-icon"></i>' : '').'
         </a>';
            }
        }

        $html .= '</div>';

        return $html;
    }

    public function link(): string
    {
        // Backwards compatibility - returns first crop link
        if (empty($this->cropable_settings)) {
            return '';
        }

        $firstKey   = array_key_first($this->cropable_settings);
        $dimensions = $this->cropable_settings[$firstKey];
        $width      = (int)$dimensions[0];
        $height     = (int)$dimensions[1];

        if ($width <= 0 || $height <= 0) {
            return '';
        }

        $isCropped = $this->isCroppedForKey($firstKey);

        return '<a class="crop"
                   data-crop-key="'.$firstKey.'"
                   data-crop-w="'.$width.'"
                   data-crop-h="'.$height.'"
                   data-bs-toggle="modal"
                   data-bs-target="#mediaclass-crop"
                   href="'.route('mediaclass.cropable', $this->media).'?w='.$width.'&h='.$height.'&crop_key='.$firstKey.'">
                    <i class="fa-solid fa-crop"></i>
                </a>';
    }

    public function settings(): self
    {
        $this->settings = $this->media->settings();

        if (array_key_exists('cropable', $this->settings)) {
            $cropable = $this->settings['cropable'];

            // Check if it's the new format (associative array)
            if (is_array($cropable) && ! isset($cropable[0])) {
                $this->cropable_settings = $cropable;
            } else {
                // Old format - convert to new format
                $this->cropable_settings = ['default' => $cropable];
            }
        }

        // Handle route parameters
        if (Route::currentRouteName() == 'mediaclass.cropable') {
            $cropKey                = request('crop_key', 'default');
            $this->current_crop_key = $cropKey;

            if (isset($this->cropable_settings[$cropKey])) {
                $this->cropable_width  = (int)$this->cropable_settings[$cropKey][0];
                $this->cropable_height = (int)$this->cropable_settings[$cropKey][1];
            } else {
                $this->cropable_width  = (int)request('w');
                $this->cropable_height = (int)request('h');
            }
        }

        return $this;
    }

    public function setCurrentCropKey(string $key): self
    {
        $this->current_crop_key = $key;

        if (isset($this->cropable_settings[$key])) {
            $this->cropable_width  = (int)$this->cropable_settings[$key][0];
            $this->cropable_height = (int)$this->cropable_settings[$key][1];
        }

        return $this;
    }

    public function getCurrentCropKey(): ?string
    {
        return $this->current_crop_key;
    }

    public function setWidth(int $width): self
    {
        $this->cropable_width = $width;

        return $this;
    }

    public function setHeight(int $height): self
    {
        $this->cropable_height = $height;

        return $this;
    }

    public function setCropableFromComponent(?string $cropable): self
    {
        if ( ! $cropable) {
            return $this;
        }

        // Try to decode as JSON first (new format)
        $decoded = json_decode($cropable, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $this->cropable_settings = $decoded;
        } else {
            // Check if it's the [object Object] case
            if (trim($cropable) === '[object Object]') {
                throw new \InvalidArgumentException('Mediaclass: Invalid cropable data - object was not properly serialized');
            }
            // Old format - single crop dimensions
            $settings                = explode(',', $cropable);
            $this->cropable_settings = [
                'default' => [
                    (int)current($settings),
                    (int)end($settings),
                ],
            ];
        }

        // Set default dimensions from first crop
        if ( ! empty($this->cropable_settings)) {
            $firstKey              = array_key_first($this->cropable_settings);
            $this->cropable_width  = (int)$this->cropable_settings[$firstKey][0];
            $this->cropable_height = (int)$this->cropable_settings[$firstKey][1];
        }

        return $this;
    }

    public function width(): int
    {
        return $this->cropable_width;
    }

    public function height(): int
    {
        return $this->cropable_height;
    }

    public function checkIfCropped(): self
    {
        $this->croppedImages = $this->media->getCroppedImages();

        return $this;
    }

    public function isCroppedForKey(string $key): bool
    {
        return $this->media->isCroppedForKey($key);
    }

    public function isCropable(): bool
    {
        return ! empty($this->cropable_settings);
    }

    public function printSizes(): string
    {
        if (empty($this->cropable_settings)) {
            return '';
        }

        $html = '<div class="crop-sizes">';

        foreach ($this->cropable_settings as $key => $dimensions) {
            $width     = (int)$dimensions[0];
            $height    = (int)$dimensions[1];
            $isCropped = $this->isCroppedForKey($key);

            if ($width > 0 && $height > 0) {
                $html .= '<span class="crop-size-item">';
                $html .= ucfirst($key).': '.$width.' x '.$height;
                if ($isCropped) {
                    $html .= ' <i class="fa-solid fa-circle-check"></i>';
                }
                $html .= '</span>';
            }
        }

        $html .= '</div>';

        return $html;
    }

    public function printCheckMark(): string
    {
        $hasAnyCrop = false;

        foreach ($this->cropable_settings as $key => $dimensions) {
            if ($this->isCroppedForKey($key)) {
                $hasAnyCrop = true;
                break;
            }
        }

        if ($hasAnyCrop) {
            return '<i class="fa-solid fa-circle-check"></i>';
        }

        return '';
    }

    public function getCropableSettings(): array
    {
        return $this->cropable_settings;
    }

    /**
     * Check if this is the old (backwards compatible) cropable implementation
     */
    public function isCropped(): bool
    {
        // For backwards compatibility, check if any crop exists
        return ! empty($this->croppedImages);
    }
}