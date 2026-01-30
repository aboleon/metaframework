@php
    $locales = \MetaFramework\Accessors\Locale::localesAsSelectable();
    $localeKeys = array_keys($locales);
@endphp
<div class="mfw-translatable-tabs"
     data-mfw-translatable-tabs="{{ $id }}"
     data-locales='@json($localeKeys)'
     data-selected-locale="{{ $selectedlocale }}"
     data-ajax="{{ route('mfw.ajax') }}"
     data-translate-target-required="{{ __('mfw.translate_target_required') }}">
    <x-mfw::language-tabs id="{{ $id }}" :selectedlocale="$selectedlocale"/>
    <div class="tab-content pt-4">
        @foreach(config('mfw.translatable.locales') as $locale)
            <div class="tab-pane fade{!! $selectedlocale == $locale ? ' show active' : null !!}"
                 id="{{ $id }}_{{ $locale }}"
                 role="tabpanel"
                 aria-labelledby="{{ $id }}_btn_{{ $locale }}">
                <div class="row mb-4">
                    <x-mfw::fillable-parser :datakey="$datakey"
                                            :fillables="$fillables"
                                            :model="$model"
                                            :locale="$locale"
                                            :fallbacklocale="$fallbacklocale"
                                            :disabled="$disabled"
                                            :parsed="$pluck"
                    />
                </div>
            </div>
        @endforeach

        @if ($translatable)
            <div class="mfw-translatable-tools mt-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <x-mfw-inputable::radio
                        :label="__('mfw.translate_from')"
                        name="mfw_translate_from.{{ $id }}"
                        :values="$locales"
                        :affected="$selectedlocale"
                        :params="['data-mfw-translate-from' => '1']"
                    />
                </div>
                <div class="col-md-5">
                    <label class="form-label d-block">{{ __('mfw.translate_to') }}</label>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($locales as $locale => $label)
                            <x-mfw-inputable::checkbox
                                name="mfw_translate_to.{{ $id }}.{{ $locale }}"
                                :value="$locale"
                                :label="$label"
                                :params="[
                                    'data-mfw-translate-target' => '1',
                                    'data-locale' => $locale,
                                ]"
                            />
                        @endforeach
                    </div>
                </div>
                <div class="col-md-3 text-md-end">
                    <button type="button" class="btn btn-outline-secondary mfw-translate-btn" data-mfw-translate-button="1">
                        <span class="d-inline-flex align-items-center gap-2">
                            <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path fill="currentColor" d="M4 3h8a2 2 0 0 1 2 2v6h-2V5H4v14h6v2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/>
                                <path fill="currentColor" d="M20 8h-5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h5a2 2 0 0 0 2-2V10a2 2 0 0 0-2-2zm0 12h-5V10h5v10z"/>
                                <path fill="currentColor" d="M7 8h4v2H7V8zm0 4h4v2H7v-2zm0 4h3v2H7v-2z"/>
                            </svg>
                            <span>{{ __('mfw.translate_action') }}</span>
                            <img src="{!! asset('vendor/mfw/components/deepl.svg') !!}" alt="{{ __('mfw.translate_service') }}" height="30" style="width:auto;"/>
                            <span class="mfw-translate-spinner ms-2" style="display:none;">
                                <i class="core spinner fa fa-cog fa-spin fa-fw"></i>
                            </span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@if ($translatable)
    @pushonce('css')
        <style>
            .mfw-translate-btn:hover,
            .mfw-translate-btn:focus-visible {
                background-color: #e9e9e9;
                color: #042b48;
                border-color: #e9e9e9;
            }
        </style>
    @endpushonce
    @pushonce('js')
        <script src="{!! asset('vendor/mfw/components/translatable-tabs.js') !!}"></script>
    @endpushonce
@endif
