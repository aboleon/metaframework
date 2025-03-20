<script type="text/html" id="payment-row-template">
    <tr data-id="{id}">
        <td class="date" data-value="{date}">{date_display}</td>
        <td class="amount" data-value="{amount}">{amount}</td>
        <td class="payment_method" data-value="{payment_method}">{payment_method_display}</td>
        <td class="authorization_number" data-value="{authorization_number}">
            {authorization_number}
        </td>
        <td class="card_number" data-value="{card_number}">{card_number}</td>
        <td class="bank" data-value="{bank}">{bank}</td>
        <td class="issuer" data-value="{issuer}">{issuer}</td>
        <td class="check_number" data-value="{check_number}">{check_number}</td>
        <td class="d-flex gap-1 invoiced {{ $invoiced ? 'd-none' : '' }}">
            <a href="#"
               class="btn btn-sm btn-secondary btn-edit"
               data-bs-toggle="tooltip"
               data-bs-placement="top"
               data-bs-title="Éditer">
                <i class="fas fa-pen"></i>
            </a>
            <a href="#"
               class="btn btn-sm btn-warning d-none"
               data-bs-toggle="tooltip"
               data-bs-placement="top"
               data-bs-title="Rembourser">
                <i class="fas fa-undo-alt text-dark"></i>
            </a>


            <x-ajax-modal
                class="btn btn-sm btn-danger btn-delete-order-payment"
                icon_class="fas fa-trash"
                tooltip="Supprimer"
                on_confirm="deleteOrderPayment"
            />

        </td>
    </tr>
</script>
<template id="payment-edit-row-template">
    <tr>
        <input type="hidden" name="payment[id][]" value="0">
        <td>
            <x-mfw::datepicker name="payment[date][]"
                               config="dateFormat=Y-m-d,altInput=true,defaultDate=today,altFormat={{config('app.date_display_format')}}"
                               :value="$error ? old('payment.date') : date('d/m/Y')"/>
        </td>
        <td>
            <input name="payment[amount][]" type="number" step="0.01" class="form-control" placeholder="€"/>
        </td>
        <td>
            <x-mfw::select
                name="payment[payment_method][]"
                :values="$paymentMethods"
                affected="check"
                :nullable="false"/>
        </td>
        <td><input name="payment[authorization_number][]"
                   type="text"
                   class="form-control"
                   placeholder="Num auto"></td>
        <td><input name="payment[card_number][]"
                   type="text"
                   class="form-control"
                   placeholder="Num carte"></td>
        <td><input name="payment[bank][]" type="text" class="form-control" placeholder="Banque">
        </td>
        <td><input name="payment[issuer][]" type="text" class="form-control" placeholder="Emetteur">
        </td>
        <td><input name="payment[check_number][]"
                   type="text"
                   class="form-control"
                   placeholder="Chèque"></td>
        <td>
            <div class="d-flex gap-1 invoiced">

                <a href="#"
                   class="btn btn-sm btn-success btn-validate"
                   data-bs-placement="top"
                   data-bs-title="Valider"
                   data-bs-toggle="tooltip"><i class="fas fa-check"></i></a>

                <a href="#"
                   class="btn btn-sm btn-danger btn-delete"
                   data-bs-placement="top"
                   data-bs-title="Supprimer"
                   data-bs-toggle="tooltip"><i class="fas fa-trash"></i></a>

                <a href="#"
                   style="display: none;"
                   class="btn btn-sm btn-danger btn-cancel"
                   data-bs-placement="top"
                   data-bs-title="Annuler"
                   data-bs-toggle="tooltip"><i class="fas fa-times"></i></a>
            </div>
        </td>
    </tr>
</template>
