@extends('layouts.'. (auth()->check() && auth()->user()->hasRole('admin,dev') ? 'backend' : 'front'))

@if (isset($header))
    <x-slot name="header">
        <div class="p-2 text-dark fs-5 fw-bold">
            Une erreur est survenue
        </div>
    </x-slot>
@else
    @section('slot_header')
        <div class="p-2 text-dark fs-5 fw-bold">
            Une erreur est survenue
        </div>
    @endsection
@endif

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

@section('class_main')
    page-404
@endsection

@section('slot')

    <div class="error-container">
        <h1 class="text-dark">404</h1>
        <x-mfw::alert :message="$exception->getMessage()"/>

        @if (str_contains(request()->route()?->getName(), '.meta.'))
            <a class="btn btn-info" style="color: white;background: var(--ab-blue)"
               href="{{ route('panel.meta.create', ['type' => request()->route('type')]) }}">Créer un contenu de type
                <strong>{{ request()->route('type') }}</strong></a>
        @endif

        @push('js')
        <script>
            $(function() {
               $('.error-container').css('height', window.innerHeight-150);
            });
        </script>
        @endpush

    </div>
@endsection
