<x-mail-layout :banner="$mailed->banner">
    Le group manager {{$mailed->mainContactFullName}} a créé votre compte pour
    l'événement {{$mailed->eventName}}.
    <br>
    Pour vous connecter, utilisez <a href="{{$mailed->autoConnectUrl}}">ce lien.</a>

</x-mail-layout>