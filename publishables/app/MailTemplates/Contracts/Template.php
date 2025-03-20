<?php

namespace App\MailTemplates\Contracts;

interface Template
{

    /**
     * Retourne la signature du template
     * @private @string $signature
     */
    public function signature():string;

    /**
     * Déclaration des noms des variables sous forme d'array
     */
    public function variables():array;

    /**
     * Récupération des données que les variables sont censées renvoyer via $class->variable_name()
     */
    public function computed():array;
}
