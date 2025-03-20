<template id="dict-dynamic-template">
    <div class="mt-3 dict-dynamic-form shadow p-3 w-100 bg-body-secondary rounded position-absolute mt-3 dict-dynamic-form z-3">
        @foreach(config('mfw.translatable.locales') as $locale)
            <x-mfw::input name="dict-dynamic-name[{{ $locale }}]" class="mb-2" label="Texte en {{ __('lang.'.$locale.'.label') }}"/>
        @endforeach
        <div class="d-flex justify-content-between mt-3 mb-1">
            <span class="btn btn-secondary btn-sm cancel">Annuler</span>
            <span class="btn btn-warning btn-sm save"><i class="fa-solid fa-check"></i> Enregistrer</span>
        </div>
    </div>
</template>
