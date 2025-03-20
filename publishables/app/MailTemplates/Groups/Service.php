<?php

namespace App\MailTemplates\Groups;

use App\MailTemplates\Contracts\GroupVariables;

class Service implements GroupVariables
{

    public static function variables(): array
    {
        return [
            'PRESTATIONintitule',
            'PRESTATIONadresse',
            'PRESTATIONdate',
            'PRESTATIONheure',
            'PRESTATIONcommentaire'
        ];
    }

    public static function title(): string
    {
        return 'Personnalisation Prestation';
    }

    public static function icon(): string
    {
        return '<i class="fa-solid fa-mug-saucer"></i>';
    }

}
