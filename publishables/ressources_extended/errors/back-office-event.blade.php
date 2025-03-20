<x-event-manager-layout :event="$event">

    <x-slot name="header">
        <div class="p-2 text-dark fs-5 fw-bold">
            Une erreur est survenue
        </div>
    </x-slot>

@push('css')
    <style>
        .error-container {
            height: 100vh;
            display: flex;
            justify-content: center;
            flex-flow: column;
            align-items: center;
        }

        .error-container h1 {
            font-size: 50px;
            margin-bottom: 50px;
            font-weight: 700;
            line-height: 30px;
        }

        .error-container div {
            font-size: 22px;
        }
    </style>
@endpush

    <div class="error-container">
        <h1 class="text-dark">Erreur</h1>
        <x-mfw::alert :message="($message ?? session('event_error_message')) ?: 'Rien à faire ici'" class="simplified"/>


        @push('js')
        <script>
            $(function() {
               $('.error-container').css('height', window.innerHeight-150);
            });
        </script>
        @endpush

    </div>

</x-event-manager-layout>
