<div class="cancelled fw-bold {{ $cart->cancelled_at ? '' : 'd-none' }}" style="font-size: 12px;">
    @if ($cart->cancellations && $cart->cancellations->isNotEmpty())
        @foreach ($cart->cancellations as $cancelled)
            <span class="d-block text-danger fw-bold"
                  style="font-size: 12px;">{{ $cancelled->cancelled_at->format('d/m/Y à H:i') }} - annulé {{ $cancelled->quantity }} ch.</span>
        @endforeach
    @else
    Annulée le <span class="cancelled_time">{{$cart->cancelled_at?->format("d/m/Y à H\hi")}}</span>
    @endif
</div>

