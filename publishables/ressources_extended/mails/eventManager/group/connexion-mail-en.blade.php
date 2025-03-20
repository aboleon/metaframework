<x-mail-layout>
    Hi {{$user->first_name}},<br /><br />

    <p>
        You are the main contact for the group {{$group->name}} for the event {{$event->texts->name}}.
        <br>
        You can now manage the services and accommodations for the members of your group by clicking on the link below:
        <a href="{{route('front.event.group.dashboard', [
            'locale' => "fr",
            'event' => $event,
    ])}}">Manage my groups</a>
    </p>
</x-mail-layout>
