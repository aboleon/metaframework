<{{$tag}} data-id="{{ $item->id }}" class="item {{ ($level > 0 ? 'fw-normal ms-'.$level*4:'fw-bold').($item->parent ? ' child' : '') .
 ($filter && !in_array($item->id, $subset) ? ' d-none' : '') }}"
{!!  $item->parent ? ' data-parent="'.$item->parent.'"' : ''  !!}>
<x-mfw::checkbox :name="$formTag" :value="$item->id" :affected="$affected" :label="$alltranslations ? implode(' / ',\App\Helpers\Translations::translationsState($item, 'name')) : $item->name"/>
</{{$tag}}>
@if ($item->entries->isNotEmpty())
    @foreach($item->entries as $subitem)
        <x-dico-form-printer :filter="$filter" :subset="$subset" :item="$subitem" :level="$level+1" :tag="$tag" :form-tag="$formTag" :affected="$affected" :alltranslations="$alltranslations"/>
    @endforeach
@endif
