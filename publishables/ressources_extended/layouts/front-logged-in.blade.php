@php
    use App\Accessors\EventAccessor;
    use App\Services\Pec\PecParser;
    use App\Accessors\Front\FrontCache;
@endphp
<x-front-layout>


    @php
        $eventName = $event->texts->name;
        $avatarSrc = \App\Accessors\Accounts::getPhotoByAccount($account);
    $eventContactAccessor = (new \App\Accessors\EventContactAccessor())->setEventContact($eventContact);
    $adminSubEmail = EventAccessor::getAdminSubscriptionEmail($event);
    @endphp

    @pushonce("css")
        {!! csscrush_tag(public_path('front/bstheme/css/user.css')) !!}
    @endpushonce
    <div class="dashboard mt-4">
        @if($isConnectedAsManager)
            <div class="container px-0 pb-1" style="border-bottom: 1px dashed #818181;">
                <div class="row align-items-center">
                    <div class="col-sm-8 text-danger fw-bold">
                        @if ($groupManager->id != $eventContact->id)
                            {{__('front/groups.connected_as_group_manager')}} {{ $account->names() }}
                        @endif
                    </div>
                    <div class="col-sm-4 text-end">
                        <a
                            x-data="{clicked: false}"
                            @click="clicked=true;"
                            href="{{route('front.event.switch-back-and-go-to-group-buy', $event)}}"
                            class="btn btn-sm btn-primary ms-md-auto mt-md-1">
                            {{ __('front/groups.return_to_group_dashboard') }}
                            <div
                                x-cloak
                                x-show="clicked"
                                class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">{{ __('front/ui.loading') }}</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        @endif

        @if ($eventContactAccessor->isPecAuthorized())
            {{-- pec enabled, pec eligible, deposit status ok --}}
            <div class="pec-banner bg-success text-white p-2 fs-6 rounded">
                Vous profitez d'une subvention de l'industrie
            </div>

        @else

            @if($eventContact->pec_enabled && !$eventContact->is_pec_eligible)

                <div class="pec-banner bg-danger text-white py-3 px-4 fs-6 rounded">
                    Vous ne bénéficiez actuellement plus de la prise en charge.
                    <br>
                    Veuillez contacter <a class="text-white text-decoration-underline"
                                          href="mailto:{{ $adminSubEmail }}">{{ $adminSubEmail }}</a>
                    pour plus d'information.
                </div>
            @endif

            @if ($eventContact->is_pec_eligible)

                @if ($eventContactAccessor->isExemptGrantFromDeposit())
                    @php
                        $eventContact->pec_enabled = true;
                        $eventContact->save();
                    @endphp
                    <script>
                        window.location.reload();
                    </script>
                @endif

                @if (!$eventContactAccessor->hasPaidGrantDeposit())
                    @php
                        $pendindDeposit = $eventContactAccessor->getPayableGrantDeposit();
                    @endphp

                    @if ($pendindDeposit)

                        <div
                            class="pec-banner bg-success text-white py-2 px-4 fs-6 rounded d-flex justify-content-between align-items-center">
                            Vous êtes éligible à une prise en charge financière par une subvention de
                            l'industrie pour cet événement.
                            <a href="#"
                               class="m-0 btn btn-sm btn-primary"
                               data-bs-toggle="modal"
                               data-bs-target="#pec-activate-modal">Je souhaite en profiter</a>
                        </div>

                        @include('layouts.modals.pec-activate-modal')
                    @else
                        <div class="pec-banner bg-danger text-white py-3 px-4 fs-6 rounded">
                            Vous ne bénéficiez pas actuellement de la prise en charge.
                            <br>
                            Veuillez contacter <a class="text-white text-decoration-underline"
                                                  href="mailto:{{ $adminSubEmail }}">{{ $adminSubEmail }}</a>
                            pour plus d'information.
                        </div>
                    @endif
                @endif
            @else

            @endif
        @endif

        <section class="py-0 pb-3 pt-xl-2">
            <div class="row align-items-center">
                <div class="col-12 col-xl-5">
                    <x-front.event-banner :event="$event"/>
                </div>
                <div class="col-12 col-xl-7">
                    <div class="row align-items-md-start">
                        <div class="col-6 d-flex gap-2">
                            <div class="avatar">
                                <img class="avatar-img rounded-circle border border-white border-3 shadow"
                                     src="{{$avatarSrc}}"
                                     alt="">
                            </div>
                            <div>
                                <h1 class="my-1 fs-4">{{ $eventContact->account->names() }}</h1>
                                @if($participationType)
                                    <span>
                                            <b class="text-primary-emphasis">{{$eventContact->participationType?->name??"Participant"}}</b>
                                        </span>
                                @endif
                            </div>
                        </div>
                        <div
                            class="col-6 d-flex flex-column flex-md-row gap-3 align-items-end align-items-md-start justify-content-md-end flex-wrap">

                            @unless($enableOrderBtn && $eventContact && $participationType)
                                @include('front.shared.logout')
                            @endif
                        </div>

                    </div>
                </div>

            </div>
        </section>
    </div>


    <x-front.session-notifs prefix="user."/>

    @if(!$groupView)
        <div class="container mt-5">
            <div class="row">
                <div class="col-12 col-xl-3 d-flex justify-content-between align-items-center">
                    <a class="h6 mb-0 fw-bold d-xl-none"
                       href="#">{{__('front/ui.menu')}}</a>
                    <button class="btn btn-primary d-xl-none"
                            type="button"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasSidebar"
                            aria-controls="offcanvasSidebar">
                        <i class="bi bi-list"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($isConnectedAsManager && $groupManager)
        @php
            $intent = FrontCache::getGroupManagerParams('intent');
        @endphp

        @if($intent == 'general-info')
            <div class="alert alert-danger">
                {!! __('front/account.modifying_account_by_manager_alert', ['account' => $account->names()]) !!}
            </div>
        @endif
    @endif

    <section class="pt-0">
        <div class="container">
            <div class="row">
                @if(!$participationType)
                    {{ $slot }}
                @else
                    @if(!$groupView)
                        @include('front.user.inc.left-sidebar')
                        <div class="col-xl-9">
                            {{ $slot }}
                        </div>
                    @else
                        {{ $slot }}
                    @endif
                @endif


            </div>
        </div>
    </section>

    @yield("after_content")
    @include('front.shared.confirm_modal')
    @include('front.shared.modal.ajax-notif-modal')
    @push("common_scripts")

        <script src="{{asset('js/interact.js')}}"></script>
        <script src="{{asset('js/ModalHelper.js')}}"></script>
        <script src="{{asset('js/ItemContainer.js')}}"></script>
        <script src="{{asset('js/HtmlItemContainer.js')}}"></script>
        <script defer
                src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js"></script>

    @endpush
</x-front-layout>
