@php

    use App\Accessors\EventAccessor;
    use App\Accessors\GroupAccessor;
    use App\Helpers\Front\SudoerHelper;
    $user = Auth::user();
    $eventGroup = \App\Accessors\EventManager\EventGroups::getGroupByMainContact($event, $user);
    $groups = new GroupAccessor($eventGroup->group);
    $groupBillingAddress = $groups->billingAddress();

@endphp
<x-front-logged-in-layout :event="$event" :group-view="$groupView">
    <div class="">
        <a class="btn btn-sm btn-outline-primary mb-0"
           href="{{route('front.event.group.dashboard', $event)}}">Dashboard du groupe
            "{{$eventGroup?->group?->name}}"</a>
        <x-front.debugmark title="g={{$eventGroup?->group?->id}}; eg={{$eventGroup?->id}}"/>
    </div>


    <div class="mt-3">
        @if($groupBillingAddress)
            {{$slot}}
        @else
            @php
                $adminSubEmail = EventAccessor::getAdminSubscriptionEmail($event);
            @endphp
            <div class="alert alert-warning">
                <h5 class="alert-heading">Adresse de facturation du groupe manquante</h5>
                <p>
                    Vous devez renseigner une adresse de facturation pour le groupe pour pouvoir
                    continuer.
                </p>
                <a href="mailto:{{$adminSubEmail}}"
                   class="btn btn-sm btn-primary">
                    Veuillez contacter l'organisation.
                </a>
            </div>
        @endif
    </div>
</x-front-logged-in-layout>
