<x-front-mail-layout>

    <div style="text-align: center">
        <img src="{{ $eventMediaUrl }}"
             alt="{{$eventName}}"
             style="width: 100%; display: block; margin: 0 auto;" />
    </div>

    <div class="p-20">

        <p>
            Hello,<br>
            You have requested to receive your password to log in to your
            {{$eventName}} account.
        </p>
        <p>
            Below you will find your complete login credentials:
        </p>
        <p>
            Your username: {{$email}}<br>
            Your password: {{$password}}
        </p>
        <p>
            You can change your password at any time on your online account.
        </p>
        <p>
            See you soon at <a href="{{$eventUrl}}">{{$eventUrl}}</a>
        </p>
        <div class="bg-tint p-3">
            <a class="text-decoration-none" href="{{$eventUrl}}">{{$eventUrl}}</a>
        </div>
    </div>

</x-front-mail-layout>
