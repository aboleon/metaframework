<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MetaFramework\Actions\Translator;
use App\Traits\Locale;
use MetaFramework\Traits\Responses;
use MetaFramework\Polyglote\Traits\Translation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class Nav extends Model
{
    use HasFactory;
    use Locale;
    use Responses;
    use Translation;

    protected $table = 'nav';
    protected $guarded = [];




    public array $fillables = [
        'title' => [
            'type' => 'text',
            'label' => 'Intitulé',
        ],
        'url' => [
            'type' => 'text',
            'label' => 'URL',
        ],
    ];

    protected array $selectables = [];

    public array $zones = [
        'main' => 'Menu principal',
        'footer_1' => 'Navigation / Pied de page',
        'footer_2' => 'Informations / Pied de page',
        'footer_3' => 'Liens / Pied de page',
    ];

    public array $custom_selectables;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->translatable = array_keys($this->fillables);

        $this->selectables = config('nav.selectables');
        $this->custom_selectables = config('nav.custom_selectables');

    }


    public function model(): static
    {
        return $this;
    }


    public function fetchSelectableInventory(): array
    {
        return [
            'database' => Meta::whereIn('type', array_keys($this->selectables))->select('type', 'id', 'title')->get()->groupBy('type'),
            'custom' => $this->custom_selectables
        ];
    }

    public static function pages(): Collection
    {
        return cache()->rememberForever('pages_' . app()->getLocale(), fn() => collect(Meta::where('type', 'page')->select('title', 'nav_title', 'url', 'taxonomy')->get()));
    }

    public static function page(string $taxonomy): ?Meta
    {
        return Nav::pages()->where('taxonomy', $taxonomy)->first();
    }

    public static function pageLink(string $taxonomy): ?string
    {
        $link = self::page($taxonomy);
        if ($link) {
            return '<a href="' . url(app()->getLocale(), $link->translation('url', app()->getLocale())) . '">' . ($link->getTranslation('title', app()->getLocale(), false) ?: $link->translation('title', app()->getLocale())) . '</a>';
        }

        return null;
    }

    public static function pageRawLink(string $taxonomy): ?string
    {
        $link = self::page($taxonomy);
        if ($link) {
            return url(app()->getLocale(), $link->translation('url', app()->getLocale()));
        }
        return null;
    }

    /**
     * @throws \Exception
     */
    public function process(): object
    {
        $linkable = is_numeric(request('nav_entry'));
        $is_custom = request('nav-type') == 'custom';

        $this->parent = request()->filled('parent') ? request('parent') : null;
        $this->type = $is_custom
            ? 'custom'
            : ($linkable
                ? Meta::find(request('nav_entry'))->type
                : request('nav_entry')
            );
        $this->meta_id = $is_custom
            ? null
            : ($linkable
                ? request('nav_entry')
                : null
            );
        $this->zone = request('zone') ?: 'main';

        $this->save();

        (new Translator($this))
            ->update();

        $this->clearCache();

        $this->responseSuccess(__('ui.record_created'));
        $this->redirectRoute('panel.nav.index');

        return $this;
    }

    public function meta(): BelongsTo
    {
        return $this->belongsTo(Meta::class, 'meta_id')->select('id', 'type', 'url', 'taxonomy','title');
    }

    public function clearCache()
    {
        foreach (config('translatable.locales') as $locale) {
            foreach (array_keys($this->zones) as $key) {
                cache()->forget($key . '_nav_' . $locale);
            }
        }
    }


}
