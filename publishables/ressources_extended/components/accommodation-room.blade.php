<div class="row mb-3 room">
    <div class="col-md-7">
        <div class="d-flex justify-content-between align-items-end">
            <div class="w-100 me-3">
                <x-selectable-dictionnary key="type_chambres" name="type" :affected="$room->room_id"/>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <x-mfw::input type="number" :params="['min'=>1]" name="capacity" label="Nombre de personnes" :value="$room->capacity ?: 1"/>
        <input type="hidden" class="room_key" name="room_id" value="{{ $room->id }}">
    </div>
    <div class="col-md-1 d-flex align-items-end">
        <x-mfw::simple-modal id="delete_room"
                             class="btn btn-danger btn-sm mb-2"
                             title="Suppression d'une combinaison de chambres"
                             confirmclass="btn-danger"
                             confirm="Supprimer"
                             callback="ajaxDeleteRoom"
                             :modelid="$room->id"
                             text="<i class='fas fa-trash' style='font-size: smaller'></i></a>"/>

    </div>
</div>
