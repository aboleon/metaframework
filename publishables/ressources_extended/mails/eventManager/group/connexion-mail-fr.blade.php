<x-mail-layout>
    Bonjour {{$user->first_name}},<br /><br />

    <p>
        Tu es contact principal du groupe {{$group->name}} pour l'événement {{$event->texts->name}}.
        <br>
        Tu peux désormais gérer les prestations et hébergements pour les membres de ton groupe, en cliquant sur le lien ci-dessous:

        <a href="{{route('front.event.group.dashboard', [
            'locale' => "fr",
            'event' => $event,
    ])}}">Gérer mes groupes</a>
    </p>
</x-mail-layout>
