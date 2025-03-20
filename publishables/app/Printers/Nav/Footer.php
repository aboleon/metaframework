<?php

namespace App\Printers\Nav;

use App\Models\Nav;
use MetaFramework\Polyglote\Traits\Translation;

class Footer
{
    use Builder;
    use Translation;

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
            $html = '<ul class="nav flex-column">';
            foreach ($this->tree as $item) {
                $this->entry($html, $item, $parent = 0, $bold = true);
            }
            $html .= '</ul>';
        }

        return $html;
    }

    private function entry(string &$html, $item, $parent = null, $bold = false): void
    {
        $this->makeLink($item);

        $html .= '<li class="nav-item">';
        $html .= '<a class="nav-link" href="'.$this->link.'">' . $item->translation('title') . '</a>';
        $html .= '</li>';
    }
}
