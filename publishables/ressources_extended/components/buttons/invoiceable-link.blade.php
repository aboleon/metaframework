<a href="{{ route('pdf-printer', ['type' => $type, 'identifier' => $identifier]) }}"
   class="mfw-edit-link btn btn-sm {{ $btnClass }}"
   target="_blank"
   data-bs-toggle="tooltip"
   data-bs-placement="top"
   data-bs-title="{{ $title }}">
    <i class="fa-solid fa-{{ $icon }}"></i>
</a>
