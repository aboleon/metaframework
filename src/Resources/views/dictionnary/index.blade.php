
<x-backend-layout>
    <x-slot name="header">
        <h2>
            {{ trans_choice('ui.dictionnary.label',2) }}
        </h2>
        @role('dev')
        <x-back.topbar.list-combo
                route-prefix="panel.dictionnary"
                :create-btn-dev-mark="true"
        />
        @endrole
    </x-slot>

    <x-mfw::devnotice />
    <div class="shadow p-4 bg-body-tertiary rounded">
        <x-datatables-mass-delete model="Dictionnary" />
        {!! $dataTable->table()  !!}
    </div>
    @include('templates.devmark')
    @include('lib.datatable')
    @push('js')
        {{ $dataTable->scripts() }}

        <script>
          setTimeout(function() {
            let dt = $('#dictionnary-table'),
              th = dt.find('th.type, th.slug');
            if (th.length) {
              th.attr('data-bs-toggle', 'tooltip')
                .attr('data-bs-placement', 'left')
                .append($('template#devmark').html())
                .end()
                .find('th.type').attr('data-bs-title', 'Meta - dict.à catégories')
                .end()
                .find('th.slug')
                .attr('data-bs-html', 'true')
                .attr('data-bs-title', '<p style=\'text-center\'>Pas obligatoire.<br/>Prévu pour travailler avec:<br/>- Cache<br/>- Dictionnary->entrySubClass</p>');

              const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
              const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
            }
          }, 500);

          // override default datatable row click
          function DTclickableRow() {
            setTimeout(function() {
              $('.dt.dataTable tbody > tr > td:not(:nth-child(0)):not(:nth-child(1)):not(:last-of-type):not(.unclickable)').css('cursor', 'pointer').click(function() {
                window.location.assign($(this).parent().find('a.action-entries-index').attr('href'));
              });
            }, 1000);
          }

          DTclickableRow();

          $('.dt').on('draw.dt', function() {
            DTclickableRow();
          });

        </script>

    @endpush
</x-backend-layout>
