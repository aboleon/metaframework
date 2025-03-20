<div class="mfw-line-separator mt-5"></div>
<div class="mt-5">
    <h4 class="d-inline-block">Champs sur mesure</h4>
    @if ($customFormBindedModel->id)
        <div class="d-inline-block ms-4">
            @if (!$customFormBindedModel->customForm)
                <a href="{{ route('panel.customfields.create', ['model'=> $customFormBindedModel::class,'id'=>$customFormBindedModel->id]) }}" class="btn btn-sm btn-success">Ajouter</a>
            @else
                <a href="{{ route('panel.customfields.edit', $customFormBindedModel->customForm) }}" class="btn btn-sm btn-warning text-dark">Gérer</a>
            @endif
        </div>
    @else
        <x-mfw::alert type="warning" message="Vous pourrez créér des champs lorsque le compte aura été créé." />
    @endif
</div>

@if ($customFormBindedModel->id && $customFormBindedModel->customForm)
    @include('custom_fields.show', ['data' => $customFormBindedModel->customForm])
@endif
