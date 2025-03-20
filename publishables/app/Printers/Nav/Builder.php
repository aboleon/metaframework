<?php

namespace App\Printers\Nav;

use App\Models\Nav;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

trait Builder
{
    private object $tree;
    private EloquentCollection $collection;

    private function buildTree(): object
    {
        $this->tree = $this->collection->whereNull('parent')->tap(function ($items) {
            return $items;
        });

        self::subs($this->tree, $this->collection);

        return $this;
    }

    private static function subs(&$array, $collection)
    {
        foreach ($array as $item) {
            $item->subs = $collection->where('parent', $item->id)->tap(function ($items) {
                return $items;
            });
            self::subs($item->subs, $collection);
        }
        return $array;
    }

    private function fetchRecords(string $zone): void
    {
        $this->collection = Nav::where('zone', $zone)->with('meta')->get()->sortBy('position');
    }

    private function makeLink(Nav $item): void
    {
        if ($item->meta) {
            $links = [];
            $links[] = config('nav.urls.'. $item->meta->type) ?? '';
            $links[] = $item->meta->translation('url', $this->locale());
            $this->link = url(implode('/', array_filter($links)));
        } else {
            $custom_selectable = collect($item->custom_selectables)->filter(fn($entry) => $entry['type'] == $item->type)->first();

            if ($custom_selectable) {
                $this->link = $custom_selectable['url'];
            } else {

                $this->link = $item->translation('url', $this->locale());
            }
        }
    }

}
