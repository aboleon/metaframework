<?php

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use InvalidArgumentException;
use MetaFramework\Inputable\Contracts\GooglePlacesInterface;

class GooglePlaces extends Component
{
    /**
     * $params[] sert … ajouter des params suppl. … l'url JS de Google,
     * exemple :
     *      [types => (cities)]
     */
    public GooglePlacesInterface $model;
    public GooglePlacesInterface $geo;
    public Collection $required;
    public ?string $defaultTextAdress = null;
    private array $readonly = [
        'street_number',
        'route',
        'locality',
        'postal_code',
    ];

    /**
     * @param  GooglePlacesInterface|null  $model
     * @param  GooglePlacesInterface|null  $geo
     * Hide some fields:
     * administrative_area_level_1
     * administrative_area_level_1_short
     * administrative_area_level_2
     * locality
     * postal_code
     * route
     * street_number
     */
    public function __construct(
        ?GooglePlacesInterface $model = null,
        ?GooglePlacesInterface $geo = null,
        public string $field = 'mfw_geo',
        public string $random_id = '',
        public array $params = [],
        public string $placeholder = '',
        public string $tag_required = 'required',
        public ?string $label = null,
        public ?string $notice = null,
        public array $hidden = [
            'administrative_area_level_1_short',
            'administrative_area_level_1',
            'administrative_area_level_2',
            'country_code',
        ],
    ) {
        $resolvedModel = $model ?? $geo;
        if (! $resolvedModel) {
            throw new InvalidArgumentException('GooglePlaces requires a model (or geo) instance.');
        }

        $this->model             = $resolvedModel;
        $this->geo               = $resolvedModel;
        $this->random_id         = Str::random(4);
        $this->required          = collect($this->params['required'] ?? []);
        $this->defaultTextAdress = $this->model->text_address ?? ($this->model->locality ?? null);
    }

    public function render(): Renderable
    {
        return view('mfw::components.google-places');
    }

    public function tagRequired(string $key): string
    {
        return $this->required->contains($key) ? $this->tag_required : '';
    }

    public function labelRequired(string $key): string
    {
        return $this->required->contains($key) ? ' *' : '';
    }

    public function inputable(string $key): string
    {
        return 'col-' . $key . ' ' . (in_array($key, $this->hidden) ? ' d-none' : '');
    }

    public function readonlies(string $key): string
    {
        return ! $this->defaultTextAdress && in_array($key, $this->readonly) ? ' lockable' : '';
    }
}
