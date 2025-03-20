<?php

namespace App\Printers\Meta;

use App\Traits\TreeBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class Select
{
    use TreeBuilder;

    private ?int $affected;
    private string $name;

    public function __construct(EloquentCollection $collection, string $name, ?int $affected = null)
    {
        $this->collection = $collection;
        $this->affected = $affected;
        $this->name = $name;
    }

    public function __invoke()
    {
        return $this->buildTree()->print();
    }



    public function print(): string
    {
        $html = '';
        if ($this->tree->isNotEmpty()) {
            $html = '<select class="form-control" name="'.$this->name.'" id="'.rtrim(str_replace(['[',']'],'_', $this->name),'_').'"><option value="">-- Sélectionner --</option>';
            foreach ($this->tree as $item) {
                $this->entry($html, $item);
            }
            $html .= '</select>';
        }

        return $html;
    }

    private function entry(string &$html, $item, $parent = null, $level = 0): void
    {
        $html .= '<option value="' . $item->id . '"' . ($this->affected == $item->id ? ' selected' : '') . '>' . str_repeat('- ', $level) . $item->translation('title');
        self::buildLevels($html, $item, ++$level);
        $html .= '</option>';
    }

    private function buildLevels(string &$html, $collection, int $level): string
    {
        if ($collection->subs->isNotEmpty()) {
            foreach ($collection->subs as $items) {
                $this->entry($html, $items, $items->parent, $level);
            }
        }
        return $html;
    }
}
