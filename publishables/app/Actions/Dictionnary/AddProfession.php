<?php

namespace App\Actions\Dictionnary;

use App\Accessors\Dictionnaries;
use App\Models\DictionnaryEntry;
use Illuminate\Support\Str;
use InvalidArgumentException;
use MetaFramework\Traits\Ajax;

class AddProfession
{
    use Ajax;

    private ?\App\Models\Dictionnary $dictionnary;
    private DictionnaryEntry $entry;

    public function __construct()
    {
        $this->enableAjaxMode();
        $this->fetchInput();

        $this->ensureIsValidDictionnary('professions');

        $this->entry = new DictionnaryEntry;
        $this->entry->dictionnary_id = $this->dictionnary->id;
    }

    public function addSubEntry(): array
    {
        $this->ensureInputIsProvided('dynamic_profession');

        if ($this->hasErrors()) {
            return $this->fetchResponse();
        }

        if ($this->dictionnary->entries->pluck('entries.*.name')->flatten()->unique()->map(fn($a) => Str::slug($a))->contains(Str::slug(request('dynamic_profession.fr')))) {
            $this->responseWarning("Le terme " . request('dynamic_profession.fr') . ' existe déjà.');
            return $this->fetchResponse();
        }

        $this->entry->parent = request('optgroup');
        $this->processCommonEntry('dynamic_profession');

        $this->responseElement('callback', 'appendDymanicMetaEntry');
        $this->responseElement('optgroup', request('optgroup'));

        return $this->fetchResponse();
    }

    public function addEntry(): array
    {
        $this->ensureInputIsProvided('dynamic_profession_group');

        if ($this->hasErrors()) {
            return $this->fetchResponse();
        }

        if ($this->dictionnary->entries->pluck('name')->map(fn($a) => Str::slug($a))->contains(Str::slug(request('dynamic_profession_group.fr')))) {
            $this->responseWarning("Le terme " . request('dynamic_profession_group.fr') . ' existe déjà.');
            return $this->fetchResponse();
        }

        $this->processCommonEntry('dynamic_profession_group');

        $this->responseElement('callback', 'appendDymanicMetaGroup');

        return $this->fetchResponse();
    }

    private function ensureInputIsProvided(string $input): void
    {
        foreach (config('mfw.translatable.locales') as $locale) {
            if (!request()->filled($input . '.' . $locale)) {
                $this->responseWarning("Le texte en " . __('lang.' . $locale . '.label') . ' est absent.');
            }
        }
    }

    private function ensureIsValidDictionnary(string $dictionnary): void
    {

        $this->dictionnary = Dictionnaries::dictionnary($dictionnary);

        if (!$dictionnary) {
            throw new InvalidArgumentException(
                sprintf(
                    '"%s" ne retourne pas un dictionnaire',
                    $dictionnary
                )
            );
        }
    }

    private function processCommonEntry(string $string): void
    {
        foreach (request($string) as $locale => $content) {
            $this->entry->setTranslation(key: 'name', locale: $locale, value: $content);
        }
        $this->entry->save();

        Dictionnaries::reset($this->dictionnary->slug);

        $this->responseSuccess("L'entrée est ajoutée.");
        $this->responseElement('term', request($string . '.fr'));
        $this->responseElement('entry', $this->entry);

    }
}
