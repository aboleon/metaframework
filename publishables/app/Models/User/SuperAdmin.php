<?php

namespace App\Models\User;

use App\Interfaces\UserCustomDataInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuperAdmin extends Model implements UserCustomDataInterface
{
    use HasFactory;

    public function profileData(): array
    {
        return [
            'job' => [
                'type' => 'text',
                'label' => __('forms.job'),
                'required' => true,
            ],
            'phone' => [
                'type' => 'text',
                'label' => __('forms.fields.direct_line'),
                'required' => true,
            ],
            'mobile' => [
                'type' => 'text',
                'label' => __('forms.fields.mobile_phone'),
                'required' => true,
            ],
        ];
    }

    public function mediaSettings(): array
    {
        return [
            '_media' =>
                [
                    'label' => 'Photo',
                ]
        ];
    }
}
