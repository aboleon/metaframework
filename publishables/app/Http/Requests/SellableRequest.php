<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SellableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'price' => 'numeric',
            'title.' . app()->getLocale() => 'required',
            'description.' . app()->getLocale() => 'required',
            'vat_id' => 'integer',
            'sold_per' => 'required',
            'category_id' => 'required_if:sellable_id,null',
            'sku' => 'nullable',
            'price_buy' => 'numeric'
        ];
    }

    public function messages(): array
    {
        return [
            'price.numeric' => __('validation.numeric', ['attribute' => "Le prix de vente"]),
            'price_buy.numeric' => __('validation.numeric', ['attribute' => "le prix d'achat"]),
            'vat_id.integer' => "Le taux de TVA n'est pas spécifié.",
            'sold_per.required' => __('validation.required', ['attribute' => __('mfw-sellable.sold_per')]),
            'category_id.required' => __('validation.required', ['attribute' => "La catégorie du catalogue"]),
            'title.' . app()->getLocale() . '.required' => __('validation.required', ['attribute' => __('mfw.title')]),
            'description.' . app()->getLocale() . '.required' => __('validation.required', ['attribute' => __('mfw.description')]),
        ];
    }
}
