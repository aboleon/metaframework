<?php

namespace App\MailTemplates\Groups;

use App\MailTemplates\Contracts\GroupVariables;

class Event implements GroupVariables
{
    public static function signature(): string
    {
        return 'wg_event';
    }

    public static function variables(): array
    {
        return [
            'Type d\'évènement' => 'EVENEMENTSType',
            'Adresse' => 'EVENEMENTSAdresse_Lieu_1',
            'Ville' => 'EVENEMENTSville',
            'Pays' => 'EVENEMENTSpays',
            'Accès' => 'EVENEMENTSacces',
            'Date de début' => 'EVENEMENTSDate_Debut',
            'Date de fin' => 'EVENEMENTSDate_Fin',
            'Intitulé' => 'EVENEMENTSIntitule_1',
            'Lieu' => 'EVENEMENTSLieu_1',
            'Titre' => 'EVENEMENTSTitre',
            'Texte de facturation' => 'EVENEMENTSTxt_Fact',
            'Site du congrès' => 'EVENEMENTSsiteducongres',
            'Client' => 'EVENEMENTSClient',
            'Inscription prénom' => 'EVENEMENTSprenomrespinscription',
            'Inscription nom' => 'EVENEMENTSnomrespinscription',
            'Inscription e-mail' => 'EVENEMENTSemailrespinscription',
            'Inscription numéro de tél.' => 'EVENEMENTStelrespinscription',
            'Url' =>'EVENEMENTSurl',
            'Logo' => 'EVENEMENTSlogo'
        ];
    }

    public static function title(): string
    {
        return 'Personnalisation Évènement';
    }

    public static function icon(): string
    {
        return 'bookmark';
    }

}
