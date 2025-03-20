<?php

declare(strict_types=1);

namespace App\Traits;


use Illuminate\Support\Collection;

trait SelectableValues
{
    public static function convertCollectionToValues(Collection $collection, string $name = 'name'): array
    {
        $keyed = $collection->mapWithKeys(fn($item) => [$item['id'] => $item[$name]]);

        return $keyed->all();
    }

    public static function convertCollectionWithRelationToValues(?object $collection, string $relationName, string $label = 'name', string $relationLabel = 'name'): array
    {
        $keyed = $collection->mapWithKeys(function($item) use ($label, $relationName, $relationLabel){
            $values = [
                'name' => $item[$label],
                'relations' => self::convertCollectionToValues($item->{$relationName}, $relationLabel)
            ];
            return [$item['id'] => $values];
        });
        return $keyed->all();
    }
}
