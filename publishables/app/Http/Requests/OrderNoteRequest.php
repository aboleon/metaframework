<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderNoteRequest extends FormRequest
{

    public function __construct()
    {
        parent::__construct();

    }

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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'note' => 'required',
            'order_id' => 'exists:orders,id',
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function messages(): array
    {
        return [
            'note.required' => "Vous devez saisir une note",
            'order_id.exists' => "La commande pour laquelle essayez d'affecter cette note n'existe pas.",
        ];

    }
}
