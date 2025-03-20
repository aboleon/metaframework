<?php

namespace App\Abstract;

/**
 * Le MetaModel hérite des fonctionnalités du Root Meta model et
 * fourni des fonctionalités (bridge) vers le Meta model dérivé
 */
abstract class BlocModel extends MetaModel
{

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function getLabel(): string
    {
        return static::$label;
    }

    public static function getTaxonomy(): string
    {
        return static::$taxonomy;
    }
}
