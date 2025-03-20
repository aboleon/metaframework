<?php

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use MetaFramework\Interfaces\GooglePlacesInterface;

class GooglePlaces extends Component
{
    /**
     * $params[] sert à ajouter des params suppl. à l'url JS de Google,
     * exemple :
     *      [types => (cities)]
     */

    public Collection $required;
    public ?string $defaultTextAdress = null;
    private $readonly
        = [
            'street_number',
            'route',
            'locality',
            'postal_code',
        ];

    /**
     * @param  GooglePlacesInterface  $geo
     * @param  string                 $field
     * @param  string                 $random_id
     * @param  array                  $params
     * @param  string                 $placeholder
     * @param  string                 $tag_required
     * @param  string|null            $label
     * @param  array                  $hidden
     * Hide some fields
     * --
     * administrative_area_level_1
     * administrative_area_level_1_short
     * administrative_area_level_2
     * locality
     * postal_code
     * route
     * street_number
     */

    public function __construct(
        public GooglePlacesInterface $geo,
        public string $field = 'wa_geo',
        public string $random_id = '',
        public array $params = [],
        public string $placeholder = '',
        public string $tag_required = 'required',
        public ?string $label = null,
        public ?string $notice = null,
        public array $hidden
        = [
            'administrative_area_level_1_short',
            'administrative_area_level_1',
            'administrative_area_level_2',
            'country_code',
        ],
    ) {
        $this->random_id         = Str::random(4);
        $this->required          = collect($this->params['required'] ?? []);
        $this->defaultTextAdress = $this->geo->text_address ?? ($this->geo->locality ?? null);
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
        return 'col-'.$key.' '.(in_array($key, $this->hidden) ? ' d-none' : '');
    }

    public function readonlies(string $key): string
    {
        return ! $this->defaultTextAdress && in_array($key, $this->readonly) ? ' lockable' : '';
    }
}
