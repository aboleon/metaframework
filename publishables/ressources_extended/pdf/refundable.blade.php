@php @endphp
    <!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Facture</title>
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
        table.table-semi-bordered td
        {
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
            background-position: 0px 20px;
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

            <div class="title">Avoir</div>


            <table class="table-bordered table-two-rows">
                <tr>
                    <td><b>DATE</b></td>
                </tr>
                <tr>
                    <td>{{ $refund->created_at->format('d/m/Y') }}</td>
                </tr>
            </table>

            <div class="info-coordinates">
                <b>Adresse de facturation :</b><br>
                {!! $address !!}
                @if($order->invoiceable->vat_number)
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

        <th style="padding-left:20px;width: 62%; text-align: left; vertical-align: middle; ">Désignation</th>
        <th>Date</th>
        <th>Prix HT</th>
        <th>Tva</th>
        <th>Total TTC</th>
    </tr>
    </thead>
    <tbody>
    @foreach($refund->items as $model)
        @php
            $net = \MetaFramework\Accessors\VatAccessor::netPriceFromVatPrice($model->amount, $model->vat_id);
            $vat = \MetaFramework\Accessors\VatAccessor::vatForPrice($model->amount, $model->vat_id);
            $totals[$model->vat_id][] = [
                'net' => $net,
                'ttc' => $model->amount,
                'vat' => $vat
            ];
        @endphp
        <tr>
            <td style="text-align: left;padding-left:20px;">
                {{ $model->object }}
            </td>
            <td>
                {{  $model->date }}
            </td>
            <td class="net_price align-top">
                {{ \MetaFramework\Accessors\Prices::readableFormat($net, '','.') }}

            </td>
            <td class="align-top">
                {{  \MetaFramework\Accessors\VatAccessor::rate($model->vat_id) }}%
            </td>
            <td class="align-top">
                {{ \MetaFramework\Accessors\Prices::readableFormat($model->amount, '','.') }}
            </td>
        </tr>

    @endforeach
    </tbody>
</table>
@php
    $sums = collect($totals)->flatten(1)->reduce(function ($carry, $item) {
    $carry['net'] += $item['net'];
    $carry['ttc'] += $item['ttc'];
    return $carry;
}, ['net' => 0, 'ttc' => 0]);
@endphp

<table class="table-bottom">
    <tr>
        <td style="width: 80%"></td>
        <td>
            <table class="table-recap">
                <tr>
                    <td>Total HT</td>
                    <td>{{ \MetaFramework\Accessors\Prices::readableFormat($sums['net'], 'EUR', '.') }}</td>
                </tr>

                @foreach($totals as $vat_id => $subtotal)
                    <tr>
                        <td>
                            TVA {{ \MetaFramework\Accessors\VatAccessor::readableArrayList()[$vat_id] }}</td>
                        <td>{{ \MetaFramework\Accessors\Prices::readableFormat(array_sum(array_column($subtotal,'vat')), 'EUR', '.') }}</td>
                    </tr>
                @endforeach

                <tr>
                    <td>Total TTC</td>
                    <td>{{ \MetaFramework\Accessors\Prices::readableFormat($sums['ttc'], 'EUR', '.') }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
