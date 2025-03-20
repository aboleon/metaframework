<x-backend-layout>

    <x-slot name="header">
        <h2>
            {!! trans('mfw-sellable.label') .' '.  ($data->id ? '&raquo; Création ' : '') !!}
        </h2>


        <x-back.topbar.edit-combo
                route-prefix="panel.sellables"
                :model="$data"
                item-name="l'article {{$data->title}}"
        />
    </x-slot>
    @php
        $error = $errors->any();
    @endphp
    <div class="shadow p-4 bg-body-tertiary rounded">

        <x-mfw::response-messages />

        @php
            $error = $errors->any();
        @endphp

        <form method="post"
              action="{{ $route }}"
              id="wagaia-form"
              class="form"
              data-ajax="{{ route('ajax') }}">

            @csrf
            @if($data->id)
                @method('put')
            @endif

            <fieldset class="mb-4">
                <legend>{{ $data->title ?: 'Nouvel article' }}</legend>
                <div class="row p-0">

                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-4 mfw-holder position-relative mb-3">
                                <div class="d-flex justify-content-between align-items-end">
                                    <div class="w-100 me-3">
                                        <x-selectable-dictionnary key="catalog"
                                                                  name="category_id"
                                                                  :affected="$error ? old('category_id') : $data->category_id" />
                                    </div>
                                    <span class="fs-4 add-dynamic dict-dynamic" data-dict="catalog"><i
                                                class="fa-solid fa-circle-plus"></i></span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <x-mfw::select name="sold_per"
                                               :values="\App\Enum\SellablePer::translations()"
                                               :label="__('mfw-sellable.sold_per')"
                                               :nullable="false"
                                               :affected="$error ? old('sold_per') : ($data->id ? $data->sold_per : \App\Enum\SellablePer::default())" />
                            </div>
                            <div class="col-md-4 mb-3">
                                <x-mfw::input name="sku"
                                              :value="$error ? old('sku') : $data->sku"
                                              label="Référence fournisseur" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">

                            <div class="col-lg-4 mb-3">
                                <x-mfw::input type="number"
                                              name="price_buy"
                                              :params="['min'=>0, 'step'=>'any']"
                                              :value="$error ? old('price') : $data->price_buy"
                                              label="Prix d'achat € TTC " />
                            </div>
                            <div class="col-lg-4 mb-3">
                                <x-mfw::input type="number"
                                              name="price"
                                              :params="['min'=>0, 'step'=>'any']"
                                              :value="$error ? old('price') : $data->price"
                                              label="Prix de vente € TTC" />
                            </div>
                            <div class="col-lg-4 mb-3">
                                <x-mfw::select name="vat_id"
                                               :values="MetaFramework\Accessors\VatAccessor::readableArrayList()"
                                               :affected="$error ? old('vat_id') : ($data->id ? $data->vat_id : \MetaFramework\Accessors\VatAccessor::defaultRate()?->id)"
                                               :label="__('mfw-sellable.vat.label')"
                                               :nullable="false" />
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <x-mfw::translatable-tabs :model="$data" />

        </form>

    </div>
    @once
        @include('accounts.shared.dict_template')
    @endonce
    @push('js')
        <script>
          activateEventManagerLeftMenuItem('sellables');
        </script>
        <script src="{!! asset('js/dynamic_dictionnary.js') !!}"></script>
    @endpush
    @include('lib.tinymce')
</x-backend-layout>
