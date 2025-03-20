<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\HotelAddress;
use Illuminate\Http\JsonResponse;
use MetaFramework\Accessors\Countries;
use MetaFramework\Interfaces\GooglePlacesInterface;
use MetaFramework\Traits\Responses;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;

class SearchController extends Controller
{
    use Responses;

    public function parse(Request $request): Renderable|RedirectResponse
    {
        if (!request()->filled('action')) {
            $this->responseWarning("Aucune action de recherche n'est définie");
            $this->flash();
            return view('errors.exception');
        }

        if (!method_exists($this, request()->action)) {
            $this->responseWarning("Cette action de recherche n'existe pas.");
            $this->flash();
            return view('errors.exception');
        }

        return $this->{request()->action}($request);
    }
}
