<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MetaFramework\Traits\Responses;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InstantSearch extends Meta
{
    use HasFactory;
    use Responses;

    /** Content types filter
     * @var mixed|string
     */
    protected ?string $content_type = null;

    /** Keywords as input
     */
    protected string $keywords = '';

    /** Parsed keywords
     */
    protected mixed $parsed_keywords = [];

    /** Selectable fields
     */
    protected array $selectables = [];

    protected string $searchable = '';

    public function __construct()    {

        $this->keywords = (string)request('keyword');
        $this->prepareKeywords();

    }

    /** The main search function
     * @return $this
     */
    public function search(): self
    {
        //$this->response['items'] = $this->fetchResults();
        return $this->fetchResults();
    }


    /**
     * The results collection queries
     */
    public function fetchResults()
    {
        $page_content = $this->pageContentQuery();
        $formation_content = $this->formationContentQuery();
        return $formation_content->union($page_content)->paginate();
    }

    public function paginated(): LengthAwarePaginator
    {
        return $this->pageContentQuery();
    }

    /**
     * Query on the main content types
     */
    private function pageContentQuery()
    {

        $searchable = 'url,title,abstract,content';

        $selectables = [
            'id',
            'access_key',
            'taxonomy',
            'title',
            'abstract',
            'parent',
            DB::raw('(IF(abstract IS NULL, `content`, abstract )) as excerpt'),
            'published',
            'url',
            'type',
            'updated_at'
        ];
        return self::query()
            ->select($selectables)
            ->selectRaw("MATCH ({$searchable}) AGAINST (? IN BOOLEAN MODE) AS relevance_score",
                [$this->parsed_keywords])
            ->published()
            ->whereIn('type', ['page','bloc','news'])
            ->with('hasParent')
            ->having('relevance_score', '>', 0)
            ->orderByDesc('relevance_score')
            ->orderBy('title');
    }

    /**
     * Query on the main content types
     */
    private function formationContentQuery()
    {
        $searchable_meta = 'url,title,abstract,content';
        $searchable_formation = 'target_group,
        prerequis,
        objectifs,
        modalites,
        materiel_perso,
        descriptif_detail,
        animation,
        tarif_detail,
        modalites_inscription';

        $selectables = [
            'meta.id',
            'access_key',
            'taxonomy',
            'title',
            'abstract',
            'parent',
            DB::raw('(IF(abstract IS NULL, `content`, abstract )) as excerpt'),
            'published',
            'url',
            'type',
            'updated_at'
        ];

        return self::query()
            ->select($selectables)
            ->published()
            ->join('formation_content', 'meta.id', '=', 'formation_content.meta_id')
            ->selectRaw("MATCH ({$searchable_meta}) AGAINST (? IN BOOLEAN MODE) AS relevance_score",
                [$this->parsed_keywords])
            ->with('hasParent')
            ->having('relevance_score', '>', 0)
            ->orderByDesc('relevance_score')
            ->orderBy('title')
            ->union(
                self::query()
                    ->select($selectables)
                    ->published()
                    ->join('formation_content', 'meta.id', '=', 'formation_content.meta_id')
                    ->selectRaw("MATCH ({$searchable_formation}) AGAINST (? IN BOOLEAN MODE) AS relevance_score",
                        [$this->parsed_keywords])
                    ->with('hasParent')
                    ->having('relevance_score', '>', 0)
                    ->orderByDesc('relevance_score')
                    ->orderBy('title')
            );
    }

    /**
     * Parsing of the keywords input
     */
    protected function prepareKeywords(): void
    {
        $reservedSymbols = ['-', '+', '<', '>', '@', '(', ')', '~'];
        $this->keywords = str_replace($reservedSymbols, '', $this->keywords);
        $this->parsed_keywords = array_filter(explode(' ', trim($this->keywords)), function ($item) {
            return strlen(trim($item, ' -')) > 2;
        });

        foreach ($this->parsed_keywords as $key => $word) {
            if (strlen($word) >= 3) {
                $this->parsed_keywords[$key] = '+' . $word . '*';
            }
        }
        $this->parsed_keywords = implode(' ', $this->parsed_keywords);
    }
}
