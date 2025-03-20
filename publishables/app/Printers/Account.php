<?php

namespace App\Printers;

use App\Models\AccountCard;
use App\Models\AccountDocument;
use App\Models\AccountPhone;
use App\Models\Establishment;
use Illuminate\Support\Collection;
use MetaFramework\Accessors\Countries;
use MetaFramework\Interfaces\GooglePlacesInterface;

class Account
{
    public static function address(GooglePlacesInterface $data): string
    {
        $open_tag = '<address>';
        $close_tag = '</address>';

        $output = $open_tag .
            '<h5 class="card-title text-dark">' . ($data->name ?: 'Sans titre') . '</h5>' .
            trim($data->street_number . ' ' . $data->route) . '<br>' .
            trim($data->postal_code . ' ' . $data->locality) . '<br>' .
            Countries::getCountryNameByCode($data->country_code) . '<br>';

        if ($data->billing) {
            $output .= '<h6 class="mt-2"><span class="badge bg-warning text-dark">Adresse de facturation</span></h6>';
        }
        if ($data instanceof Establishment) {
            $output .= '<h6 class="mt-2"><span class="badge bg-warning text-dark">Adresse d\'établissement</span></h6>';
        }
        $output .= $close_tag;

        return $output;
    }

    public static function card(AccountCard $data): string
    {
        return "<div class=\"account-doc\"><h5 class=\"card-title text-dark\">" . ($data->name ?: 'Sans titre') . "</h5><div class='row'><div class='col-md-6'><strong>" . __('ui.number') . "</strong>" . $data->serial . "</div><div class='col-md-6'><strong>" . __('ui.expires_at') . "</strong>" . $data->expires_at?->format('d/m/Y') . "</div></div></div>";
    }

    public static function document(AccountDocument $data): string
    {
        return "<div class=\"account-doc\"><h5 class=\"card-title text-dark\">" . ($data->name ?: 'Sans titre') . "</h5><div class='row'><div class='col-12'><strong>" . __('ui.number') . "</strong>" . $data->serial . "</div><div class='col-md-6'><strong>" . __('ui.emitted_at') . "</strong>" . $data->emitted_at->format('d/m/Y') . "</div><div class='col-md-6'><strong>" . __('ui.expires_at') . "</strong>" . $data->expires_at->format('d/m/Y') . "</div></div></div>";
    }

    public static function phone(AccountPhone $data): string
    {
        $output = '<h5 class="card-title text-dark">' . ($data->name ?: 'Sans titre') . '</h5>';

        $output .= $data->phone;

        if ($data->default) {
            $output .= '<h6 class="mt-2"><span class="badge bg-warning text-dark">Principal</span></h6>';
        }

        return $output;
    }

    public static function associatedGroups(\App\Models\Account $account): string
    {
        return $account->groups->isEmpty()
            ? ''
            : $account->groups->map(fn($item) => '- <a href="' . route('panel.groups.edit', $item) . '">' . $item['name'] . ' / ' . $item['company'] . '</a>')->join('<br>');

    }
    public static function associatedEvents(\App\Models\Account $account): string
    {
        return $account->events->isEmpty()
            ? ''
            : $account->events->map(fn($item) => '- <a href="' . route('panel.events.edit', $item) . '">' . (new EventPrinter($item))->names() . '</a>')->join('<br>');

    }
}
