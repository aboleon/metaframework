<x-mail-layout :banner="$mailed->banner">
    Le group manager {{$mailed->groupManagerFullName}} a acheté des choses pour vous
    au nom du groupe {{$mailed->groupName}} pour l'événement {{$mailed->eventName}}.
    <br>
    Vous pouvez vous connecter et voir ce qu'il a acheté pour vous en cliquant sur <a href="{{$mailed->autoConnectUrl}}">ce lien.</a>
</x-mail-layout>