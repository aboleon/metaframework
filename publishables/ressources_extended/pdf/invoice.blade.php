@php use App\Enum\OrderClientType; @endphp
    <!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $documentTitle }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
        }

        table.table-bordered {
            border-collapse: collapse;
        }

        table.table-bordered,
        table.table-bordered tr,
        table.table-bordered td {
            border: 1px solid black;
        }

        table.table-semi-bordered {
            border-collapse: collapse;
            border-bottom: 2px solid black !important;
        }

        table.table-semi-bordered,
        table.table-semi-bordered tr,
        table.table-semi-bordered th,
        table.table-semi-bordered td {
            border: 1px solid black;
        }

        table.table-semi-bordered tbody tr,
        table.table-semi-bordered tbody th,
        table.table-semi-bordered tbody td {
            border-bottom: 1px solid transparent;
        }

        td {
            vertical-align: top;
        }


        .logo-container {
            background-repeat: no-repeat;
            background-position: 0 20px;
            background-size: 330px;
            width: 70%;
        }

        .logo-container img {
            margin-top: 12px;
            width: 100px;
        }

        .address-info {
            margin-top: 130px;
            width: 300px;
        }

        .header-info {
            text-align: center;
        }

        .title {
            font-size: 16px;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 10px;
            background: #aaa;
            border: 1px solid black;
            border-radius: 7px;
            padding: 5px;
        }

        .table-two-rows {
            margin-bottom: 21px;
        }

        .table-two-rows td {
            padding: 7px;
        }

        .info-coordinates {
            border: 1px solid black;
            text-align: left;
            height: 140px;
            padding: 5px;
            margin-bottom: 10px;
        }

        .table-items {
            text-align: center;
            margin-bottom: 30px;
        }

        .table-items thead {
            text-transform: uppercase;
        }

        .table-items tbody td:first-child {
            text-align: left;
        }

        .table-items td {
            padding: 5px;
        }


        .table-recap tr td:first-child {
            text-align: right;
            padding-right: 10px;
            white-space: nowrap;
        }

        .table-recap tr td:nth-child(2) {
            text-align: left;
            padding-left: 10px;
            white-space: nowrap;
        }

        .table-bottom {
            margin-bottom: 30px;
        }

    </style>
</head>
<body>

<table>
    <tr style="height:360px;">
        <td class="logo-container">

            @if ($banner)
                <img
                    src="{{  \App\Helpers\PdfHelper::imageToBase64($banner) }}"
                    style="width: 65%; position:absolute;"
                    alt="congrès logo">
            @endif

            <div class="address-info">
                Divine [id]<br>
                17 rue Venture 13001 Marseille, FRANCE<br>
                Tél. +33 (0) 491 57 19 60 - Fax +33 (0) 491 57 19 61<br>
                SIRET : 449 895 333 00036 - Code APE : 7911Z<br>
                TVA : FR75449895333
            </div>

            <img
                src="{{  \App\Helpers\PdfHelper::imageToBase64('assets/pdf/logonew.jpg') }}"
                alt="divine logo">
        </td>
        <td class="header-info">

            <div class="title">{{ $documentTitle }}</div>

            <table class="table-bordered table-two-rows">
                <tr>
                    <td><b>DATE</b></td>
                </tr>
                <tr>
                    <td>{{ $isReceipt ? $order->created_at->format('d/m/Y') : ($orderAccessor->isOrator() ? $order->created_at->format('d/m/Y') : $invoice?->created_at->format('d/m/Y')) }}</td>
                </tr>
            </table>

            <div class="info-coordinates">
                <b>Adresse de facturation :</b><br>
                {!! $address !!}
                @if($order->invoiceable?->vat_number)
                    <br><br><b>TVA Intracommunautaire :</b><br>
                    <br>{{ $order->invoiceable->vat_number }}
                @endif
            </div>

        </td>
    </tr>
</table>


