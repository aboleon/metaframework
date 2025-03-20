<x-mail-layout>
    Bonjour {{ $user->names() }},<br/><br/>

    <p>Un nouveau mot de passe a été généré par un administrateur.</p>

    <p>{!! $password !!}</a></p>

</x-mail-layout>
