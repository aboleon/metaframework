@php
    use App\Enum\ParticipantType;
    use App\Models\EventContact;
@endphp
<div class="tab-pane fade"
     id="history-tabpane"
     data-ajax="{{route('ajax')}}"
     role="tabpanel"
     aria-labelledby="history-tabpane-tab">

    <div class="mt-4">
        <h4 class="mt-4">Historique</h4>
        <div class="row m-0">
            <div class="col-md-6">
                <h5>Participation du contact</h5>
                @php
                    $eventContactRows = EventContact::with('event.texts', 'participationType')
                    ->where("user_id", $account->id)->get();
                @endphp

                @if(false === $eventContactRows->isEmpty())
                    <table class="table">
                        <thead class="table-secondary">
                        <tr>
                            <th>Événement</th>
                            <th>Type de participation</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($eventContactRows as $eventContactRow)
                            @php
                                $pGroup = $eventContactRow->participationType?->group??$eventContactRow->registration_type;
                            @endphp
                            <tr>
                                <td>{{ $eventContactRow->event?->texts?->name }}</td>
                                <td>{{ $eventContactRow->participationType ?
                                        $eventContactRow->participationType->name:
                                        (
                                        $pGroup ?
                                        "Groupe: " . ParticipantType::translated($pGroup) :
                                         "Non défini"
                                         )
                                     }}
                                </td>
                            </tr>
                        </tbody>
                        @endforeach
                    </table>
                @else
                    <p>Aucune participation</p>
                @endif
            </div>
            <div class="col-md-6">
                <h5>Contact d'un groupe</h5>
                @php
                    $eventGroupRows = \App\Models\EventManager\EventGroup::with('event.texts', 'group', 'participationType')
                    ->whereHas("group", function($q) use($account){
                        $q->where('main_contact_id', $account->id);
                    })
                    ->get();

                @endphp
                @if($eventGroupRows->isNotEmpty())
                    <table class="table">
                        <thead class="table-secondary">
                        <tr>
                            <th>Événement</th>
                            <th>Groupe</th>
                            <th>Type de participation</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($eventGroupRows as $eventGroupRow)
                            <tr>
                                <td>{{ $eventGroupRow->event?->texts?->name }}</td>
                                <td>{{ $eventGroupRow->group?->name }}</td>
                                <td>{{ $eventGroupRow->participationType ?
                                        $eventGroupRow->participationType->name :
                                         "Non défini"
                                     }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <p>Aucun groupe</p>
                @endif
            </div>


        </div>
    </div>
</div>

