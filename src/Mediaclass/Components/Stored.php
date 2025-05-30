<?php

namespace MetaFramework\Mediaclass\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\Component;
use MetaFramework\Mediaclass\Interfaces\MediaclassInterface;
use MetaFramework\Mediaclass\Models\Media;

class Stored extends Component
{
    protected array $positionning = [
        'left','up','down','right'
    ];

    public Collection $medias;

    public function __construct(
        public MediaclassInterface $model,
        public string $group,
        public ?string $subgroup = null,
        public bool       $positions = false,
        public int|bool   $description = true,
        public array|string|null $cropable = null, // Changed to accept array
        public string $nomedia = '',
    )
    {
        $this->description = $this->description ? 1 : 0;
        $this->medias = $this->model->media->where('group', $this->group);

        if ($this->subgroup) {
            $this->medias = $this->medias->where('subgroup', $this->subgroup);
        }

        $this->nomedia = $this->nomedia ?: __('mediaclass.no_media');

        // Check if model has mediaclassSettings for this group
        if ($this->cropable === null && method_exists($this->model, 'mediaclassSettings')) {
            $modelSettings = $this->model->mediaclassSettings();

            if (isset($modelSettings[$this->group])) {
                $groupSettings = $modelSettings[$this->group];

                // Set cropable if defined in group settings
                if (isset($groupSettings['cropable']) && $groupSettings['cropable'] === true) {
                    $this->cropable = [
                        $this->group => [
                            $groupSettings['width'],
                            $groupSettings['height']
                        ]
                    ];
                }
            }
        }

        // Convert array to JSON for data attribute
        if (is_array($this->cropable)) {
            $this->cropable = json_encode($this->cropable);
        }
    }

    public function isFile(Media $media): bool
    {
        return !str_contains($media->mime, 'image');
    }

    public function isImage(Media $media): bool
    {
        return str_contains($media->mime, 'image');
    }

    public function render(): Renderable
    {
        return view('mediaclass::components.stored');
    }

    public function getPositionning(): array
    {
        return $this->positionning;
    }
}