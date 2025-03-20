@php
    use App\Helpers\PhoneHelper;
@endphp
<div class="tab-pane fade show active"
     id="identity-tabpane"
     role="tabpanel"
     aria-labelledby="identity-tabpane-tab">
    <div class="row mb-4 pt-4">
        <div class="col-lg-6 pe-md-5">
            <h4>Identité</h4>
            <div class="row">
                <div class="col-lg-12 mb-3">
                    <x-mfw::radio name="profile[civ]"
                                  :values="App\Enum\Civility::toArray()"
                                  default="M"
                                  :affected="$errors->any() ? old('profile.civ') : $account->profile?->civ"/>
                </div>
                <div class="col-xl-6 mb-3">
                    <x-mfw::input name="user[first_name]"
                                  required="true"
                                  :label="__('account.first_name')"
                                  :value="$error ? old('user.first_name') : $account->first_name"/>
                </div>
                <div class="col-xl-6 mb-3">
                    <x-mfw::input name="user[last_name]"
                                  required="true"
                                  :label="__('account.last_name')"
                                  :value="$error ? old('user.last_name') : $account->last_name"/>
                </div>
                @include('accounts.form.profile')
                <div class="col-lg-12 mb-3">
                    <div class="mfw-line-separator my-4"></div>
                    <div class="row">
                        <div class="col-12">
                            <h4>Adresses e-mail</h4>
                            <div class="d-flex justify-content-between align-items-center ">
                                <div class="w-100 me-3 mb-2">
                                    <x-mfw::input required="true"
                                                  name="user[email]"
                                                  type="email"
                                                  label="Adresse e-mail principale / de connexion"
                                                  :value="$error ? old('user.email') : $account->email"/>
                                </div>
                                @if($account->id)
                                    <a href="#"
                                       data-bs-toggle="modal"
                                       data-bs-target="#mfwDynamicModal"
                                       data-modal-content-url="{{ route('panel.modal', ['requested' => 'createAccountEmail', 'account_id' => $account->id]) }}"
                                       class="fs-4 add-dynamic mt-4"><i class="fa-solid fa-circle-plus"></i></a>
                                @endif
                            </div>
                            <a class="text-primary link-offset-3"
                               href="mailto:{{ $account->email }}">{{ $account->email }}</a><br>
                            @if ($account->mails->isNotEmpty())
                                <b class="d-block mt-3 mb-1">Adresses e-mail complémentaires</b>
                                <div id="complementary_mails">
                                    @foreach($account->mails as $mail)
                                        <a class="text-primary link-offset-3"
                                           href="mailto:{{ $mail->email }}">{{ $mail->email }}</a>
                                        <br>
                                    @endforeach
                                </div>
                            @endif
                            @push('callbacks')
                                <script>
                                    function addEmailToAccountUI(result) {
                                        if (!result.hasOwnProperty('error')) {
                                            $('#complementary_mails').append(
                                                '<a class="text-primary link-offset-3" href="mailto:' + result.email + '">' + result.email + '</a><br>',
                                            );
                                        }
                                    }
                                </script>
                            @endpush
                        </div>
                        @php
                            $phone = PhoneHelper::getDefaultPhoneNumberByAccount($account);
                        @endphp

                        <div class="col-12 mt-4">
                            <h4>Numéros de téléphone</h4>
                            <x-back.phone-row namespace="phone"
                                              :phone="$phone"
                                              label="Téléphone principal"/>

                            @php
                                $other_phones = $account->phones->reject(fn($item) => $item->default);
                            @endphp
                            @if ($other_phones->isNotEmpty())
                                <b class="d-block mt-2">Autres numéros</b>
                                @foreach($other_phones as $item)
                                    <div class="text-dark my-1">{{  $item->phone }}
                                        / {{ $item->name ?: 'Sans titre' }}</div>
                                @endforeach
                            @endif

                        </div>

                    </div>
                    <div class="mfw-line-separator mt-5 mb-3"></div>
                </div>
                <div>
                    <h4>Photo</h4>
                    <div class="row">
                        <div class="col-lg-12 mb-3">
                            <x-media-library-attachment
                                collection="photo"
                                name="photo"
                            />
                        </div>
                    </div>
                    @if($account->getFirstMediaUrl('photo'))
                        <div class="row">
                            <div class="col-lg-12 mb-3">
                                <img src="{{ $account->getFirstMediaUrl('photo') }}"
                                     alt="Photo de profil"
                                     width="150">
                            </div>
                        </div>

                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            @include('accounts.form.more')
            @include('custom_fields.custom_fields_bloc', ['customFormBindedModel' => $account])
        </div>
        <div class="col-12">
            <x-mfw::textarea name="profile[notes]"
                             label="Notes"
                             :value="$error ? old('profile.notes') : $account->profile?->notes"/>
        </div>
    </div>
</div>
