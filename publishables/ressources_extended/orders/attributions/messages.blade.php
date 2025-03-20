@php
    $locale = app()->getLocale();
    $attribution_messages = [
        'fr' => [
            'groupmax' => "Limite d'affectation atteinte.La quantité maximale d'attribution est de ",
            'minimal' => "La quantité minimale d'attribution est de 1.",
            'overflow' => "Vous ne pouvez pas attribuer plus de quantité de ce qu'il en reste.",
            'unsufficient' => "Vous ne pouvez pas attribuer cette quantité à autant de membres",
        ],
        'en' => [
            'groupmax' => "Assignment limit reached. The maximum assignment quantity is ",
            'minimal' => "The minimum assignment quantity is 1.",
            'overflow' => "You cannot assign more than the remaining quantity.",
            'unsufficient' => "You cannot assign this quantity to so many members.",
        ],
    ];
@endphp
<div id="attribution-messages" class="d-none">
    @foreach($attribution_messages[$locale] as $key => $message)
        <span class="{{ $key }}">{{ $attribution_messages[$locale][$key] }}</span>
    @endforeach
</div>
