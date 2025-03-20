<x-front-mail-layout>

    <div style="text-align: center">
        <img src="{{ $eventMediaUrl }}"
             alt="{{$eventName}}"
             style="width: 100%; display: block; margin: 0 auto;" />
    </div>

    <div class="p-20">

        <p>
            Bonjour,<br>
            Vous avez demandé à recevoir votre mot de passe pour vous connecter à votre
            compte {{$eventName}}.
        </p>
        <p>
            Vous trouverez ci-dessous vos identifiants de connexion complets :
        </p>
        <p>
            Votre identifiant : {{$email}}<br>
            Votre mot de passe : {{$password}}
        </p>
        <p>
            Vous pouvez modifier votre mot de passe à tout moment sur votre compte en ligne.
        </p>
        <p>
            A bientôt sur <a href="{{$eventUrl}}">{{$eventUrl}}</a>
        </p>
        <div class="bg-tint p-3">
            <a class="text-decoration-none" href="{{$eventUrl}}">{{$eventUrl}}</a>
        </div>
    </div>

</x-front-mail-layout>
