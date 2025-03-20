<?php

namespace App\Modules\CustomFields\Traits;

use App\Modules\CustomFields\Models\CustomFieldContent;
use App\Modules\CustomFields\Models\CustomFieldForm;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use MetaFramework\Traits\Responses;
use Throwable;

trait HasCustomFields
{
    use Responses;

    public function customForm(): MorphOne
    {
        return $this->morphOne(CustomFieldForm::class, 'model');
    }

    public function saveCustomFormFields(): void
    {
        if (!$this->customForm) {
            return;
        }

        $this->customForm->content()->delete();

        try {

            if (request()->filled('custom_fields')) {
                $data = [];
                $input = request('custom_fields');

                foreach ($input['key'] as $key) {

                    if (!array_key_exists($key, $input)) {
                        continue;
                    }

                    if (is_array($input[$key])) {
                        foreach ($input[$key] as $line_key => $item) {
                            $data[] = new CustomFieldContent(['key' => $key, 'value' => $line_key]);
                        }
                        continue;
                    }

                    $data[] = new CustomFieldContent(['key' => $key, 'value' => $input[$key]]);

                }

                $this->customForm->content()->saveMany($data);

            }
        } catch (Throwable $e) {
            $this->responseException($e);
        }
    }

}
