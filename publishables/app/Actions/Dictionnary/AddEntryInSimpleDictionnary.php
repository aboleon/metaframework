<?php

namespace App\Actions\Dictionnary;

use App\Accessors\Dictionnaries;
use App\Models\DictionnaryEntry;
use Illuminate\Support\Str;
use MetaFramework\Traits\Ajax;

class AddEntryInSimpleDictionnary
{

    use Ajax;

    public function __invoke(): array
    {
        $this->enableAjaxMode();
        $this->fetchInput();

        if (!request()->filled('dict') or !request()->filled('dict_select_id')) {
            $this->responseError("Le dictionnaire n'a pas pu être identifié.");
            return $this->fetchResponse();
        }

        if (!is_array(request('dict-dynamic-name'))) {
            $this->responseError("Les données ne sont pas correctement composées.");
            return $this->fetchResponse();
        }

        foreach (config('mfw.translatable.locales') as $locale) {
            if ('fr' === $locale) {
                if (!request()->filled('dict-dynamic-name.' . $locale)) {
                    $this->responseWarning("Le texte en " . __('lang.' . $locale . '.label') . ' est absent.');
                }
            }
        }
        if ($this->hasErrors()) {
            return $this->fetchResponse();
        }

        $dictionnary = Dictionnaries::dictionnary(request('dict'));
        if (!$dictionnary) {
            $this->responseError("Le dictionnaire n'a pas pu être récupéré.");
            return $this->fetchResponse();
        }

        if ($dictionnary->entries->pluck('name')->map(fn($item) => Str::slug($item))->contains(Str::slug(request('dict-dynamic-name.fr')))) {
            $this->responseWarning("Le terme " . request('dict-dynamic-name.fr') . ' existe déjà.');
            return $this->fetchResponse();
        }

        $entry = new DictionnaryEntry;
        $entry->dictionnary_id = $dictionnary->id;

        foreach (request('dict-dynamic-name') as $locale => $content) {
            $entry->setTranslation(key: 'name', locale: $locale, value: $content);
        }
        $entry->save();

        if (request('subcallback') !== 'undefined') {
            $this->responseElement('subcallback', request('subcallback'));
        }

        $this->responseSuccess("L'entrée est ajoutée.");
        $this->responseElement('callback', 'appendDymanicDictionnaryEntry');
        $this->responseElement('dict_select_id', request('dict_select_id'));
        $this->responseElement('term', request('dict-dynamic-name.fr'));
        $this->responseElement('entry', $entry);

        Dictionnaries::reset(request('dict'));

        return $this->fetchResponse();
    }
}
