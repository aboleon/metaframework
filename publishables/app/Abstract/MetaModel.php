<?php

namespace App\Abstract;

use MetaFramework\Mediaclass\Interfaces\MediaclassInterface;
use MetaFramework\Mediaclass\Models\Mediaclass;
use App\Models\Meta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Throwable;

/**
 * Le MetaModel hérite des fonctionnalités du Root Meta model et
 * fourni des fonctionalités (bridge) vers le Meta model dérivé
 */
abstract class MetaModel extends Meta implements MediaclassInterface
{

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Récupère la signature du Meta model (TB meta)
     */
    public static function getSignature(): string
    {
        return static::$signature;
    }

    /**
     * Traitement DB par défaut basé sur la seule configuration du Meta model
     */
    public function processModel(): static
    {
        if ($this->reliesOnMeta()) {
            return $this;
        }

        $data = [];
        foreach ($this->translatable as $key) {
            $data[$key] = request($this::$signature . '.' . $key);
        }
        $this->modelContent->updateOrCreate(['meta_id' => $this->id], $data);

        return $this;
    }

    /**
     * Retourne le Meta Content model (s'il existe)
     * Le Meta Content model représente toute la structure info custom
     * portée dans une TB DB dédiée
     */
    public function modelContent(): ?hasOne
    {
        try {
            $class = '\App\Models\\' . Str::studly($this->type) . 'Content';
            return $this->hasOne($class, 'meta_id')->withDefault();
        } catch (Throwable $e) {
            return null;
        }
    }

    public function hasModelContent(): bool
    {
        return $this->modelContent() instanceof hasOne;
    }

    /**
     * Eloquent scope sur la signature du Meta model
     */
    public function scopeSignature($query): Builder
    {
        return $query->whereType(self::getSignature());
    }

    public function unsetMetaAsTransltable(): static
    {
        $this->translatable = array_filter($this->translatable, fn($item) => !str_starts_with($item, 'meta['));
        return $this;
    }

    public function getMetaAsTransltable(): array
    {
        return array_map(fn($item) => str_replace(['meta[',']'],'', $item), array_filter($this->translatable, fn($item) => str_starts_with($item, 'meta[')));
    }
}
