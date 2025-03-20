<?php

namespace App\MailTemplates\Groups;

use App\MailTemplates\Contracts\GroupVariables;

class Manager implements GroupVariables
{

    public static function variables(): array
    {
        return [
            'Prénom' => 'Util_Prenom',
            'Nom' => 'Util_Nom',
            'e-mail' => 'Util_email',
            'Ligne indirecte' => 'Util_lignedirecte',
            'Mobile' => 'Util_mobile',
            'Adresse postale' => 'Util_adressepostale',
        ];
    }

    public static function title(): string
    {
        return 'Personnalisation Responsable';
    }

    public static function icon(): string
    {
        return 'user';
    }

}
