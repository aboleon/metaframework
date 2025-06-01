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
        public bool $ghost = false
    )
    {
        $this->description = $this->description ? 1 : 0;

        if ($this->ghost) {
            // For ghost models, don't query the relationship
            // Instead, query Media directly using model type and group
            $modelType = get_class($this->model);
            $morphMap = \Illuminate\Database\Eloquent\Relations\Relation::morphMap();
            $morphType = array_search($modelType, $morphMap) ?: $modelType;

            $query = Media::where('model_type', $morphType)
                ->where('group', $this->group)
                ->whereNull('model_id'); // Ghost records have no model_id

            if ($this->subgroup) {
                $query->where('subgroup', $this->subgroup);
            }

            $this->medias = $query->get();

            // Inject the ghost model into each media
            $this->medias->each(function($media) {
                $media->setRelation('model', $this->model);
            });
        } else {
            $this->medias = $this->model->media->where('group', $this->group);

            if ($this->subgroup) {
                $this->medias = $this->medias->where('subgroup', $this->subgroup);
            }
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