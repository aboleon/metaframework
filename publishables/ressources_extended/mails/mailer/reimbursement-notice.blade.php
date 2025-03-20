<x-mail-layout :banner="$mailed->banner">
    Nous venons de procéder au remboursement de la caution d'un montant de {{$mailed->amount}}€
    que vous avez payée concernant {{$mailed->depositName}} pour {{$mailed->eventName}}.
    <br>
    Cela concerne : {{$mailed->beneficiaryName}}
</x-mail-layout>