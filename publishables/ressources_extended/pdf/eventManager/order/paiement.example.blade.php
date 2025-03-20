@php use App\Helpers\PdfHelper; @endphp
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
        table.table-semi-bordered td {
            border: 1px solid black;
        }

        table.table-semi-bordered tbody tr,
        table.table-semi-bordered tbody td {
            border-bottom: 1px solid transparent;
        }

        td {
            vertical-align: top;
        }


        .logo-container {
            background-image: url('{{ PdfHelper::imageToBase64('assets/pdf/biglogo.jpg') }}');
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
        }

        .table-recap tr td:nth-child(2) {
            text-align: left;
            padding-left: 10px;
        }

        .table-bottom{
            margin-bottom: 30px;
        }

    </style>
</head>
<body>
<table>
    <tr style="height:360px;">
        <td class="logo-container">
            <div class="address-info">
                Divine [id]<br>
                17 rue Venture 13001 Marseille, FRANCE<br>
                Tél. +33 (0) 491 57 19 60 - Fax +33 (0) 491 57 19 61<br>
                SIRET : 449 895 333 00036 - Code APE : 7911Z
            </div>

            <img
                    src="{{  PdfHelper::imageToBase64('assets/pdf/logonew.jpg') }}"
                    alt="divine logo">
        </td>
        <td class="header-info">

            <div class="title">Proforma</div>


            <table class="table-bordered table-two-rows">
                <tr>
                    <td><b>DATE</b></td>
                </tr>
                <tr>
                    <td>26/10/2023</td>
                </tr>
            </table>

            <div class="info-coordinates">
                Adresse de facturation :<br>
                Hôpital Paul Desbief<br>
                BERNUS Mélanie<br>
                Médecine<br>
                38 rue de Forbin<br>
                13236 Marseille<br>
                France
            </div>

            <table class="table-bordered table-two-rows">
                <tr>
                    <td><b>TVA Intracommunautaire</b></td>
                </tr>
                <tr>
                    <td>FR75449895333</td>
                </tr>
            </table>
        </td>
    </tr>
</table>


<table class="table-semi-bordered table-items">
    <thead>
    <tr>
        <td style="width: 62%">Désignation</td>
        <td>Quantité</td>
        <td>Prix HT</td>
        <td>Tva</td>
        <td>Total TTC</td>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>Diner du congrès (cfcgv)</td>
        <td>1</td>
        <td>75.00</td>
        <td>20%</td>
        <td>90.00</td>
    </tr>
    <tr>
        <td>Diner du congrès (cfcgv)</td>
        <td>1</td>
        <td>75.00</td>
        <td>20%</td>
        <td>90.00</td>
    </tr>
    <tr>
        <td>Diner du congrès (cfcgv)</td>
        <td>1</td>
        <td>75.00</td>
        <td>20%</td>
        <td>90.00</td>
    </tr>
    <tr style="height: 200px">
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    </tbody>
</table>

<table class="table-bottom">
    <tr>
        <td style="width: 80%">
            Chèque en euros payable en France au nom de Divine[id] <br>
            Carte bancaire : Visa ou Mastercard uniquement Virement :<br>
            IBAN : FR 76 1130 6000 9348 1141 8671 957<br>
            BIC : AGRIFRPP813<br>
            Merci de préciser le numéro de proforma
        </td>
        <td>
            <table class="table-recap">
                <tr>
                    <td>Total HT</td>
                    <td>225.00 EUR</td>
                </tr>
                <tr>
                    <td>TVA 20%</td>
                    <td>45.00 EUR</td>
                </tr>
                <tr>
                    <td>TVA 10%</td>
                    <td>45.00 EUR</td>
                </tr>
                <tr>
                    <td>Total TTC</td>
                    <td>270.00 EUR</td>
                </tr>
                <tr>
                    <td>Réglé</td>
                    <td>270.00 EUR</td>
                </tr>
                <tr>
                    <td>Reste à régler</td>
                    <td>270.00 EUR</td>
                </tr>
            </table>
        </td>
        <td></td>
    </tr>
</table>

<div>
    Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusamus aperiam aspernatur aut autem
    consequatur cumque cupiditate, debitis earum eius et fugit id impedit inventore iste natus
    obcaecati optio velit voluptate?
</div>
</body>
</html>