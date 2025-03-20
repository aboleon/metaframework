<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

trait Categories
{

    public array $array;
    public object $tree;
    public string $output_route;

    public static function subs(&$array, $collection)
    {
        foreach ($array as $item) {
            $item->subs = $collection->where('parent', $item->id)->tap(function ($items) {
                return $items;
            });
            self::subs($item->subs, $collection);
        }
        return $array;
    }

    public function hasParent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent')->orderBy('position');
    }


    public function getTree(): object
    {
        return $this->tree;
    }

    public static function pluckCollectionIds(&$array, $collection)
    {
        foreach ($collection as $item) {
            if ($item->subs->isNotEmpty()) {
                $array = array_merge($array, $item->subs->pluck('id')->toArray());
                self::pluckCollectionIds($array, $item->subs);
            }
        }
        return $array;
    }


    public static function printBreadcrumb(object $categories): string
    {
        if ($categories->isNotEmpty()) {
            foreach ($categories as $category) {
                $array = [];
                $array[] = $category;
                static::breadcrumb($array, $category);
                $array = collect(array_reverse($array));

                return $array->pluck('title')->join(' > ');
            }
        }
        return '';
    }


    public static function breadcrumb(&$array, object $category)
    {
        if ($category->hasParent) {
            $array[] = $category->hasParent;
            self::breadcrumb($array, $category->hasParent);
        }
        return $array;
    }


    public function printCheckboxTree(?Collection $assigned): string
    {
        $this->assigned = $assigned ?? collect();
        $output = '';
        foreach ($this->tree as $item) {
            $output .= '<div id="category_' . $item->id . '"><strong>' . $item->title . '</strong>';
            $output .= '<div class="form-check">';
            $output .= '<input class="form-check-input" type="checkbox" name="category[]" value="' . $item->id . '" id="category_' . $item->id . '" ' . ($this->assigned->contains($item->id) ? 'checked' : '') . '/>';
            $output .= '<label class="form-check-label" for="category_' . $item->id . '" >' . $item->title . '</label>';
            self::buildCheckboxes($output, $item, 4);
            $output .= '</div>';
        }
        return $output;
    }


    private function buildCheckboxes(string &$output, $collection, int $level): string
    {
        if ($collection->subs->isNotEmpty()) {
            foreach ($collection->subs as $items) {
                $output .= '<div class="form-check ms-2 mt-1">';
                $output .= '<input class="form-check-input" type="checkbox" name="category[]" value="' . $items->id . '" id="category' . $items->id . '"' . ($this->assigned->contains($items->id) ? 'checked' : '') . '/>';
                $output .= '<label class="form-check-label" for="category' . $items->id . '" >' . $items->title . '</label>';
                self::buildCheckboxes($output, $items, ($level + 4));
                $output .= '</div>';
            }
        }
        return $output;
    }

    public function updatePositions(): void
    {
        if (request()->filled('positions')) {
            foreach (request('positions') as $key => $position) {
                static::where('id', $key)->update(['position' => $position]);
            }
        }
    }

    public function fetchSubcats()
    {
        return $this->hasParent ? $this->hasParen->children : static::whereNull('parent')->get();
    }


    public function printTable(string $route): string
    {
        $table = '';
        $this->output_route = $route;
        foreach ($this->tree as $item) {
            $table .= $this->tableRow($item);
            self::buildTableRows($table, $item, 4);
        }
        return $table;
    }

    public function buildTableRows(string &$table, $collection, int $level): string
    {
        if ($collection->subs->isNotEmpty()) {
            foreach ($collection->subs as $items) {
                $table .= $this->tableRow($items, $level, $items->parent);
                self::buildTableRows($table, $items, ($level + 4));
            }
        }
        return $table;
    }


    private function tableRow($item, $level = 0, $parent = null): string
    {
        return '<tr' . ($parent ? ' data-parent="' . $item->parent : null) . '" data-id="' . $item->id . '">
<td>' . $item->showThumbnail('image_base') . '</td>
<td>' . str_repeat('&nbsp;', $level) . $item->title . '</td>
<td>
<div class="dropdown">
    <button class="btn btn-sm btn-danger dropdown-toggle" type="button"
            id="dropdownMenuLink_submenu_actions_{{$item->id}}" data-bs-toggle="dropdown"
            aria-expanded="false">Actions
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink_actions_' . $item->id . '">
    <li><a class="dropdown-item" href="' . route('panel.' . $this->output_route . '.edit', $item->id) . '">Éditer</a></li>
    <li><a class="dropdown-item" href="' . route('panel.' . $this->output_route . '.create', ['parent' => $item->id]) . '">Créer une sous-catégorie</a></li>
    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#myModal' . $item->id . '">Supprimer</a></li>
    </ul>
    </div>
 <div id="myModal' . $item->id . '" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="' . route('panel.' . $this->output_route . '.destroy', $item->id) . '">
                <input type="hidden" name="_method" value="delete">
                ' . csrf_field() . '
                <div class="modal-header">
                    <h5>Supression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true" aria-label="Fermer">
                    </button>
                </div>
                <div class="modal-body">
                    <p>Voulez-vous supprimer cette catégorie ?</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm" data-bs-dismiss="modal" aria-hidden="true">Annuler</button>
                    <button class="btn btn-warning btn-sm">Confirmer</button>
                </div>
            </form>
        </div>
    </div>
</div></td></tr>';
    }

    /**
     * @throws \App\Exceptions\InvalidImageOperation
     */
    public function updateCategory(): static
    {
        $this->title = request('title');
        if (request()->has('parent')) {
            $this->parent = request('parent') ?: null;
        }
        if (request()->has('position')) {
            $this->position = request('position') ?: 0;
        }
        $this->save();

        $this->updatePositions();
        $this->processMedia();

        return $this;
    }

}