@if(\MetaFramework\Accessors\Locale::multilang())
    <ul id="{{ $id }}_tabs" class="nav nav-tabs admintabs" role="tablist">
        @foreach(config('mfw.translatable.locales') as $locale)
            <li class="nav-item " role="presentation">
                <button class="nav-link {!! $locale == app()->getLocale() ? 'active': null !!}"
                        id="{{ $id }}_btn_{{ $locale }}"
                        data-bs-toggle="tab"
                        data-bs-target="#{{ $id }}_{{ $locale }}"
                        type="button" role="tab"
                        aria-controls="{{ $id }}_{{ $locale }}"
                        aria-selected="true">
                    <img src="{!! asset('vendor/flags/4x3/'.$locale.'.svg') !!}" alt="{{ trans('mfw-lang.'.$locale.'.label') }}" class="d-inline-block"/>
                    {!! trans('mfw-lang.'.$locale.'.label') !!}
                </button>
            </li>
        @endforeach
    </ul>
@endif