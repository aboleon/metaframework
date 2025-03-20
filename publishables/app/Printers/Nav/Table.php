<?php

namespace App\Printers\Nav;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\View;

class Table
{

    use Builder;

    public function __invoke(): string
    {
        return $this->buildTree()->printTable();
    }

    public function __construct(EloquentCollection $collection)
    {
        $this->collection = $collection;
    }

    private function printTable(): string
    {
        $table = '';
        foreach ($this->tree as $item) {
            $table .= $this->tableRow($item, $level = 0, $parent = 0, $bold = true);
            self::buildTableRows($table, $item, 4);
        }

        return $table;
    }

    private function buildTableRows(string &$table, $collection, int $level): string
    {
        if ($collection->subs->isNotEmpty()) {
            foreach ($collection->subs as $items) {
                $table .= $this->tableRow($items, $level, $items->parent, $items->subs->isNotEmpty());
                self::buildTableRows($table, $items, ($level + 4));
            }
        }
        return $table;
    }

    private function tableRow($item, $level = 0, $parent = null, $bold = false): string
    {
        return View::make('components.nav-row', [
            'item' => $item,
            'parent' => $parent,
            'level' => $level,
            'bold' => $bold
        ])->render();
    }
}
