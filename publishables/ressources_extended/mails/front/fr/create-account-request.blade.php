<x-front-mail-layout>

    <div style="text-align: center">
        <img src="{{ $eventMediaUrl }}"
             alt="{{$eventName}}"
             style="width: 100%; display: block; margin: 0 auto;" />
    </div>

    <div class="p-20">

        <p>
            Bonjour,<br>
            Nous avons reçu une demande de création de compte pour cet email pour l'événement {{$eventName}}.
        </p>
        <p>
            Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet email.
        </p>
        <p>
            Si vous êtes à l'origine de cette demande, veuillez cliquer sur le lien ci-dessous pour créer votre compte :
            <a href="{{$createAccountUrl}}">{{$createAccountUrl}}</a>
        </p>
        <div class="bg-tint p-3">
            <a class="text-decoration-none" href="{{$eventUrl}}">{{$eventUrl}}</a>
        </div>
    </div>

</x-front-mail-layout>
