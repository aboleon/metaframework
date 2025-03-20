<x-front-mail-layout>
    <div class="p-20">

        <p>
            Bonjour,<br>
            {{$userName}} veut créer un groupe dans le cadre de l'événement {{$eventName}}.
            Voici les informations qu'il a renseignées :
        </p>
        <table>
            <tr>
                <td>Nom de l'entité qui va inscrire et payer les inscriptions et/ou hébergements
                </td>
                <td>{{$name}}</td>
            </tr>
            <tr>
                <td>Adresse postale</td>
                <td>{{$address}}</td>
            </tr>
            <tr>
                <td>CP</td>
                <td>{{$zip}}</td>
            </tr>
            <tr>
                <td>Ville</td>
                <td>{{$city}}</td>
            </tr>
            <tr>
                <td>Pays</td>
                <td>{{$country}}</td>
            </tr>
            <tr>
                <td>Téléphone</td>
                <td>{{$phone}}</td>
            </tr>
        </table>
    </div>

</x-front-mail-layout>
