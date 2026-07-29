<?php

declare(strict_types=1);

namespace MetaFramework\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use MetaFramework\Models\SiteOwner;
use MetaFramework\Services\Validation\ValidationTrait;
use MetaFramework\Support\Traits\Responses;
use Throwable;

class SiteOwnerController extends Controller
{
    use Responses;
    use ValidationTrait;

    public function index(): Renderable
    {
        return view('mfw::siteowner')->with('data', SiteOwner::first());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->request_validation();
        $this->validation();

        try {
            $object = SiteOwner::firstOrNew();
            $object->name = $request['name'];
            $object->address = $request['address'];
            $object->manager = $request['manager'];
            $object->phone = $request['phone'];
            $object->vat_number = $request['vat_number'];
            $object->reg_number = $request['reg_number'];
            $object->email = $request['email'];
            $object->zip = $request['zip'];
            $object->locality = $request['locality'];
            $object->save();

            $this->responseSuccess(__('mfw::mfw.record_created'));

            Cache::forget('mfw_siteowner');

        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->sendResponse();
    }

    private function request_validation(): void
    {
        $this->validation_rules = [
            'name' => 'required',
            'address' => 'required',
            'manager' => 'required',
            'phone' => 'required',
            'vat_number' => 'required',
            'reg_number' => 'required',
            'email' => 'required',
            'zip' => 'required',
            'locality' => 'required',
        ];

        $this->validation_messages = [
            'name.required' => "Le nom de la structure n'est pas renseigné.",
            'address.required' => "L'adresse de la structure n'est pas renseignée.",
            'manager.required' => "Le gérant de la structure n'est pas renseigné.",
            'phone.required' => "Le numéro de téléphone n'est pas renseigné.",
            'vat_number.required' => "Le numéro TVA n'est pas renseigné.",
            'reg_number.required' => config('mfw.siteowner.reg_number')." n'est pas renseigné.",
            'email.required' => "L'adresse e-mail n'est pas renseignée.",
            'zip.required' => "Le code postal n'est pas renseigné.",
            'locality.required' => "La ville n'est pas renseignée.",
        ];
    }
}
