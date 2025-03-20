<x-front-mail-layout>
    <div class="p-20">
        <p>
            Bonjour,<br>
            {{$user->names()}} souhaite annuler sa venue à l'événement {{ $event->texts->name }}.
            <br>
            Merci de le contacter directement.
        </p>
    </div>

</x-front-mail-layout>
