<form id="mfw-nav-dev-artisan-command-form">
    @csrf
    <x-mfw-inputable::input
        name="command"
        label="{{ __('mfw::mfw.dev.artisan.label') }}"
        :required="true"
        :params="[
            'autocomplete' => 'off',
            'maxlength' => 1000,
            'placeholder' => 'cache:clear',
        ]"
    />
    <small class="form-text text-muted">{{ __('mfw::mfw.dev.artisan.help') }}</small>
</form>
