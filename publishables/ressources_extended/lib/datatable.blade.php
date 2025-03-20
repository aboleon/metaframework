@push('css')
    <link href="https://cdn.datatables.net/v/bs5/dt-1.13.8/b-2.3.6/r-2.4.1/datatables.min.css"
          rel="stylesheet" />
@endpush
@push('js')
    <script src="https://cdn.datatables.net/v/bs5/dt-1.13.8/b-2.3.6/r-2.4.1/datatables.min.js"></script>
    <script>
      function DTclickableRow() {
        setTimeout(function() {
          $('.dt.dataTable tbody > tr > td:not(:nth-child(0)):not(:nth-child(1)):not(:last-of-type):not(.unclickable)').css('cursor', 'pointer').click(function() {
            if (0 === $(this).closest('.datatable-not-clickable').length) {
              window.location.assign($(this).parent().find('a.mfw-edit-link').attr('href'));
            }
          });
        }, 1000);
      }

      DTclickableRow();

      $('.dt').on('draw.dt', function() {
        DTclickableRow();
      });
    </script>
@endpush

