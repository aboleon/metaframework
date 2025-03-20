@props([
    'routePrefix' => '',
    'itemName' => null,
    'model' => null,
    'createRoute' => null,
    'useCreateRoute' => true,
    'showIndexRoute' => true,
    'showSaveButton' => true,
    'indexRoute' => null,
    'deleteRoute' => null,
    'showDeleteBtn' => true,
    'deleteBtnText' => "Supprimer",
    'wrap' => true,
    'event' => null,
])

@if ($wrap)
    <div class="d-flex align-items-center gap-1" id="topbar-actions" x-data>
        @endif

        @if($event)
            <x-back.topbar.edit-event-btn :event="$event" />
        @endif

        @if($showIndexRoute)
            @php
                $indexRoute = $indexRoute ?? route($routePrefix . '.index');
            @endphp
            <x-back.topbar.index-btn :route="$indexRoute" />
        @endif

        @if($useCreateRoute)
            @php
                $createRoute = $createRoute ?? route($routePrefix . '.create');
            @endphp
            <x-back.topbar.new-btn :route="$createRoute" />
        @endif

        @if($showDeleteBtn)
            @if ($model?->id)
                @php

                    $itemNameText = is_callable($itemName) ? $itemName($model) : $itemName;
                    $showDelete = true;

                    // by default, don't show delete button if the model uses soft deletes and is already deleted
                    if(in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($model))){
                        $showDelete = !$model->trashed();
                    }
                    $deleteRoute = $deleteRoute ?? route($routePrefix . '.destroy', $model);
                @endphp

                @if($showDelete)
                    <x-back.topbar.delete-btn
                            :id="$model->id"
                            :route="$deleteRoute"
                            :item-name="$itemNameText"
                            :delete-btn-text="$deleteBtnText"
                    />
                @endif
            @endif
        @endif

        @if($showSaveButton)
            <x-save-btns :saveexit="true" />
            <x-back.topbar.separator />
        @endif


        @if ($wrap)
    </div>
@endif
