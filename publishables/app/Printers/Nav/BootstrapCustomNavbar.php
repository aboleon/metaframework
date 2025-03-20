<?php

namespace App\Printers\Nav;

use App\Models\Nav;
use App\Traits\Locale;
use MetaFramework\Polyglote\Traits\Translation;

class BootstrapCustomNavbar
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
            $html = '<ul class="navbar-nav nav">';
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
            $html .= '<nav class="navbar subnavbar"><ul class="navbar subnav naav">';
            foreach ($collection->subs as $items) {
                $this->entry($html, $items, $items->parent, $items->subs->isNotEmpty());
            }
            $html .= '</ul></nav>';
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
            $this->toggler = '';
        }

        if ($parent) {
            $this->li_classes[] = 'subnav-item" data-title="' . $item->title;
            $this->link_classes[] = '';

        }
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

    private function entry(string &$html, $item, $parent = null): void
    {
        $this->entryClasses($item, $parent);
        $this->makeLink($item);


        $html .= '<li' . ($this->li_classes ? ' class="' . collect($this->li_classes)->join(' ') . '"' : '') . '>';
        $html .= '<a ' . $this->toggler . ' href="' . $this->link . '"' . ($this->link_classes ? ' class="' . collect($this->link_classes)->join(' ') . '"' : '') . '>' . $item->translation('title') . '</a>';
        self::buildLevels($html, $item);
        $html .= '</li>';
    }
}
