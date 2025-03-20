<div class="room-group pt-3 mb-3 mfw-line-separator" data-ajax="{{ route('ajax') }}">
    <div class="row">
        <div class="col-md-6 presentation pe-5">
            <x-mfw::translatable-tabs :id="$model->id ? 'room_group_'.$model->id: 'new-room-group'" :model="$model" :fillables="(new \App\Models\EventManager\Accommodation\RoomGroup())->fillables"/>
        </div>
        <div class="col-md-6 rooms">
            <div class="rows mb-3">
                @forelse($model->rooms as $room)
                    <x-accommodation-room :room="$room"/>
                @empty
                @endforelse
            </div>
            <button class="btn btn-sm btn-info add-room" type="button">
                <i class="fas fa-plus" style="font-size: smaller"></i> Ajouter une chambre
            </button>
        </div>
    </div>
    <input type="hidden" class="key" name="room_group_key[]" value="{{ $model->id ?: 'new-room-group' }}"/>
    <div style="margin: -30px 0 30px">

        <x-mfw::simple-modal id="delete_room_group"
                             class="btn btn-danger btn-sm delete-room-group"
                             title="Suppression d'un groupe de chambres"
                             confirmclass="btn-danger"
                             confirm="Supprimer"
                             callback="ajaxDeleteRoomGroup"
                             :modelid="$model->id"
                             text="<i class='fas fa-trash' style='font-size: smaller'></i> Supprimer la catégorie</a>"/>
    </div>
</div>
