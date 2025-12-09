<li class="delete" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{{ $title }}" {!! $params_as_string !!}>
    <a href="#" class="btn btn-sm btn-danger"
       data-bs-target="#destroy_{{ $reference }}"
       data-bs-toggle="modal"
      >
        <i class="bi bi-trash-fill"></i>
    </a>
</li>
