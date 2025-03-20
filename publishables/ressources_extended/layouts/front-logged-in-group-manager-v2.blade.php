@php
    use App\Accessors\EventAccessor;
    use App\Accessors\Pec;
    use App\Helpers\AuthHelper;
    use App\Accessors\Front\FrontCartAccessor;
@endphp

<x-front-layout :is-group-manager="true">
    @php
        $eventName = $event->texts->name;
    @endphp

    @pushonce("css")
        {!! csscrush_tag(public_path('front/bstheme/css/user.css')) !!}
    @endpushonce
    <div class="dashboard mt-4">
        <section class="py-0 pb-3 pt-xl-2">
            <div class="row">
                <div class="col-12 col-xl-5">
                    <x-front.event-banner :event="$event"/>
                </div>
                <div class="col-12 col-xl-7">
                    <div class="card bg-transparent card-body pb-0 pt-2 pt-xl-0 mt-sm-0 container">
                        <div class="row align-items-md-start">
                            <div class="col-6 d-flex gap-2">
                                <div>
                                    <h1 class="my-1 fs-4">{{$eventGroup->group->name}}
                                        <x-front.debugmark
                                            title="g={{$eventGroup->group->id}}; eg={{$eventGroup->id}}; ec={{$eventContact->id}}; u={{$eventContact->user->id}}"
                                        />
                                    </h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <x-front.session-notifs prefix="user."/>

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
        </section>

        <section class="pt-0">
            <div class="container">
                <div class="row">
                    @include('front.user.inc.group-left-sidebar')
                    <div class="col-xl-9">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </section>

        @yield("after_content")


        @include('front.shared.confirm_modal')
        @include('front.shared.modal.ajax-notif-modal')


    </div>
    @push("common_scripts")

        <script src="{{asset('js/interact.js')}}"></script>
        <script src="{{asset('js/ModalHelper.js')}}"></script>
        <script src="{{asset('js/ItemContainer.js')}}"></script>
        <script src="{{asset('js/HtmlItemContainer.js')}}"></script>
        <script defer
                src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js"></script>

    @endpush
</x-front-layout>
