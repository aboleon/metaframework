<?php

namespace App\Traits\DataTables;

use Yajra\DataTables\Html\Builder as HtmlBuilder;

trait Common
{
    public function setHtml(string $table, array $config = []): HtmlBuilder
    {
        $minifiedAjaxUrl = $config['minifiedAjaxUrl'] ?? '';
        $params = $config['params'] ?? [];
        $orderBys = $config['orderBys'] ?? null;
        $orderBy = $config['orderBy'] ?? 1;
        $orderByDirection = $config['orderByDirection'] ?? "desc";

        $defaultLanguage = [
            'url' => 'https://cdn.datatables.net/plug-ins/2.1.4/i18n/fr-FR.json',
        ];

        // Merge custom language settings
        $customLanguage = $config['language'] ?? [];
        $mergedLanguage = array_merge($defaultLanguage, $customLanguage);

        $res = $this->builder()
            ->setTableId($table . '-table')
            ->addTableClass('table table-hover dt')
            ->columns($this->getColumns())
            ->minifiedAjax($minifiedAjaxUrl);

        if ($orderBys) {
            foreach ($orderBys as $index => $direction) {
                $res->orderBy($index, $direction);
            }
        } else {
            $res->orderBy($orderBy, $orderByDirection);
        }

        return $res
            ->parameters(array_merge([
                'pageLength' => 25,
                'lengthMenu' => [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Tous"]],
                'language' => $mergedLanguage,
            ], $params))
            ->selectStyleSingle();
    }
}
