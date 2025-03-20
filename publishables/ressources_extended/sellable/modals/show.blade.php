<style>
    .modal-content {
        width: 800px;
    }

    em {
        display: block;
        margin: 6px 0;
        padding: 0 15px;
        font-size: 16px;
        font-style: normal;
    }
</style>

<form>

    <div class="params">
        <input type="hidden" name="event_id" value="{{ $event_id }}">
        <input type="hidden" name="sellable_id" value="{{ $sellable->id }}">
    </div>
    <fieldset class="mb-4">
        <legend>{{ $custom?->title ?: $data->title }}</legend>
        <div class="row p-0">
            <div class="col-12">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <x-mfw::select name="sold_per"
                                       :values="\App\Enum\SellablePer::translations()"
                                       :label="__('mfw-sellable.sold_per')"
                                       :nullable="false"
                                       :affected="$data->sold_per"/>
                        {!! $custom ? '<em>'.\App\Enum\SellablePer::translated($sellable->sold_per).'</em>' : '' !!}
                    </div>
                    <div class="col-md-4 mb-3">
                        <x-mfw::input name="sku" :value="$data->sku" label="Référence"/>
                        {!! $custom ? '<em>'.$sellable->sku.'</em>' : '' !!}
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="row">
                    <div class="col-lg-4 mb-3">
                        <x-mfw::input type="number"
                                      name="price_buy"
                                      :params="['min'=>0, 'step'=>'any']"
                                      :value="$data->price_buy" label="Prix d'achat"/>
                        {!! $custom ? '<em>'.$sellable->price_buy.'</em>' : '' !!}
                    </div>
                    <div class="col-lg-4 mb-3">
                        <x-mfw::input type="number"
                                      name="price"
                                      :params="['min'=>0, 'step'=>'any']"
                                      :value="$data->price" label="Prix de vente"/>
                        {!! $custom ? '<em>'.$sellable->price.'</em>' : '' !!}
                    </div>
                    <div class="col-lg-4 mb-3">
                        <x-mfw::select name="vat_id"
                                       :values="MetaFramework\Accessors\VatAccessor::readableArrayList()"
                                       :affected="$data->vat_id"
                                       :label="__('mfw-sellable.vat.label')" :nullable="false"/>
                        {!! $custom ? '<em>'.MetaFramework\Accessors\VatAccessor::readableArrayList()[$sellable->vat_id].'</em>' : '' !!}
                    </div>
                </div>
            </div>
        </div>
    </fieldset>

    <x-mfw::translatable-tabs :model="$data"/>

    {!! $info !!}
</form>
<script>
    $(function () {
        $('#remove_sellable_customization').off().on('click', function () {
            console.log('Remove customization');
            ajax('action=removeSellableCustomization&' + $('#mfwDynamicModal .params').find('input').serialize(), $('#mfwDynamicModalBody'));
        });
    });
</script>
