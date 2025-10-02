<?php

namespace MetaFramework\Models;

use Illuminate\Database\Eloquent\{
    Model,
    Relations\BelongsTo,
    Relations\HasMany,
    Relations\HasOne,
    SoftDeletes};
use Illuminate\Support\Str;
use MetaFramework\Abstract\MetaModel;
use MetaFramework\Accessors\Locale;
use MetaFramework\Mediaclass\Interfaces\MediaclassInterface;
use MetaFramework\Mediaclass\Traits\Mediaclass;
use MetaFramework\Polyglote\Traits\Translation;
use MetaFramework\Support\Traits\Responses;
use MetaFramework\Traits\{
    AccessKey,
    MetaParams,
    OnlineStatus,
    TreeBuilder};

/**
 * Model méthods & properties
 *
 * @property null|int $published
 * @property HasOne $hasParent
 * @property string $taxonomy
 * @property null|int $parent
 * @property string $type
 * @property mixed $content
 * @property int $id
 */

class Meta extends Model implements MediaclassInterface
{
    use AccessKey;
    use Translation;
    use Mediaclass;
    use MetaParams;
    use Responses;
    use OnlineStatus;
    use SoftDeletes;
    use TreeBuilder;

    protected $table = 'meta';
    protected $guarded = [];
    protected $casts = [
        'configs' => 'array',
        'published' => 'datetime'
    ];

    public ?MetaModel $submodel = null;
    public static string $signature = 'meta';


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->fillables = [
            'title' => [
                'type' => 'title',
                'label' => 'Titre'
            ],
            'abstract' => [
                'type' => 'textarea',
                'class' => 'h-200',
                'label' => 'Résumé'
            ],
            'title_meta' => [
                'type' => 'input',
                'label' => 'Meta titre'
            ],
            'abstract_meta' => [
                'type' => 'textarea',
                'label' => 'Meta description'
            ],
            'url' => [
                'type' => 'input',
                'label' => 'URL'
            ],
        ];

        $this->defineTranslatables();
    }

    public function subModel(): MetaModel
    {
        if ($this->submodel !== null) {
            return $this->submodel;
        }

        $this->submodel = (new MetaSubModel($this))->model();

        return $this->submodel;
    }

    /**
     * Retourne un formulaire attaché via le Form model,
     * s'il existe
     */
    public function form(): HasOne
    {
        return $this->hasOne(Forms::class, 'meta_id');
    }

    /**
     * Retourne l'url publique du Model
     * @throws \Exception
     */
    public function link(string $locale): string
    {
        return $this->url;
        $url = [];

        if (Locale::multilang()) {
            $url = [$locale];
        }
        $url[] = $this->subModel()->urls['prefix'];
        $url[] = $this->url;

        return $this->subModel()->urls['show'].'/'.$this->url ?: implode('/', array_filter($url));
    }

    public static function makeMeta(string $type): Meta
    {
        $meta = new Meta;
        $meta->type = $type;
        $meta->author_id = auth()->id();
        $meta->position = Meta::max('position') + 1;
        $meta->access_key = $meta::generateAccessKey();
        $meta->save();

        return $meta;
    }

    /**
     * Traitement DB du Root Meta model
     */
    public function process(): static
    {
        if (request()->has('meta.content')) {
            $this->translatable[] = 'content';
        }

        foreach ($this->translatable as $value) {
            foreach (config('translatable.locales') as $locale) {
                $data = (
                $value == 'url'
                    ? Str::slug($this->translatableFromRequest('meta.url', $locale) ?: $this->translatableFromRequest('meta.title', $locale))
                    : $this->translatableFromRequest('meta.' . $value, $locale)
                );
               $this->saveTranslation($value, $locale, $data);
            }
        }
        if ($this->subModel()->isStoringMetaContentAsJson()) {
            $this->content = request($this->subModel()->getSignature());
        }


        $parent = request('meta.parent');
        $parent = ($parent && $parent == $this->id ? null : $parent);
        $this->parent = $parent;

        $this->level = $this->hasParent ? $this->hasParent->level + 1 : 1;

        $this->taxonomy = request('meta.taxonomy');
        $this->configs = request('meta.configs');
        $this->template = request('meta.template');

        $this->save();

        $this->cacheables();

        return $this;
    }
    /**
     * Retourne l'image d'illustration de base du Meta model,
     * si elle existe
     */
    public function illustration(): ?\MetaFramework\Mediaclass\Models\Media
    {
        return $this->media->where('group', 'meta')->first();
    }

    /**
     * Retourne l'autheur du model
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Retourne le parent du model
     */
    public function hasParent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent');
    }

    /**
     * Retourne les parents du model en mode recursive
     */
    public function parents(): belongsTo
    {
        return $this->belongsTo(self::class, 'parent')->with('parents');
    }

    /**
     * Retourne les enfants du model en mode recursive
     */
    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'parent')->with('children');
    }


    public function processAttachedForms()
    {
        if (request()->filled('meta.forms')) {
            if (is_null($this->form)) {
                $this->form()->save(new Forms([
                    'name' => request('meta.forms')
                ]));
            } else {
                if ($this->form->name != request('meta.forms')) {
                    $this->form()->update([
                        'name' => request('meta.forms')
                    ]);
                }
            }
        } else {
            if (!is_null($this->form)) {
                $this->form->delete();
            }
        }
    }

    /**
     * Réinitialise un objet déclaré dans config('app.cacheables')
     */
    private function cacheables(): void
    {
        if (in_array($this->type, config('app.cacheables'))) {
            cache()->forget($this->type);
        }
    }



}
