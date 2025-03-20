<nav class="d-flex justify-content-between" id="event-nav-tab">
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <x-mfw::tab tag="order-tabpane" label="Commande" :active="true" />
@if ($orderAccessor->isOrder() && $orderAccessor->hasItems() && !$orderAccessor->isOrator())
    <x-mfw::tab tag="payments-tabpane" label="Paiements" />
    <x-mfw::tab tag="refunds-tabpane" label="Avoirs" />
@endif
<x-mfw::tab tag="notes-tabpane" label="Notes" />
</div>
</nav>
