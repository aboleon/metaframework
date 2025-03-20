<x-backend-layout>
    <x-slot name="header">
        <h2>
            {!! $data->id ? 'Courrier type: <b class="text-dark">'. $data->subject .'</b>' : 'Nouveau courrier type' !!}
        </h2>

        <div class="d-flex align-items-center" id="topbar-actions">

            <a class="btn btn-sm btn-secondary mx-2"
               href="{{ route('panel.mailtemplates.index') }}">
                <i class="fa-solid fa-bars"></i>
                Index
            </a>
            <a class="btn btn-sm btn-success"
               href="{{ route('panel.mailtemplates.create') }}">
                <i class="fa-solid fa-circle-plus"></i>
                Créer</a>
            @if ($data->id)
                <a class="btn btn-danger ms-2" href="#"
                   data-bs-toggle="modal"
                   data-bs-target="#destroy_{{ $data->id }}">
                    <i class="fa-solid fa-trash"></i>
                    Supprimer
                </a>
            @endif
            <div class="separator"></div>
            <x-save-btns/>
        </div>
    </x-slot>
    @php
        $error = $errors->any();
        $parser = new App\MailTemplates\Parser($data);
        $class = $parser->instance();
    @endphp



    @if ($data->id)
        <x-mfw::modal :route="route('panel.mailtemplates.destroy', $data)"
                      title="Suppression d'un courrier type"
                      question="Supprimer le courrier type <b class='text-dark'>{{ $data->subject }}</b> ?"
                      reference="destroy_{{ $data->id }}"/>
    @endif


    <div class="shadow p-4 bg-body-tertiary rounded">

                <x-mfw::response-messages/>
                <x-mfw::validation-errors/>

                @php
                    $error = $errors->any();
                @endphp

                <form method="post" action="{{ $route }}" id="wagaia-form">
                    @csrf
                    @if($data->id)
                        @method('put')
                    @endif

                    <h4>Configuration</h4>
                    <x-mfw::radio name="orientation" :values="\App\MailTemplates\Enum\MailTemplateMode::translations()" :affected="$error ? old('orientation') : $data->orientation" :default="\App\MailTemplates\Enum\MailTemplateMode::default()"/>

                    <x-mfw::radio name="format" :values="\App\MailTemplates\Enum\MailTemplateFormat::translations()" :affected="$error ? old('format') : $data->format" :default="\App\MailTemplates\Enum\MailTemplateFormat::default()"/>

                    <div class="mfw-line-separator mb-4 pt-2"></div>

                    <h4>Contenu</h4>

                    <x-mfw::translatable-tabs :fillables="$data->fillables" :model="$data"/>

                    <div class="row mb-4{{ !auth()->user()->hasRole('dev') ? ' d-none' : '' }}">
                        <div class="col-12">
                            <fieldset>
                                <legend class="mb-2"><x-mfw::devmark/> Paramètres réservés au développeur</legend>

                                <div class="row my-4 p-0">
                                    <div class="col-lg-6">
                                        <label for="identifier" class="form-label">Signature unique qui sert à mapper le template. </label>
                                        <input class="form-control" id="identifier" name="identifier" value="{{ $error ? old('identifier') : ($data->identifier ?: Str::random(10))}}"/>
                                    </div>
                                    <div class="col-lg-6">
                                        @php
                                            $params = [];
                                            if (!$class) {
                                                echo wg_parse_response($parser->fetchResponse());
                                            } else {

                                                    echo '<b class="d-block pb-2">Le mail fait appel à la classe suivante :</b>';
                                                    echo wg_warning_notice($class::class .' <br><span style="color:#656565"><em>et dont la signature est </em> <b style="color:black">' . $parser->fetchResponseElement('signature').' </b></span>');

                                                $params = $parser->fetchResponseElement('params');
                                                if ($params) {
                                                    echo '<b class="d-block pb-2">Elle prend les paramètres suivants :</b>';
                                                        echo wg_info_notice(collect($params)->join('<br>'));
                                                } else {
                                                    echo '<b class="d-block pb-2">Elle ne prend pas de paramètres.</b>';
                                                }
                                            }
                                        @endphp
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </form>

            </div>

    @push('js')
        @include('mailtemplates.scg_tinymce')
    @endpush
</x-backend-layout>
