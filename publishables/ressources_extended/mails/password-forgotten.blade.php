<x-mail-layout>
    Bonjour,<br/><br/>

    <p>Vous nous avez demandé de réinitialiser votre mot de passe pour votre compte en utilisant l'adresse e-mail: {{ request('email') }}.</p>

    <p>S'il s'agit d'une erreur ou si vous n'avez pas demandé de réinitialisation de mot de passe, ignorez simplement cet e-mail et rien ne se passera.</p>

    <p>Pour réinitialiser votre mot de passe, visitez l'adresse suivante : <a href="{{ $reset_url }}">{{ $reset_url }}</a></p>
    <p>{{ __('auth.password.forgotten.reset_expires', ['count' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')]) }}</p>

</x-mail-layout>
