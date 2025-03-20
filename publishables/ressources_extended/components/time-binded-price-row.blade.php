<div class="mb-3 row time-binded-price-row" data-identifier="{{ $identifier }}">
    <div class="col-md-3">
        <x-mfw::number name="{{ $prefix }}.price." label="TTC" :value="$price->price"/>
    </div>
    <div class="col-md-3">
        <x-mfw::datepicker name="{{ $prefix }}.ends." label="jusqu'au" :value="$price->ends" :required="true"/>
    </div>
    <div class="col-md-3 pt-4">
        <x-mfw::simple-modal id="delete_time_binded_price"
                             class="btn btn-danger btn-sm mt-2"
                             title="Suppression d'un prix"
                             confirmclass="btn-danger"
                             confirm="Supprimer"
                             :callback="$callback"
                             :identifier="$identifier"
                             :modelid="$price->id"
                             text="Supprimer"/>
    </div>
</div>

@pushonce('js')
    <script>
        const time_binded_prices = {
            container: function() {
                return $('#time-binded-prices');
            },
            addBtn: function () {
                return $('#add-time-binded-price');
            },
            guid: function() {
              return guid();
            },
            add: function () {
                this.addBtn().off().on('click', function () {
                    time_binded_prices.container().append($('template#time-binded-price-row').html());
                    let last_row = time_binded_prices.container().find('.time-binded-price-row').last(),
                        guid = time_binded_prices.guid();
                    last_row.attr('data-identifier', guid);
                    last_row.find('a[data-modal-id=delete_time_binded_price]').attr('data-identifier', guid);
                    setDatepicker();
                });
            },
            init: function () {
                this.add();
            },
        };
        time_binded_prices.init();
    </script>
@endpushonce
