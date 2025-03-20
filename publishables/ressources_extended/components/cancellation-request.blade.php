@if ($cart->cancellation_request)
    <div class="cancellation_requests mt-2">
        @forelse ($cart->cancellations as $cancelled)
            <span class="d-block text-dark fw-bold"
                  style="font-size: 12px;">{{ $cancelled->requested_at->format('d/m/Y à H:i') }} - demande d'annulation de {{ $cancelled->quantity }} ch.</span>
        @empty
            <span class="d-block text-danger fw-bold"
                  style="font-size: 12px;">{{ $cart->cancellation_request->format('d/m/Y à H:i') }} - demande d'annulation {{ $cart->cancelled_qty ? ' de ' . $cart->cancelled_qty .' ch.' : ''}} </span>
        @endforelse
    </div>
@endif
