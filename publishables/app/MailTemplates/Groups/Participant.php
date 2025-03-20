<?php

namespace App\MailTemplates\Groups;

use App\MailTemplates\Contracts\GroupVariables;

class Participant implements GroupVariables
{

    public static function variables(): array
    {
        return [
            'Date de naissance' => 'PARTICIPANTSdatedenaissance',
            'Date de validité du passeport' => 'PARTICIPANTSdatedevaliditepasseport',
            'Lieu de naissance' => 'PARTICIPANTSlieudenaissance',
            'N° de passeport' => 'PARTICIPANTSndepasseport',
            'Adresse principale' => 'PARTICIPANTSAdresse_1',
            //'' => 'PARTICIPANTSAdresse_2',
            //'' => 'PARTICIPANTSAdresse_3',
            'Code postal' => 'PARTICIPANTSCode_Postal',
            'e-mail' => 'PARTICIPANTSEmail',
            'Fax' => 'PARTICIPANTSFax',
            'Fonction' => 'PARTICIPANTSFonction',
            'Nom' => 'PARTICIPANTSNom',
            'Participation' => 'PARTICIPANTSParticipation',
            'Pays' => 'PARTICIPANTSPays',
            'Tél.portable' => 'PARTICIPANTSPortable',
            'Prénom' => 'PARTICIPANTSPrenom',
            'Société' => 'PARTICIPANTSSociete',
            'Solde H' => 'PARTICIPANTSSoldeH',
            'Solde I' => 'PARTICIPANTSSoldeI',
            'Interventions' => 'PARTICIPANTSInterventions',
            'Hébergement' => 'PARTICIPANTSHebergement',
            'Prestations' => 'PARTICIPANTSPrestations',
            'Num.téléphone' => 'PARTICIPANTSTelephone',
            'Titre' => 'PARTICIPANTSTitre',
            'Ville' => 'PARTICIPANTSVille',
            'Url connexion' => 'PARTICIPANTSUrlConnect',
            'Doublon' => 'PARTICIPANTSDoublon',
            'Licence' => 'PARTICIPANTSLicence',
            'Labos' => 'PARTICIPANTSLabos',
            'Historique' => 'PARTICIPANTSHistorique',
            'Blacklist' => 'PARTICIPANTSBlacklist',
            'Routage' => 'PARTICIPANTSRoutage',
        ];
    }

    public static function title(): string
    {
        return 'Personnalisation Participant';
    }

    public static function icon(): string
    {
        return 'accessibility-check';
    }

}
