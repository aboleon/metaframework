@php
    $account_roles = $account->id ? $account->roles : [['role_id' => $role['id']]];
    $error = $errors->any();
@endphp

@forelse($account_roles as $account_role)

    @php
        $filtered_role = array_filter($roles, fn($item) => $item['id'] == $account_role['role_id']);
        $subclass = $account->userSubData((string)(key($filtered_role)));
    @endphp

    @if($subclass)
        <fieldset>
            <input type="hidden" name="has_account_profile" value="1" />
            <legend>Informations complémentaires {{ $account->roles->count() > 0 ? current($filtered_role)['label'] : '' }}</legend>
            <div class="row gx-5">
                @if ($subclass->profileData())
                    <div class="col-lg-6">
                        @foreach($subclass->profileData() as $key => $value)
                            @switch($value['type'])
                                @case('textarea')
                                @case('textarea_extended')
                                    <div class="col-12 mb-4">
                                        <x-mfw::textarea name="profile[{{$key}}]"
                                                    :className="$value['type'] .' '.($value['class']??'') "
                                                    value="{!! $error ? old('profile.'.$key) : (isset($account->profile->{$key}) ? $account->profile->{$key} : '') !!}" label="{{$value['label']}}"/>
                                    </div>
                                    @break
                                @default

                                    <div class="{{ $value['class'] ?? 'col-12' }} mb-4">
                                        <x-mfw::input name="profile[{{$key}}]" :required="$value['required']" value="{{  $error ? old('profile.'.$key) : (isset($account->profile->{$key}) ? $account->profile->{$key} : '') }}" label="{{$value['label']}}"/>
                                    </div>

                            @endswitch
                        @endforeach
                    </div>
                @endif
                <div class="col-lg-6">
                    <h4>Photo</h4>

                    <x-media-library-attachment
                            name="profile_photo"
                    />
                    @if($account->profile?->getFirstMediaUrl('profile_photo'))
                        <div class="row mt-2">
                            <div class="col-lg-12 mb-3">
                                <img src="{{ $account->profile->getFirstMediaUrl('profile_photo') }}" alt="Photo de profil"
                                     width="150">
                            </div>
                        </div>

                    @endif
{{--                    @if ($subclass->mediaSettings())--}}
{{--                        @foreach($subclass->mediaSettings() as $media)--}}
{{--                            <x-mediaclass::uploadable :model="$account" :settings="$media" :description="false" limit="1"/>--}}
{{--                        @endforeach--}}
{{--                    @endif--}}
                </div>
            </div>
        </fieldset>

    @endif
@empty

@endforelse
