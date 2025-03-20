<?php

namespace App\Actions;

use App\Http\Requests\EstablishmentRequest;
use App\Models\Establishment;
use MetaFramework\Services\Validation\ValidationInstance;
use MetaFramework\Traits\Ajax;
use Throwable;

class EstablishmentActions
{
    use Ajax;

    public function __construct()
    {
        $this->enableAjaxMode();
        $this->fetchInput();

    }

    public function create(): array
    {
        $establismentRequest = new EstablishmentRequest();

        $validation = new ValidationInstance();
        $validation->addValidationRules($establismentRequest->rules());
        $validation->addValidationMessages($establismentRequest->messages());
        $validation->validation();

        try {
            $establishment = Establishment::create($validation->validatedData('establishment'));
            $this->responseSuccess(__('ui.record_created'));
            $this->responseElement('callback', 'appendDymanicEstablishment');
            $this->responseElement('establishment', $establishment);
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }

    public function getEstablishments(): array
    {
        try {
            $establishments = Establishment::select('id', 'name')
                ->orderBy('name')
                ->get()
                ->map(function ($establishment) {
                    return [
                        'value' => $establishment->id,
                        'label' => $establishment->name,
                    ];
                })
                ->toArray();

            return ["items" => $establishments];

        } catch (Throwable $e) {
            $this->responseException($e);
            return $this->fetchResponse();
        }
    }


    public function getEstablishmentsForCountry(string $country_code): array
    {
        try {

            $establishments = Establishment::select('id', 'name', 'locality')
                ->where('country_code', $country_code)
                ->orderBy('locality')
                ->orderBy('name')
                ->get();

            return [
                'establishments' => $establishments->toArray(),
                'localities' => $establishments->pluck('locality')->unique()->toArray(),
                'callback' => 'setEstablishmentsForCountry'
            ];

        } catch (Throwable $e) {
            $this->responseException($e);
            return $this->fetchResponse();
        }
    }

    public function getEstablishmentsForLocality(string $country_code, string $locality): array
    {
        try {
            $establishments = Establishment::query()->select('id', 'name')->where('country_code', $country_code);
            if ($locality != 'all') {
                $establishments->where('locality', $locality);
            }

            return [
                'establishments' => $establishments->get()->toArray(),
                'callback' => 'setEstablishmentsForLocality'
            ];

        } catch (Throwable $e) {
            $this->responseException($e);
            return $this->fetchResponse();
        }
    }
}
