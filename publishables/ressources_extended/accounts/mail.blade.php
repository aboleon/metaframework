<x-backend-layout>
    <x-slot name="header">
        @include('accounts.shared.subedit_header')
    </x-slot>

    <div class="shadow p-3 mb-5 bg-body-tertiary rounded">
        <x-mfw::validation-banner />
        <x-mfw::response-messages />
        <div class="row m-3">
            <div class="col">

                <form method="post" action="{{ $route }}">
                    @if (isset($method))
                        @method($method)
                    @endif
                    @csrf

                    @if($redirect_to)
                        <input type="hidden" name="custom_redirect" value="{{ $redirect_to }}">
                    @endif
                    <fieldset class="position-relative">
                        <legend class="d-flex justify-content-between align-items-end">
                            <span>
                                Adresse e-mail<span class="text-secondary"> | {{ $account->names() }}</span>
                           </span>

                            @php
                                $url = $redirect_to ?? route('panel.accounts.edit', $account);
                            @endphp
                            <x-mfw::notice message="<a href='{{ $url }}#address-tabpane'>Adresses & Contacts</a>" />
                        </legend>
                        @include('accounts.form.mail')
                    </fieldset>
                    <div class="mt-5">
                        <x-mfw::btn-save />
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-backend-layout>
