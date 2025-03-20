<table id="roomnotes" class="table {{ $order->roomnotes->isNotEmpty() ? '' : 'd-none'}}">
    <caption>Commentaires sur les chambres</caption>
    <tbody>

    @if($order->roomnotes->isNotEmpty())
        @foreach($order->roomnotes as $item)
            <x-room-note-row :model="$item"/>
        @endforeach
    @endif
    </tbody>
    <tfoot>
    <tr>
        <td colspan="4" class="text-end">
            <button type="button" class="btn btn-sm btn-success">Ajouter</button>
        </td>
    </tr>
    </tfoot>
</table>

<div id="roomnotes_toggler" class="invoiced {{ $invoiced ? 'd-none' : '' }}">
    <x-mfw::checkbox name="add_roomnotes" value="1" label="Ajouter des commentaires sur les chambres" :switch="true"
                     :affected="$order->roomnotes->isNotEmpty()"/>
</div>
<template id="roomnotes-rooms"></template>
<template id="roomnotes-template">
    <x-room-note-row :model="new \App\Models\Order\RoomNote()"/>
</template>
