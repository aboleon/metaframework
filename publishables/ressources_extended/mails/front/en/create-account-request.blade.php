<x-front-mail-layout>

    <div style="text-align: center">
        <img src="{{ $eventMediaUrl }}"
             alt="{{$eventName}}"
             style="width: 100%; display: block; margin: 0 auto;" />
    </div>

    <div class="p-20">

        <p>
            Hi,<br>
            We have received a request to create an account for this email for the event {{$eventName}}.
        </p>
        <p>
            If you are not the originator of this request, you can ignore this email.
        </p>
        <p>
            If you are the originator of this request, please click on the link below to create your account:
            <a href="{{$createAccountUrl}}">{{$createAccountUrl}}</a>
        </p>
        <div class="bg-tint p-3">
            <a class="text-decoration-none" href="{{$eventUrl}}">{{$eventUrl}}</a>
        </div>
    </div>

</x-front-mail-layout>
