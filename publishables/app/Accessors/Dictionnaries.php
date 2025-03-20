<?php

namespace App\Accessors;

use App\Enum\DictionnaryType;
use App\Models\Dictionnary;
use App\Models\ParticipationType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Throwable;

class Dictionnaries
{


    public static function reset(string $key): void
    {
        cache()->forget('dico-'.$key);
    }

    public static function dictionnary(string $key): ?Dictionnary
    {
        return cache()->rememberForever(
            'dico-'.$key,
            function () use ($key) {
                $dictionnary = Dictionnary::query()->where('slug', $key)->with('entries')->first();
                if ($dictionnary instanceof Dictionnary && $dictionnary->type == DictionnaryType::META->value) {
                    $dictionnary->entries->load('entries');
                }

                return $dictionnary;
            },
        );
    }

    /**
     * @param  string  $key  : slug du dictionnaire
     *
     * @return array : entrées du dictionnaire (avec une hierarchichie, si elle existe
     * Dictionnaire simple : [id => value]
     * Dictionnaire meta : [id => [name => optgroup, values => [id => value]]]
     */
    public static function selectValues(string $key, array $options = []): array
    {
        $alphaSort = $options['alphaSort'] ?? false;
        try {
            if (self::dictionnary($key)->type == 'meta') {
                return self::dictionnary($key)->entries->sortBy('name')->mapWithKeys(function ($item, $key) {
                    return [
                        $item->id => [
                            'name'   => $item->name,
                            'values' => $item->entries->pluck('name', 'id')->sort()->toArray(),
                        ],
                    ];
                })->toArray();
            }
            $b = self::dictionnary($key)->entries;
            if ($alphaSort) {
                $b = $b->sortBy('name');
            }
            return $b->pluck('name', 'id')->toArray();
        } catch (Throwable $e) {
            report($e);

            return [];
        }
    }

    public static function title(string $key): string
    {
        return self::dictionnary($key)->name ?? '<span class="text-danger">Dictionnaire</span> '.$key.' <span class="text-danger">introuvable</span>';
    }

    public static function type(string $key): string
    {
        return self::dictionnary($key)->type ?? 'simple';
    }

    public static function entry(string $dictionnary, int $entry_key)
    {
        return self::dictionnary($dictionnary)->entries->filter(fn($item) => $item->id == $entry_key)->first();
    }

    public static function filterAgainstMetaType($collection, $array): Collection
    {
        return $collection->filter(function ($item) use ($array) {
            $entries         = collect($item['entries']);
            $matchingEntries = $entries->whereIn('id', $array);

            return ! $matchingEntries->isEmpty();
        });
    }

    public static function filterAgainstSimpleType($collection, $array): Collection
    {
        return $collection->filter(fn($item) => in_array($item->id, $array));
    }

    public static function orators(): array
    {
        return cache()->rememberForever('orators', fn() => ParticipationType::where('group', 'orator')->pluck('name', 'id')->sort()->toArray());
    }

    public static function participationTypes(): Collection
    {
        return cache()->rememberForever('participation_types', fn() => ParticipationType::select('id', 'name', 'group')->get()->sortBy('name')->groupBy('group'));
    }

    public static function participationTypesListable(?int $id = null, string $default = ''): array|string
    {
        $list = self::participationTypes()->flatten()->pluck('name', 'id')->toArray();

        if ( ! is_null($id)) {
            return $list[$id] ?? ($default ?: 'NA');
        }

        return $list;
    }

    public static function oratorsIds(): array
    {
        return Dictionnaries::participationTypes()['orator']->pluck('id')->toArray();
    }

    public static function medicalProfessions(): array
    {
        return collect(Dictionnaries::selectValues("professions"))->filter(fn($item) => Str::slug($item['name']) == 'medical')->first()['values'] ?? [];
    }

}
