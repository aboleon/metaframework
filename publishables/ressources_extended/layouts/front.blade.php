@php
    use App\Accessors\EventAccessor;
@endphp
    <!doctype html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="ajax-route" content="{{ route("ajax") }}">
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link rel="profile" href="https://gmpg.org/xfn/11"/>
    @yield('meta')


    {!! SEO::generate(true) !!}


    <meta name='robots' content='max-image-preview:large'/>
    <meta name="googlebot" content="noodp"/>
    <meta name="robots" content="index, follow"/>
    <link rel='dns-prefetch' href='//s.w.org'/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;700&family=Roboto:wght@400;500;700&display=swap">


    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
          crossorigin="anonymous"
          referrerpolicy="no-referrer"/>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">


    <link rel="stylesheet" type="text/css" href="{{asset("front/bstheme/css/style.css")}}">
    {!! csscrush_tag(public_path('front/bstheme/css/theme.css')) !!}
    <link href="{{asset("vendor/spatie/media-library-pro/styles.css")}}"
          rel="stylesheet"
          type="text/css"/>


    <meta name="theme-color" content="#ffffff">
    <meta name="ajax-route" content="{{route('ajax')}}"/>

    @mediaLibraryStyles

    <style>
        body {
            /* outer background*/
            background: white !important;
        }

        body, a, h1, h2, h3, h4, h5, h6 {
            /*font-family: arial !important;*/
        }

        html {
            font-size: 16px;
        }

        :root {
            /* inner background*/
            --bs-body-bg: white;
        }

        .bg-color-1 {
            /**
            * top banner: /resources/views/front/shared/header.blade.php
            */
            background: #066ac9;
            color: #a8bfd9;
        }

        .main-navbar .nav-link {
            color: #747579;
        }

        .main-navbar .nav-link.active,
        .main-navbar .nav-link:hover {
            color: #066ac9;
        }

        .btn-primary {
            --bs-btn-color: #f8f9fa;
            --bs-btn-bg: #24292d;
            --bs-btn-border-color: #24292d;
            --bs-btn-hover-color: #f8f9fa;
            --bs-btn-hover-bg: #212529;
            --bs-btn-hover-border-color: #1c1f23;
            --bs-btn-focus-shadow-rgb: 33, 37, 41;
            --bs-btn-active-color: #f8f9fa;
            --bs-btn-active-bg: #1c1f23;
            --bs-btn-active-border-color: #181a1e;
            --bs-btn-active-shadow: none;
            --bs-btn-disabled-color: #f8f9fa;
            --bs-btn-disabled-bg: #24292d;
            --bs-btn-disabled-border-color: #24292d;
        }

        .btn-outline-primary {
            --bs-btn-color: #24292d;
            --bs-btn-border-color: #24292d;
            --bs-btn-hover-color: #fff;
            --bs-btn-hover-bg: #24292d;
            --bs-btn-hover-border-color: #24292d;
            --bs-btn-focus-shadow-rgb: 0, 0, 0;
            --bs-btn-active-color: #fff;
            --bs-btn-active-bg: #333;
            --bs-btn-active-border-color: #333;
            --bs-btn-active-shadow: none;
            --bs-btn-disabled-color: #d9d9d9;
            --bs-btn-disabled-bg: transparent;
            --bs-btn-disabled-border-color: #24292d;
            --bs-gradient: none;
        }


        .btn-success {
            --bs-btn-color: #fff;
            --bs-btn-bg: #0cbc87;
            --bs-btn-border-color: #0cbc87;
            --bs-btn-hover-color: #fff;
            --bs-btn-hover-bg: #0aa073;
            --bs-btn-hover-border-color: #0a966c;
            --bs-btn-focus-shadow-rgb: 48, 198, 153;
            --bs-btn-active-color: #fff;
            --bs-btn-active-bg: #0a966c;
            --bs-btn-active-border-color: #098d65;
            --bs-btn-active-shadow: none;
            --bs-btn-disabled-color: #fff;
            --bs-btn-disabled-bg: #0cbc87;
            --bs-btn-disabled-border-color: #0cbc87;
        }


        .bg-user-left-sidebar {
            background: #24292d !important;
            color: white;
        }


        @php
            if (isset($event) && $event):
                $frontConfig = $event->frontConfig;

                if($frontConfig?->menu_font){
                    echo <<<EEE
                      .divine-menu-font, .divine-menu-font a {
                        font-family: "$frontConfig->menu_font" !important;
                      }
                    EEE;
                }

                if($frontConfig?->general_font){
                    echo <<<EEE
                      #site, h1, h2, h3, h4, h5, h6, p, a{
                        font-family: "$frontConfig->general_font" !important;
                      }
                    EEE;
                }

                if($frontConfig?->main_color){
                    echo <<<EEE
                      .divine-main-color
                      {
                        background-color: $frontConfig->main_color !important;
                      }

                      .divine-main-color-border
                      {
                        border-color: $frontConfig->main_color !important;
                      }

                      .divine-main-color-text
                      {
                        color: $frontConfig->main_color !important;
                      }
                    EEE;
                }

                if($frontConfig?->secondary_color){
                    echo <<<EEE
                      .divine-secondary-color
                      {
                        background-color: $frontConfig->secondary_color !important;
                      }

                      .divine-secondary-color-text
                      {
                        color: $frontConfig->secondary_color !important;
                      }


                    EEE;
                }

                if($frontConfig?->text_color){
                    echo <<<EEE
                      .divine-text-color
                      {
                        color: $frontConfig->text_color;
                      }
                    EEE;
                }
            endif;
        @endphp
    </style>

    @livewireStyles
    @stack('css')
</head>


@php
    URL::defaults(['locale' => app()->getLocale()]);
    $user = Auth::user();
@endphp

<body data-event-id="{{ isset($event) && $event instanceof \App\Models\Event ? $event->id : '' }}"
      class="body @yield('class_body')">
{!! App\Accessors\Cached::settings('code_ga') !!}

<x-livewire-modal/>


<div id="site" class="container-xl d-flex flex-column">
    @include ('front.shared.header')


    <main class="mt-3 @yield('class_main')">
        @if (isset($slot))
            {{ $slot }}
        @else
            @yield('slot')
        @endif
    </main>
    @include ('front.shared.footer')
</div>

@include('shared.simple-modal')
@stack('modals')


<script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

<script src="{!! asset('vendor/mfw/js/common.js') !!}"></script>
<script src="{{asset('js/simple-modal.js')}}"></script>

@livewireScripts
@stack("callbacks")
@stack("common_scripts")
@stack('js')
@stack('js_last')
</body>
</html>
