@php use App\Helpers\ArrayHelper; @endphp
@props([
    'useIsMainContact' => true,
    'eventGroup' => null,
    'provenance' => 'event',
])


@php
    $extraParams = [
        'group' => 1,
    ];
    if($eventGroup){
        $extraParams['event_group'] = $eventGroup->id;
    }
    $sParams = ArrayHelper::toString($extraParams, "&");
@endphp

@pushonce('css')
    {!! csscrush_inline(public_path('vendor/mfw/css/fragments/_dynamic_search.css')) !!}
@endpushonce
<div class="row py-3">
    <div class="col-md-6">
        <div id="client-search"
             class="client-search-holder me-3 position-relative d-flex align-items-center"
             data-ajax="{{ route('ajax') }}"
             data-create="{{ route('panel.accounts.create', [strtolower($queryTag) => $model->id]) }}">
            <x-mfw::input type="search"
                          name="client"
                          class="client-search-input me-3"
                          :value="request('client')"
                          :params="[
                          'placeholder'=>'Rechercher et ajouter un contact',
                          'autocomplete'=>'off',
                          'extra_param' => $sParams,
                          ]" />

            <a class="btn btn-sm btn-success d-flex align-items-center"
               href="{{route('panel.accounts.create', [
                    $queryTag => $model->id,
                    'provenance' => $provenance,
               ])}}">
                <i class="fa-solid fa-circle-plus me-1"></i> Créer</a>

            <div class="messages"></div>
        </div>
    </div>
</div>
<table class="table" id="ajaxable_contacts" data-id="{{ $model->id }}">
    <thead>
    <tr>
        <th>Prénom</th>
        <th>Nom</th>
        <th>Fonction</th>
        <th>e-mail principal</th>
        <th>Ville</th>
        <th>Pays</th>
        @if($useIsMainContact)
            <th>Est principal</th>
        @endif
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    @if ($contacts && $contacts->isNotEmpty())
        @foreach($contacts as $item)
            @php
                $accessor = (new \App\Accessors\Accounts($item));
                $address = $accessor->billingAddress();
            @endphp
            <tr class="client_{{ $item->id }}" data-id="{{ $item->id }}">
                <td>{{ $item->first_name }}</td>
                <td>{{ $item->last_name }}</td>
                <td>{{ $item->function ?: '-' }}</td>
                <td>{{ $item->email }}</td>
                <td>{{ $address?->locality }}</td>
                <td>{{  \MetaFramework\Accessors\Countries::getCountryNameByCode($address?->country_code) }}
                @if($useIsMainContact)
                    <td>{!! $item->is_main_contact?'<i class="bi bi-check-circle" style="color: green;"></i>':'<i class="bi bi-x-circle" style="color:red"></i>' !!}</td>
                @endif
                <td>
                    <ul class="mfw-actions">
                        @if($useIsMainContact)
                            <li>
                                <a class="btn-make-main-contact btn btn-danger"
                                   style="color:white;"
                                   href="#"
                                   data-bs-toggle="tooltip"
                                   data-bs-placement="top"
                                   data-bs-title="Rendre contact principal">
                                    <i class="fa-solid fa-crown"></i></a>
                            </li>
                        @endif
                        <li>
                            <a class="dissociate btn btn-warning"
                               style="color:#5b5b5b !important;"
                               href="#"
                               data-bs-toggle="tooltip"
                               data-bs-placement="top"
                               data-bs-title="Dissocier">
                                <i class="fa-solid fa-link-slash"></i></a>
                        </li>
                        <x-mfw::edit-link :route="route('panel.accounts.edit', [
                            'account' => $item->id,
                        ])" />
                    </ul>
                </td>
            </tr>
        @endforeach
    @endif

    </tbody>
