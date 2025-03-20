<?php

namespace App\Actions\Groups;

use App\Actions\AccountProfile\View\AccountProfileExportView;
use App\Actions\Export\BaseExportAction;
use App\Actions\Groups\View\GroupExportView;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExportGroupsAction extends BaseExportAction
{
    public static $fieldsMapping = [
        'id' => 'ID du groupe',
        'name' => 'Nom',
        'company' => 'Raison sociale',
        'billing_comment' => 'Commentaire facturation',
        'siret' => 'N° Siret',

//        'contact_user_id' => 'ID du contact',
//        'contact_first_name' => 'Prénom',
//        'contact_last_name' => 'Nom de famille',
//        'contact_account_type' => 'Type de compte',
//        'contact_base' => 'Base',
//        'contact_domain' => 'Domaine',
//        'contact_title' => 'Titre',
//        'contact_profession' => 'Profession',
//        'contact_savant_society' => 'Société savante',
//        'contact_civ' => 'Civilité',
//        'contact_birth' => 'Date de naissance',
//        'contact_cotisation_year' => 'Année de cotisation',
//        'contact_blacklisted' => 'Blackliste',
//        'contact_created_by' => 'Créé par',
//        'contact_blacklist_comment' => 'Commentaire blackliste',
//        'contact_notes' => 'Notes',
//        'contact_function' => 'Fonction',
//        'contact_rpps' => 'Rpps',
        //
        //
        'main_address_billing' => 'Facturation de l\'adresse principale',
        'main_address_name' => 'Nom de l\'adresse principale',
        'main_address_street_number' => 'Numéro de rue de l\'adresse principale',
        'main_address_route' => 'Itinéraire de l\'adresse principale',
        'main_address_locality' => 'Localité de l\'adresse principale',
        'main_address_postal_code' => 'Code postal de l\'adresse principale',
        'main_address_country_code' => 'Code pays de l\'adresse principale',
        'main_address_country_name' => 'Nom du pays de l\'adresse principale',
        'main_address_administrative_area_level_1' => 'Niveau administratif 1 de l\'adresse principale',
        'main_address_administrative_area_level_1_short' => 'Niveau administratif 1 de l\'adresse principale (court)',
        'main_address_administrative_area_level_2' => 'Niveau administratif 2 de l\'adresse principale',
        'main_address_text_address' => 'Adresse textuelle principale',
    ];


    public function __construct(
        protected array $groupIds,
        protected array $fields
    )
    {
    }

    protected function prepareSheet($sheet): void
    {
        //--------------------------------------------
        // header
        //--------------------------------------------
        $columnIndex = 'A';
        foreach ($this->fields as $field) {
            $fieldLabel = self::$fieldsMapping[$field];
            $sheet->setCellValue($columnIndex . '1', $fieldLabel);

            $sheet->getColumnDimension($columnIndex)->setAutoSize(true);
            $sheet->getStyle($columnIndex . '1')->getFont()->setBold(true);
            $columnIndex++;
        }

        // background color
        $lastHeaderColumn = --$columnIndex;
        $sheet->getStyle('A1:' . $lastHeaderColumn . '1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('D3D3D3');


        //--------------------------------------------
        // body
        //--------------------------------------------
        $profiles = GroupExportView::whereIn('id', $this->groupIds)->get()->toArray();

        $rowIndex = 2;
        foreach ($profiles as $profile) {
            $columnIndex = 'A';
            foreach ($this->fields as $field) {
                $value = $profile[$field] ?? null;
                $sheet->setCellValue($columnIndex . $rowIndex, $value);
                $sheet->getStyle($columnIndex . $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

                $columnIndex++;
            }
            $rowIndex++;
        }

    }
}