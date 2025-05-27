@if ($medias->isNotEmpty())
    @foreach($medias as $media)
        @php
                $is_image = $isImage($media);
                $cropableImg = new \MetaFramework\Mediaclass\Cropable($media);
                $cropableImg->setCropableFromComponent($cropable);
                $preview = $is_image ? $media->url($cropableImg->isCropped() ? 'cropped': 'sm') : asset('vendor/mfw/mediaclass/images/files/' . $media->extension().'.png');
        @endphp
        <div class="mediaclass unlinkable uploaded-image my-2" data-id="{{ $media->id }}"
             id="mediaclass-{{$media->id}}">
            <span class="unlink"><i class="bi bi-x-circle-fill"></i></span>
            <div class="row m-0">
                <div class="col-sm-3 impImg p-0 position-relative preview {{ $is_image ? 'image' : 'file' }}"
                     style="background: url({{ $preview  }});{!! $is_image ?'background-size: cover':'background-size: contain;background-repeat: no-repeat;background-position: center;' !!}">
                    <div class="actions">
                        <a target="_blank" href="{{ $media->url() }}" class="zoom">
                            <i class="fa-sharp fa-solid fa-magnifying-glass"></i>
                        </a>
                    </div>
                </div>
                <div class="col-sm-9 impFileName">
                    <div class="row infos">
                        <div class="col-sm-12">
                            <p class="name">
                                <span
                                        class="rounded-1 py-1 px-2 text-bg-secondary">{{ $media->original_filename }}</span>
                                <span class="rounded-1 py-1 px-2 bg-light-subtle text-dark opacity-75">
                                {{ __('mediaclass.uploaded_at', ['date' => $media->created_at->format('d/m/Y'), 'time' => $media->created_at->format('H:i')]) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    @if($is_image)
                        {!! $cropableImg->links() !!}
                    @endif

                    <div class="row params mt-3">
                        <div class="col-sm-7 description {{ !$description ? 'd-none' :'' }}">
                            @foreach(\MetaFramework\Accessors\Locale::projectLocales() as $locale)
                                <x-mfw::textarea name="mediaclass[{{ $media->id }}][description][{{ $locale }}]"
                                                 :height="100" class="mt-2 description"
                                                 :value="$media->description[$locale] ?? ''"
                                                 label="Description ({{ $locale }})"/>
                            @endforeach
                        </div>
                        <div class="col-sm-5 positions text-center ps-2{{ $positions ? '' : ' d-none' }}">
                            <b>Positions par rapport au contenu</b>
                            <div class="choices pt-2">
                                @foreach($getPositionning() as $p)
                                    <i class="bi bi-arrow-{{ $p }}-square-fill{{ ($media->position == $p ? ' active':'') }}"
                                       data-position="{{ $p }}"></i>
                                @endforeach
                                <input type="hidden" name="mediaclass[{{ $media->id }}][position]"
                                       value="{{ $media->position }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endforeach
@endif

<div class="mt-2 mediaclass-alerts" data-msg="{{ $nomedia }}">
    @if ($medias->isEmpty())
        <x-mfw::alert type="warning" :message="$nomedia"/>
    @endif
</div>