</table>
<div id="ajax-calls" data-ajax="{{ route('ajax') }}"></div>
@once
    @push('callbacks')
        <script>
          function clickableContact() {
            $('#ajaxable_contacts tbody > tr > td:not(:last-of-type)').off().on('click', function() {
              window.location.assign($(this).parent().find('.mfw-edit-link').attr('href'));
            });
          }

          function dissociateContact() {
            $('a.dissociate').off().on('click', function(e) {
              e.preventDefault();
              let tr = $(this).closest('tr');
              ajax('action=removeAccountFrom{{ ucfirst($queryTag) }}&id=' + tr.data('id') + '&object_id=' + $('#ajaxable_contacts').data('id'), $('#ajax-calls'));
              tr.remove();
            });
          }

          function makeMainContact() {
            $('.btn-make-main-contact').off().on('click', function(e) {
              e.preventDefault();
              let tr = $(this).closest('tr');
              let s = '{!! $eventGroup?'&event_group_id=' . $eventGroup->id:''!!}';

              ajax('action=makeMainContactOfTheGroup' + s + '&user_id=' + tr.data('id'), $('#ajax-calls'), {
                successHandler: function(res) {
                  window.location.reload();
                  return true;
                },
              });
            });
          }

          function actionOnClientList(data) {
            let suggestions = $('#client-search .suggestions'),
              create_btn = suggestions.find('.no-effect a').first();

            if (create_btn.length) {
              create_btn.click(function() {
                window.location.href = $('#client-search').data('create');
              });
            }

            $(document)
              .off('keydown.escNamespace')
              .on('keydown.escNamespace', function(event) {
                if (event.which === 27) {
                  suggestions.remove();
                }
              });

            $('#client-search li').not('.no-effect').off().on('click', function() {
              let item = $(this),
                table = $('#ajaxable_contacts tbody');

              if (table.find('tr.client_' + item.data('id')).length) {
                return false;
              }

              ajax('action=addAccountTo{{ ucfirst($queryTag) }}&id=' + item.data('id') + '&object_id=' + $('#ajaxable_contacts').data('id'), $('#ajax-calls'), {
                successHandler: function(res) {
                  let s =
                    '<tr class="client_' + item.data('id') + '" data-id="' + item.data('id') + '">' +
                    '<td>' + item.data('first-name') + '</td>' +
                    '<td>' + item.data('last-name') + '</td>' +
                    '<td>' + (item.data('function') === null ? '-' : item.data('function')) + '</td>' +
                    '<td>' + item.data('email') + '</td>' +
                    '<td>' + (item.data('locality') ? item.data('locality') : '-') + '</td>' +
                    '<td>' + (item.data('country') ? item.data('country') : '-') + '</td>' +
                          @if($useIsMainContact)
                            '<td>' + (1 === item.data('isMainContact') ? '<i class="bi bi-check-circle" style="color: green;"></i>' : '<i class="bi bi-x-circle" style="color:red"></i>') + '</td>' +
                          @endif
                            '<td>' +
                    '<ul class="mfw-actions">' +
                          @if($useIsMainContact)
                            '<li>' +
                    '<a class="btn-make-main-contact btn btn-danger" ' +
                    'style="color:white;" ' +
                    'href="#" ' +
                    'data-bs-toggle="tooltip" ' +
                    'data-bs-placement="top" ' +
                    'data-bs-title="Rendre contact principal">' +
                    '<i class="fa-solid fa-crown"></i></a>' +
                    '</li>' +
                          @endif
                            '<li>' +
                    '<a class="dissociate btn btn-warning" style="color:#5b5b5b !important;"' +
                    ' href="#" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Dissocier">' +
                    '<i class="fa-solid fa-link-slash"></i></a>' +
                    '</li>' +
                    '<li>' +
                    '<a href="/panel/accounts/' + item.data('id') + '/edit" class="mfw-edit-link btn btn-sm btn-secondary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Éditer">' +
                    '<i class="fas fa-pen"></i></a>' +
                    '</li></td></tr>';
                  table.first().append(s);

                  $('#client-search input').val('');
                  suggestions.remove();
                  dissociateContact();
                  makeMainContact();
                  clickableContact();

                  // window.location.reload();
                  return true;
                },
                errorHandler: function() {
                  suggestions.remove();
                  return true;
                },
              });
            });
          }
        </script>
    @endpush
    @push('js')
        <script>
          dissociateContact();
          makeMainContact();
          clickableContact();
        </script>
        <script src="{{ asset('js/client_search.js') }}"></script>
    @endpush
@endonce
