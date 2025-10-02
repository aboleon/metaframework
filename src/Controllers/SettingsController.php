<?php

namespace MetaFramework\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use MetaFramework\Accessors\Routing;
use MetaFramework\Models\Setting;
use MetaFramework\Support\Traits\Responses;

class SettingsController extends Controller
{
    use Responses;

    public function index(): Renderable
    {
        return view('mfw::settings')->with([
            'config_settings' => config('mfw-settings'),
            'settings' => Setting::getAllSettings(),
        ]);
    }

    public function update(): RedirectResponse
    {

        try {
            $configs = Setting::getConfigElementsKeys();
            $rules = Setting::getValidationRules();


            $data = array_intersect_key(request()->input(), array_flip($configs));


            foreach ($data as $key => $val) {

                if ($rules && array_key_exists($key, $rules)) {
                    $this->validate(request(), $rules);
                }
                Setting::add($key, $val);

            }

            cache()->forget('mfw-settings');

            $this->responseSuccess('Configuration enregistré avec succès');
        } catch (\Throwable $e) {
            $this->responseException($e);
        }

        return $this->sendResponse();
    }
}
