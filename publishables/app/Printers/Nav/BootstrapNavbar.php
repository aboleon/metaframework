<?php

namespace App\Printers\Nav;

use App\Models\Nav;
use App\Traits\Locale;
use MetaFramework\Polyglote\Traits\Translation;

class BootstrapNavbar
{
    use Builder;
    use Locale;
    use Translation;

    private array $link_classes;
    private array $li_classes;
    private string $toggler;
    private string $link;

    public function __invoke(): string
    {
        return $this->buildTree()->print();
    }


    public function __construct(string $zone, array $params = [])
    {
        $this->fetchRecords($zone);
    }

    private function print(): string
    {
        $html = '';
        if ($this->tree->isNotEmpty()) {
            $html = '<ul class="navbar-nav">';
            foreach ($this->tree as $item) {
                $this->entry($html, $item, $parent = null);
            }
            $html .= '</ul>';
        }

        return $html;
    }

    private function buildLevels(string &$html, $collection): string
    {
        if ($collection->subs->isNotEmpty()) {
            $html .= '<ul class="dropdown-menu">';
            foreach ($collection->subs as $items) {
                $this->entry($html, $items, $items->parent, $items->subs->isNotEmpty());
            }
            $html .= '</ul>';
        }
        return $html;
    }

    private function entryClasses(object $item, $parent = null): void
    {
        $subs = $item->subs->isNotEmpty();

        $this->li_classes = [];
        $this->link_classes = [];
        $this->toggler = '';

        if (!$parent) {
            $this->li_classes[] = 'nav-item';
            $this->link_classes[] = 'nav-link';
        }
        if ($subs) {
            $this->li_classes[] = 'dropdown';
            $this->link_classes[] = 'nav-link dropdown-toggle';
            $this->toggler = ' role="button" data-bs-toggle="dropdown" aria-expanded="false"';
        }

        if ($parent) {
            $this->link_classes[] = 'dropdown-item';

        }
    }

    private function entry(string &$html, $item, $parent = null): void
    {
        $this->entryClasses($item, $parent);
        $this->makeLink($item);


        $html .= '<li' . ($this->li_classes ? ' class="' . collect($this->li_classes)->join(' ') . '"' : '') . '>';
        $html .= '<a id="link-'.($item->meta?->taxonomy ?: $item->id).'" ' . $this->toggler . ' href="'.$this->link.'"' . ($this->link_classes ? ' class="' . collect($this->link_classes)->join(' ') . '"' : '') . '>' . $item->translation('title') . '</a>';
        self::buildLevels($html, $item);
        $html .= '</li>';
    }
}