<table class="table-semi-bordered table-items">
    <thead>
    <tr>
        <th style="width: 62%;text-align: left;padding-left: 20px">Désignation</th>
        <th>{{ __('ui.quantity') }}</th>
        @if (!$isReceipt)
            <th>{{ __('front/order.price_ht') }}</th>
            <th>{{ __('mfw-sellable.vat.label') }}</th>
        @endif
        <th>{{ __('front/order.total_amount') }}</th>
    </tr>
    </thead>
    <tbody>
    @if(in_array($order->client_type, [OrderClientType::CONTACT->value, OrderClientType::ORATOR->value]))
        @include('pdf.invoice.individual-order-lines')
    @elseif(OrderClientType::GROUP->value === $order->client_type)
        @include('pdf.invoice.group-order-lines')
    @endif
    </tbody>
</table>

@if (!$isReceipt)

    <table class="table-bottom">
        <tr>
            <td style="width: 80%">
                @if ($lg == 'fr')
                    Chèque en euros payable en France au nom de Divine[id] <br>
                @endif
                {!! __('front/order.payment_means_invoice') !!}
                IBAN : FR 76 1130 6000 9348 1141 8671 957<br>
                BIC : AGRIFRPP813<br>
            </td>
            <td>
                <table class="table-recap">
                    <tr>
                        <td>Total HT</td>
                        <td>{{ $orderAccessor->isOrator() ? '0.00 EUR' : \MetaFramework\Accessors\Prices::readableFormat($invoiceTotals['net'], 'EUR', '.') }}</td>
                    </tr>
                    @foreach($invoiceVatTotals as $vat_id => $subtotal)
                        <tr>
                            <td>
                                TVA {{ \MetaFramework\Accessors\VatAccessor::readableArrayList()[$vat_id] }}</td>
                            <td>{{  $orderAccessor->isOrator() ? '0.00 EUR' : \MetaFramework\Accessors\Prices::readableFormat($subtotal, 'EUR', '.') }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td>{{ __('front/order.total_amount') }}</td>
                        <td>{{  $orderAccessor->isOrator() ? '0.00 EUR' : \MetaFramework\Accessors\Prices::readableFormat($invoiceTotals['net'] + $invoiceTotals['vat'], 'EUR', '.') }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('front/order.settled') }}</td>
                        <td>{{  $orderAccessor->isOrator() ? '0.00 EUR' : \MetaFramework\Accessors\Prices::readableFormat($paid, 'EUR', '.') }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('front/order.to_pay') }}</td>
                        <td>{{  $orderAccessor->isOrator() ? '0.00 EUR' : \MetaFramework\Accessors\Prices::readableFormat(($invoiceTotals['net'] + $invoiceTotals['vat']) - $paid, 'EUR', '.') }}</td>
                    </tr>
                </table>
            </td>
            <td></td>
        </tr>
    </table>


    @if ($proforma && $order->payments->isNotEmpty())
        <strong>Paiements effectués</strong>
        <hr/>
        <table>
            <thead>
            <tr>
                <th style="text-align: left">Date</th>
                <th style="text-align: left">Montant</th>
                <th style="text-align: left">Moyen de paiement</th>
            </tr>
            </thead>
            @foreach($order->payments as $payment)
                <tr>
                    <td>
                        {{ $payment->date->format('d/m/Y') }}
                    </td>
                    <td>
                        {{ \MetaFramework\Accessors\Prices::readableFormat($payment->amount, 'EUR', '.') }}
                    </td>
                    <td>
                        {{ \App\Enum\PaymentMethod::translated($payment->payment_method) }}
                    </td>
                </tr>
            @endforeach
        </table>
    @endif
    @if ($order->po)
        <strong>PO :</strong> {{ $order->po }}
    @endif
    @if ($order->note)
        <br><br>
        <strong>Note :</strong><br>{!! $order->note !!}
    @endif
    @if ($order->terms)
        <br><br>
        <strong>
            {{ __('front/order.terms_of_payment') }} :</strong><br>{!! $order->terms !!}
    @endif

@else
    {{ __('front/order.receipt_text') }}
@endif

</body>
</html>
