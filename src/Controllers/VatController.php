<?php

namespace MetaFramework\Controllers;

use Illuminate\Database\Eloquent\SoftDeletes;
use MetaFramework\Models\Vat;
use MetaFramework\Support\Traits\Responses;
use MetaFramework\Services\Validation\ValidationTrait;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Throwable;

class VatController extends Controller
{
    use Responses;
    use SoftDeletes;
    use ValidationTrait;

    public function index(): Renderable
    {
        return view('mfw::vat.index')->with('data', Vat::all());
    }

    public function create(): Renderable
    {
        $data = [
            'data' => new Vat,
            'route' => route('mfw.vat.store')
        ];

        return view('mfw::vat.edit')->with($data);
    }


    public function store(): RedirectResponse
    {
        $this->validation_rules = [
            'vat.rate' => 'numeric|unique:vat,rate',
            'vat.default' => 'nullable'
        ];
        $this->validation_messages = [
            'vat.rate.numeric' => __('validation.integer', ['attribute' => __('mfw-sellable.vat.label')]),
            'vat.rate.unique' => __('validation.unique', ['attribute' => __('mfw-sellable.vat.label')]),
        ];
        $this->validation();

        try {
            $vat = Vat::create(
                $this->validated_data['vat']
            );

            $vat->manageDefaultState();

            $this->responseSuccess(__('mfw.record_created'));
            $this->redirect_route = 'mfw.vat.index';

        } catch (Throwable $e) {
            $this->responseException($e);
        } finally {
            return $this->sendResponse();
        }
    }

    public function edit(Vat $vat): Renderable
    {
        $data = [
            'data' => $vat,
            'route' => route('mfw.vat.update', $vat)
        ];

        return view('mfw::vat.edit')->with($data);
    }

    public function update(Vat $vat): RedirectResponse
    {
        $this->validation_rules = [
            'vat.rate' => 'numeric|unique:vat,rate,'.$vat->id,
            'vat.default' => 'nullable'
        ];
        $this->validation_messages = [
            'vat.rate.numeric' => __('validation.integer', ['attribute' => __('mfw-sellable.vat.label')]),
            'vat.rate.unique' => __('validation.unique', ['attribute' => __('mfw-sellable.vat.label')]),
        ];
        $this->validation();


        try {
            $vat->update(
                $this->validated_data['vat']
            );

            $vat->manageDefaultState();

            $this->responseSuccess(__('mfw.record_created'));
            $this->redirect_route = 'mfw.vat.index';

        } catch (Throwable $e) {
            $this->responseException($e);
        } finally {
            return $this->sendResponse();
        }
    }

    public function destroy(Vat $vat): RedirectResponse
    {
        try {
            if ($vat->default) {
                Vat::query()->where('id', '!=', $vat->id)->first()->update(['default' => null]);
                Cache::forget('default_vat_rate');
            }
            $vat->delete();
        } catch (Throwable $e) {
            $this->responseException($e, __('mfw-sellable.vat.is_used'));
        }

        return $this->sendResponse();

    }
}
