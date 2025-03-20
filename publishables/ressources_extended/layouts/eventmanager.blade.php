@extends('layouts.backend')
@push('css')
    {!! csscrush_tag(public_path('css/eventmanager.css')) !!}
@endpush

@section('navigation-vertical')
    @include('events.manager.navigation-vertical', ['event' => $event])
@stop
