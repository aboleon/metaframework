<?php

namespace App\MailTemplates\Groups;

use App\MailTemplates\Contracts\GroupVariables;

class Group implements GroupVariables
{

    public static function variables(): array
    {
        return [
            'Intitulé' => 'GROUPESNom_Groupe',
            'Nom du responsable' => 'GROUPESNom_Responsable',
            'Prénom du responsable' => 'GROUPESPrenom_Responsable',
            'Solde H' => 'GROUPESSoldeH',
            'Solde I' => 'GROUPESSoldeI'
        ];
    }

    public static function title(): string
    {
        return 'Personnalisation Groupe';
    }

    public static function icon(): string
    {
        return 'table-cell-properties';
    }

}
