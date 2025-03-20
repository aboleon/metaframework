<x-mail-layout>
    Bonjour {{$transport->eventContact->user->first_name}},<br /><br />

    <p>Vos billets et informations de voyage <b>retour</b> pour
        l'événement {{$transport->eventContact->event->texts->subname}}
        sont disponibles.</p>

    @php
        $url = route('front.event.transport.edit',  [
            'event' => $transport->eventContact->event,
            'locale' => 'fr',
        ]);
    @endphp

    <p>
        Veuillez <a href="{{$url}}">cliquer ici pour consulter les détails</a>.
    </p>

</x-mail-layout>
