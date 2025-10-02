<?php

namespace MetaFramework\Actions;


use Exception;
use Illuminate\Database\Eloquent\Model;
use MetaFramework\Accessors\Locale;
use MetaFramework\Polyglote\Traits\Translation;
use MetaFramework\Support\Traits\Responses;
use Throwable;

class Translator
{
    use Responses;

    protected Model $object;

    /**
     * @throws \Exception
     */
    public function __construct(Model $object)
    {
        $this->object = $object;

        $traits = class_uses_recursive($this->object::class);

        if (!in_array(Translation::class, $traits)) {
            throw new Exception($this->object::class . " n'utilise pas " . Translation::class);
        }
    }

    public function update(): static
    {
        try {
            foreach ($this->object->translatable as $value) {
                foreach (Locale::projectLocales() as $locale) {
                    $this->object->saveTranslation($value, $locale, $this->object->translatableFromRequest($value, $locale));
                }
            }
            $this->save();
        } catch (Throwable $e) {
            $this->responseException($e);
        } finally {
            //$this->responseElement('object', $this->object);
            return $this;
        }
    }

    public function save(): static
    {
        $this->object->save();
        return $this;
    }

    public function fetchModel(): object
    {
        return $this->object;
    }
}
