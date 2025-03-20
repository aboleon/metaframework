<table id="accompanying" class="table {{ $order->accompanying->isNotEmpty() ? '' : 'd-none'}}">
    <caption>Accompagnants</caption>
    <tbody>
    @if($order->accompanying->isNotEmpty())
        @foreach($order->accompanying as $item)
            <x-accompanying-row :model="$item"/>
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

<div id="accompanying_toggler" class="invoiced {{ $invoiced ? 'd-none' : '' }}">
    <x-mfw::checkbox name="add_accompanying" value="1" label="Ajouter des accompagnants" :switch="true"
                     :affected="$order->accompanying->isNotEmpty()"/>
</div>
<template id="accompanying-template">
    <x-accompanying-row :model="new \App\Models\Order\Accompanying()"/>
</template>
