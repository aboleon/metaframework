<x-dico-row :item="$item" :level="$level" :dictionnary="$dictionnary"/>
@if ($item->entries->isNotEmpty())
    @foreach($item->entries as $subitem)
        <x-dico-row-looper :item="$subitem" :level="$level+1" :dictionnary="$dictionnary"/>
    @endforeach
@endif
