<?php

namespace App\Http\Requests;

use App\Enum\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:1',
            'date' => 'required',
            'payment_method' => Rule::enum(PaymentMethod::class),
            'authorization_number' => 'nullable|string',
            'card_number' => 'nullable|string',
            'bank' => 'nullable|string',
            'issuer' => 'nullable|string',
            'check_number' => 'nullable|string',
            'topay' => 'required|numeric'
        ];
    }


    /**
     * @return array<string,mixed>
     */
    public function messages(): array
    {
        return [
            'amount.required' => __('validation.required', ['attribute' => "Le montant total à payer"]),
            'topay.required' => __('validation.required', ['attribute' => "Le montant"]),
            //'amount.lte' => "Le montant saisi est supérieur au reste à payer",
            'amount.numeric' => __('validation.numeric', ['attribute' => "Le montant"]),
            'topay.numeric' => __('validation.numeric', ['attribute' => "Le montant total à payer"]),
            'amount.min' => __('validation.min.numeric', ['attribute' => "Le montant"]),
            'order_id.required' => __('validation.required', ['attribute' => "L'ID de la commande"]),
            'order_id.exists' => __('validation.exists', ['attribute' => "La commande indiquée avec cet ID"]),
            'date.required' => __('validation.required', ['attribute' => 'La date']),
            'authorization_number.string' => __('validation.string', ['attribute' => "Le numéro d'authorisation"]),
            'card_number.string' => __('validation.string', ['attribute' => "Le numéro d'authorisation"]),
            'bank.string' => __('validation.string', ['attribute' => "Le numéro d'authorisation"]),
            'issuer.string' => __('validation.string', ['attribute' => "Le numéro d'authorisation"]),
            'check_number.string' => __('validation.string', ['attribute' => "Le numéro d'authorisation"])
        ];
    }
}
