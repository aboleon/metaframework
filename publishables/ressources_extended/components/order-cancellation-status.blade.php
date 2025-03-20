@if ($eventContact['order_cancellation'])
    <x-mfw::alert message="Annulation globale de la commande demandée depuis le dashboard participant"/>
@endif
@if ($order->cancelled_at)
    <x-mfw::alert message="Commande annulée le {{ $order->cancelled_at->format('d/m/Y à H:i') }}"/>
@endif

@if ($order->cancellation_request && !$order->cancelled_at)
    <x-mfw::alert message="Demande d'annulation de la commande faite le {{ $order->cancellation_request->format('d/m/Y à H:i') }}"/>
@endif

@if (!$order->cancellation_request && !$order->cancelled_at)
    @if($order->services->filter(fn($item) => !is_null($item->cancellation_request) && is_null($item->cancelled_at))->count())
        <x-mfw::alert message="Des annulations sur le panier des prestations ont été demandées."/>
    @endif
    @if($order->accommodation->filter(fn($item) => !is_null($item->cancellation_request) && is_null($item->cancelled_at))->count())
        <x-mfw::alert message="Des annulations sur le panier hébergement ont été demandées."/>
    @endif
@endif

