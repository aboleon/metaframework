@push('css')
    <link rel="stylesheet" href="{{ asset('vendor/jquery-ui-1.13.0.custom/jquery-ui.min.css') }}">
@endpush
@push('js')
    <script src="{{ asset('vendor/jquery-ui-1.13.0.custom/jquery-ui.min.js') }}"></script>
    <script>
      sortableContent = function (container, sortableElement, messages, target = 'meta') {
        // sortableContent($('#sortable-zone'), 'tr', $('#messages'), 'nav');
        // Return a helper with preserved width of cells
        const fixHelper = function (e, ui) {
          ui.children().each(function () {
            $(this).width($(this).width());
          });
          return ui;
        };
        if (container.length) {
          console.log('Has C');
          container.sortable({
            helper: fixHelper,
            delay: 300,
            stop: function (event, ui) {
              let data = [];
              container.find(sortableElement).each(function (index) {
                $(this).attr('data-index', index);
                data.push({
                  'index': index,
                  'id': $(this).data('id'),
                });
              });
                mfwAjax('action=sortable&target=' + target + '&' + $.param({'data': data}), messages);
            },
          });
        } else {

          console.log('Has NOT C', container);
        }
      };
    </script>
    <script>
        $(function () {
            $('.sortables').each(function () {
                let c = $(this);
                $(this).sortable({
                    stop: function (event, ui) {
                        let data = [];
                        c.find('.sortable').each(function (index) {
                            $(this).attr('data-index', index);
                            data.push({
                                'index': index,
                                'id': $(this).data('id'),
                            });
                        });
                        mfwAjax('action=sortable&target='+c.data('target')+
                            '&'+$.param({'data':data}), c);
                    },
                });
            });
        });
    </script>
@endpush
