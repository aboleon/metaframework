@php
    $positions = (array_key_exists('positions',$settings) && $settings['positions'] === true);
@endphp
<div class="mediaclass-uploadable {{ $size }}"
     data-maxfilesize="{{ $maxfilesize }}"
     data-limit="{{ $limit }}"
     data-model="{{ get_class($model) }}"
     data-model-id="{{ $model->id }}"
     data-positions="{{ $positions }}"
     data-group="{{ $group }}"
     data-subgroup="{{ $settings['subgroup'] ?? false }}"
     data-has-description="{{ $description }}"
     data-cropable="{{ $cropable }}"
>
    <div class="controls d-flex justify-between align-items-center" style="background: #EFEFEF">
        <span class="subcontrol mediaclass-uploader"><i class="{{ $icon }}"></i> {{ $label }}</span>
        <span class="subcontrol"
              style="font-size: 14px;font-weight: 700">{{ array_key_exists('sizes', $settings) ? current($settings['sizes']).' x '. end($settings['sizes']): '' }}</span>
    </div>
    <div class="mediaclass-upload-container"></div>
    <div class="uploaded">
        <x-mediaclass::stored :cropable="$cropable"
                              :positions="$positions"
                              :model="$model"
                              :nomedia="$nomedia"
                              :group="$group"
                              :subgroup="$settings['subgroup'] ?? null "
                              :description="$description"/>
    </div>
</div>
@once
    @if ($model instanceof \MetaFramework\Mediaclass\Interfaces\MediaclassInterface && !isset($model->id))
        <input type="hidden" name="mediaclass_temp_id" value="{{ Str::random(32) }}">
    @endif
    @include('mediaclass::fileupload_scripts')
    <x-mediaclass::template/>
    <x-mediaclass::crop-template/>
@endonce


@once
    <x-mediaclass::crop-modal/>
@endonce


