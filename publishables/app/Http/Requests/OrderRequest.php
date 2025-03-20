<?php

namespace App\Http\Requests;

use App\Enum\OrderClientType;
use App\Validation\InvoiceableValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
{
    private array $invoiceable;
    private string $prefix = 'order.';

    public function authorize(): bool
    {
        return true;
    }

    public function __construct()
    {
        parent::__construct();
        $this->invoiceable = (new InvoiceableValidation())->logic();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return array_merge(
            [
                // le compte payeur
                'selected_client_id'             => 'exists:'.(request('selected_client_type') == 'group' ? 'groups' : 'users').',id',
                'selected_client_type'           => Rule::in(OrderClientType::keys()),

                // le compte d'affectation
                $this->prefix.'client_type'      => Rule::in(OrderClientType::keys()),
                $this->prefix.'contact_id'       => 'required_if:'.$this->prefix.'client_type,contact',
                $this->prefix.'group_id'         => 'required_if:'.$this->prefix.'client_type,group',
                $this->prefix.'date'             => 'required|date_format:d/m/Y',
                $this->prefix.'external_invoice' => 'nullable',
                $this->prefix.'note'             => 'nullable|string',
                $this->prefix.'po'               => 'nullable|string',
                $this->prefix.'terms'            => 'nullable|string',
            ],
            ((request()->filled('is_amended_order') || request('as_orator')) == 1 ? [] : $this->invoiceable['rules']),
        );
    }

    public function messages(): array
    {
        return array_merge(
            [
                'selected_client_id.exists'            => __('validation.exists', ['attribute' => "Le compte"]),
                'selected_client_type.in'              => __('validation.in', ['attribute' => "Le type de client", 'values' => collect(OrderClientType::translations())->join(',')]),
                $this->prefix.'client_type.in'         => __('validation.in', ['attribute' => "Le type de client", 'values' => collect(OrderClientType::translations())->join(',')]),
                $this->prefix.'contact_id.required_if' => __('validation.required_if', ['attribute' => "Le compte participant", 'other' => "le compte d'affectation", 'value' => 'participant']),
                $this->prefix.'group_id.required_if'   => __('validation.required_if', ['attribute' => "Le compte groupe", 'other' => "le compte d'affectation", 'value' => 'groupe']),
                $this->prefix.'date.required'          => __('validation.required', ['attribute' => "La date de la commande"]),
                $this->prefix.'date.date_format'       => __('validation.date_format', ['attribute' => "La date de la commande", 'format' => 'JJ/MM/AAAA']),
                $this->prefix.'.po.string'             => __('validation.string', ['attribute' => 'PO']),
                $this->prefix.'note.string'            => __('validation.string', ['attribute' => 'Notes']),
                $this->prefix.'terms.string'           => __('validation.string', ['attribute' => 'Termes de paiement']),

            ],
            (request()->filled('is_amended_order') ? [] : $this->invoiceable['messages']),
        );
    }
}
