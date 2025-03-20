<?php

namespace App\Http\Requests;

use App\Traits\Locale;
use Illuminate\Foundation\Http\FormRequest;

class RoomRequest extends FormRequest
{
    use Locale;

    private PlaceRoomSetupRequest $setup_validation;

    public function __construct()
    {
        parent::__construct();
        $this->setup_validation = new PlaceRoomSetupRequest();
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
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
        return array_merge(
            [
                'name.' . $this->defaultLocale() => 'required|string|max:255',
                'level.' . $this->defaultLocale() => 'nullable|string|max:255'
            ],
            $this->setup_validation->rules()
        );
    }

    public function messages(): array
    {
        return array_merge(
            [
                'name.' . $this->defaultLocale() . '.required' => __('validation.required', ['attribute' => "Le nom en ". __('lang.'.$this->defaultLocale().'.label')])
            ],
            $this->setup_validation->messages()
        );
    }
}
