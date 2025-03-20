------------------------<br>
Envoyé par :<br>
------------------------<br>
Prénom / Nom: {!! request('first_name') . ' ' . request('last_name') !!}<br>
Entreprise: {{ request('company') }}<br>
Activité: {{ request('activity') }}<br>
e-mail: {{ request('email') }}<br>
Num.de téléphone: {{ request('phone') }}
<br><br><br>

------------------------<br>
Message :<br>
------------------------<br>
{!! request('message') !!}<br><br><br>


<br>------------------------<br>
envoyé le {!!date('d/m/Y à H:i')!!} de {!!request()->getaccountIp()!!}
