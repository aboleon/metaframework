@php
    $assoMsgs = [];
    if($account->groups->isNotEmpty()){
        $assoMsgs[] = "Ce compte est affecté comme contact pour les groupes suivants:<br>" . \App\Printers\Account::associatedGroups($account);
    }
    if($account->events->isNotEmpty()){
        $assoMsgs[] = "Ce compte est affecté comme contact pour les événements suivants:<br> " . \App\Printers\Account::associatedEvents($account);
    }
@endphp
<div class="tab-pane fade"
     id="associations-tabpane"
     role="tabpanel"
     aria-labelledby="associations-tabpane-tab">

    <div class="row m-0">
        <div class="">
            @foreach($assoMsgs as $group_msg)
                <div class="me-2 my-4">
                    <x-mfw::notice :message="$group_msg" />
                </div>
            @endforeach
        </div>

    </div>

</div>

