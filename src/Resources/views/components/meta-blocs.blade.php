@if (($model->uses('blocs') && $meta->id) or $meta->type == 'bloc')
    <div class="bloc-editable" id="meta_blocs" data-ajax="{{ route('mfw.ajax') }}">
        @if ($meta->type == 'bloc')
        <input type="hidden" name="meta[parent]" value="{{ $meta->parent }}"/>
        <input type="hidden" name="meta[taxonomy]" value="{{ $meta->taxonomy }}"/>
        @endif
        <fieldset>
            <legend class="">Blocs</legend>
        </fieldset>

        <div id="assigned_blocs" data-ajax="{{ route('mfw.ajax') }}">
            @php
                $blocs = \MetaFramework\Models\MetaBloc::getBlocsForMeta($meta->type == 'bloc' ? $meta->parent : $meta->id);
                if ($blocs->isNotEmpty()) { ?>
                    <ul id="meta_bloc_list" class="m-0 mb-3 p-0">
                    <?php
                    foreach($blocs as $bloc) {
                        echo '<li data-id="'.$bloc->id.'" '.($bloc->id == $meta->id ? ' class="active"':'').'><a href="'.route('mfw.meta.show', ['type'=>'bloc', 'id'=>$bloc->id]).'">'.($bloc->translation('title') ?: 'Sans titre').'<code class="d-block">'.$bloc->taxonomy::getLabel().'</code></a></li>';
                    }
                    echo '</ul>';
                }
            @endphp
        </div>

        <x-mfw-input::select name="meta_blocs_selector" :values="\MetaFramework\Models\MetaBloc::selectableArray()" label="Affecter un bloc" :affected="collect()" :nullable="false" :disablename="true"/>

        <span id="add_meta_bloc" class="btn btn-default btn-sm mt-3">Ajouter un bloc</span>
    </div>
    @push('callbacks')
        <script>
            function redirectAfterMetaBloc(data) {
                console.log(data);
                if (!data.hasOwnProperty('error')) {
                    setTimeout(function () {
                        window.location.href = '/{{ \MetaFramework\Accessors\Routing::backend() }}/meta/show/bloc/' + data.meta.id;
                    }, 2000);
                }
            }
        </script>
    @endpush
    @include('mfw::lib.sortable')
    @push('js')
        <script>
            $(function () {
                $('#add_meta_bloc').click(function () {
                    ajax('action=addMetaBloc&parent={{ $meta->type=='bloc' ? $meta->hasParent->id : $meta->id }}&bloc=' + $('#meta_blocs_selector').val(), $('#meta_blocs'));
                });

                sortableContent($('#meta_bloc_list'), 'li', $('#assigned_blocs'));
            });
        </script>
    @endpush
@endif
