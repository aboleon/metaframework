<?php

namespace App\Validation;

use App\Enum\OrderClientType;
use Illuminate\Validation\Rule;
use MetaFramework\Services\Validation\ValidationAbstract;
use App\Accessors\Dictionnaries;
use App\Enum\Civility;
use App\Models\Account;
use Illuminate\Validation\Rules\Enum;

class InvoiceableValidation extends ValidationAbstract
{
    private string $prefix = 'payer.';

    public function __construct(public ?Account $account = null)
    {

    }

    /**
     * @return array<string, array<int, \Illuminate\Validation\Rules\Enum|string>|string>>
     */
    public function rules(): array
    {
        return [
            $this->prefix . 'company' => 'nullable|string|max:255',
            $this->prefix . 'first_name' => 'nullable|string|max:255',
            $this->prefix . 'last_name' => 'required|string|max:255',
            $this->prefix . 'department' => 'nullable|string|max:255',
            $this->prefix . 'vat_number' => 'nullable|string|max:255',
            $this->prefix . 'street_number' => 'nullable|string',
            $this->prefix . 'route' => 'nullable|string',
            $this->prefix . 'complementary' => 'nullable|string',
            $this->prefix . 'postal_code' => 'nullable|string',
            $this->prefix . 'text_address' => 'nullable|string',
            $this->prefix . 'cedex' => 'nullable|string',
            $this->prefix . 'locality' => 'nullable|string',
            $this->prefix . 'country_code' => 'required|string',
            $this->prefix . 'address_id' => 'nullable|integer',
            $this->prefix . 'account_type' =>  Rule::in(OrderClientType::keys()),
        ];

    }

    /**
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return [
            $this->prefix . 'company.string' => __('validation.string', ['attribute' => "La société"]),
            $this->prefix . 'first_name.required' => __('validation.required', ['attribute' => "Le prénom"]),
            $this->prefix . 'last_name.required' => __('validation.required', ['attribute' => "Le nom"]),
            $this->prefix . 'department.string' => __('validation.string', ['attribute' => "La fonction"]),
            $this->prefix . 'vat_number.required' => __('validation.string', ['attribute' => Dictionnaries::title('professions')]),
            $this->prefix . 'address_line_1.required' => __('validation.required', ['attribute' => "Ligne adresse N 1"]),
            $this->prefix . 'address_line_1.string' => __('validation.string', ['attribute' => "Ligne adresse N 1"]),
            $this->prefix . 'address_line_2.string' => __('validation.string', ['attribute' => "Ligne adresse N 2"]),
            $this->prefix . 'address_line_2.string' => __('validation.string', ['attribute' => "Ligne adresse N 3"]),
            $this->prefix . 'zip.required' => __('validation.required', ['attribute' => "Le code postal"]),
            $this->prefix . 'locality.required' => __('validation.required', ['attribute' => "La ville"]),
            $this->prefix . 'locality.string' => __('validation.string', ['attribute' => "La ville"]),
            $this->prefix . 'country_code.required' => __('validation.required', ['attribute' => "Le pays"]),
            $this->prefix . 'country_code.string' => __('validation.string', ['attribute' => "Le pays"]),
            $this->prefix . 'country_code.integer' => __('validation.integer', ['attribute' => "L'identifiant de l'adresse"]),
            $this->prefix . 'account_type' => __('validation.in', ['attribute' => "Le type de client", 'values' => collect(OrderClientType::translations())->join(',')])

        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function logic(): array
    {
        return [
            'rules' => $this->rules(),
            'messages' => $this->messages(),
        ];
    }
}
