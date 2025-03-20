@php
    $error = $errors->any();
@endphp
<h4>Affectations</h4>
<div class="row mt-5 form" data-ajax="{{ route('ajax') }}">
    <div class="col-xl-6 mb-3 mt-3">
        <x-mfw::select label="Type de client *"
                       name="profile[account_type]"
                       :values="array_merge(['' => '--- Choisissez ---'], \App\Enum\ClientType::translations())"
                       :affected="$error ? old('profile.account_type') : ($account?->profile?->account_type ?? request()->query('account_type'))"
                       :nullable="false"/>
    </div>


    <div class="col-xl-6 mb-3 mt-3 {{ $accessor->isCompany() ? '' : 'invisible'  }}"
         id="account_type_is_company">
        <x-mfw::input name="profile[company_name]"
                      label="Société"
                      :value="$error ? old('profile.company_name') : ($account?->profile?->company_name ?? '')"/>
    </div>

    <div class="col-xl-6 mb-3 mt-3 mfw-holder position-relative">
        <div class="d-flex justify-content-between align-items-end">
            <div class="w-100 me-3">
                <x-selectable-dictionnary required="true"
                                          key="base"
                                          name="profile[base_id]"
                                          :affected="$error ? old('profile.base_id') : $account?->profile?->base_id"/>
            </div>
            <span class="fs-4 add-dynamic dict-dynamic"
                  data-dict="base"><i class="fa-solid fa-circle-plus"></i></span>
        </div>
    </div>
    <div class="col-xl-6 mb-3 mt-3 mfw-holder position-relative">
        <div class="d-flex justify-content-between align-items-end">
            <div class="w-100 me-3">
                <x-selectable-dictionnary required="true"
                                          key="domain"
                                          name="profile[domain_id]"
                                          :affected="$error ? old('profile.domain_id') : $account?->profile?->domain_id"/>
            </div>
            <span class="fs-4 add-dynamic dict-dynamic"
                  data-dict="domain"><i class="fa-solid fa-circle-plus"></i></span>
        </div>
    </div>
    <div class="col-xl-6 mb-3 mt-3 mfw-holder position-relative {{ $accessor->isCompany() ? 'd-none': '' }}">
        <div class="d-flex justify-content-between align-items-end">
            <div class="w-100 me-3">
                <x-selectable-dictionnary key="titles"
                                          name="profile[title_id]"
                                          :affected="$error ? old('profile.title_id') : $account?->profile?->title_id"/>
            </div>
            <span class="fs-4 add-dynamic dict-dynamic"
                  data-dict="titles"><i class="fa-solid fa-circle-plus"></i></span>
        </div>
    </div>
    <div class="col-xl-6 mb-3">
        <x-mfw::input name="profile[function]"
                      :label="__('forms.fields.function')"
                      :value="$error ? old('profile.function') : $account?->profile?->function"/>
    </div>
    <div class="col-xl-6 mb-3 mfw-holder position-relative">
        <div class="d-flex justify-content-between align-items-end">
            <div class="w-100 me-3">
                <x-selectable-dictionnary key="professions"
                                          :required="true"
                                          name="profile[profession_id]"
                                          :affected="$error ? old('profile.profession_id') : $account?->profile?->profession_id"/>
            </div>
            <a href="#"
               data-bs-toggle="modal"
               data-bs-target="#mfwDynamicModal"
               data-modal-content-url="{{ route('panel.modal', ['requested' => 'createProfession']) }}"
               class="fs-4 add-dynamic"><i class="fa-solid fa-circle-plus"></i></a>
        </div>
    </div>
    <div class="col-xl-6 mb-3 mfw-holder position-relative">
        <div class="d-flex justify-content-between align-items-end">
            <div class="w-100 me-3">
                <x-selectable-dictionnary key="language"
                                          name="profile[language_id]"
                                          :affected="$error ? old('profile.language_id') : $account?->profile?->language_id"/>
            </div>
            <span class="fs-4 add-dynamic dict-dynamic"
                  data-dict="language"><i class="fa-solid fa-circle-plus"></i></span>
        </div>
    </div>
    <div class="col-xl-6 mb-3 {{ $accessor->isMedical() ? '' : 'd-none' }}"
         id="rpps-access">
        <x-mfw::input name="profile[rpps]"
                      label="RPPS"
                      :value="$error ? old('profile.rpps') : ($account?->profile?->rpps ?? '')"/>
        <small class="text-danger d-block pt-1">{!! __('front/account.labels.rpps_notice') !!}</small>
    </div>
    <div class="col-12 mt-3 {{ $accessor->isCompany() ? 'd-none' : '' }}"
         id="account_type_is_not_company">
        <div class="row">
            <div class="col-xl-6 mb-3 mfw-holder position-relative">
                <div class="d-flex justify-content-between align-items-end">
                    <div class="w-100 me-3">
                        <x-selectable-dictionnary :alphaSort="true"
                                                  key="savant_societies"
                                                  name="profile[savant_society_id]"
                                                  :affected="$error ? old('profile.savant_society_id') : $account?->profile?->savant_society_id"/>
                    </div>
                    <span class="fs-4 add-dynamic dict-dynamic" data-dict="savant_societies"><i
                            class="fa-solid fa-circle-plus"></i></span>
                </div>
            </div>
            <div class="col-xl-6 mb-3 {{ is_null($account?->profile?->savant_society_id) ? 'd-none' : '' }}"
                 id="profile_cotisation_year_container">
                <x-mfw::select :values="\App\Accessors\Chronos::createYearRangeFromNowToPast(10)"
                               name="profile[cotisation_year]"
                               :affected="$error ? old('profile.cotisation_year') : $account?->profile?->cotisation_year"
                               label="A jour des cotisation de l'année"/>
            </div>
            <div class="col-xl-6 mb-3">
                <div class="d-flex justify-content-between align-items-end">
                    <div class="w-100 me-3 wa-select2">
                        <x-mfw::select name="profile.establishment_id"
                                       label="Établissement"
                                       :values="$establishments"
                                       :affected="old('profile.establishment_id', $account->profile?->establishment_id)"/>
                    </div>
                    <a href="#"
                       data-bs-toggle="modal"
                       data-bs-target="#mfwDynamicModal"
                       data-modal-content-url="{{ route('panel.modal', ['requested' => 'createEstablishment']) }}"
                       class="fs-4 add-dynamic"><i class="fa-solid fa-circle-plus"></i>
                    </a>
                </div>
                <x-mfw::validation-error field="profile.establishment_id"/>
            </div>
        </div>
    </div>
</div>

@include('lib.select2')
@push('js')
    <script>
        $(function () {
            $('.wa-select2 select.form-control').select2();
        });
    </script>
@endpush

@once
    @include('accounts.shared.dict_template')
@endonce
