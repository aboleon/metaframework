<button type="button" id="mfw-sql-query-toggle" class="btn btn-sm btn-outline-dark ms-2"
    aria-controls="mfw-sql-query-panel" aria-expanded="{{ $result || $errors->has('sql_query') ? 'true' : 'false' }}"
    title="{{ __('mfw::sql.toggle') }}" style="display: none;">
    <i class="bi bi-database"></i> SQL
</button>

<section id="mfw-sql-query-panel" class="card mb-3"
    data-open="{{ $result || $errors->has('sql_query') ? 'true' : 'false' }}" style="display: none;">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span>{{ __('mfw::sql.title') }}</span>
        <button type="button" class="btn-close" data-mfw-sql-query-close
            aria-label="{{ __('mfw::sql.close') }}"></button>
    </div>
    <div class="card-body">
        <form method="post" action="{{ route('mfw.sql-query.execute') }}" autocomplete="off">
            @csrf
            <input type="hidden" name="return_path" value="{{ $returnPath }}">
            <x-mfw-inputable::textarea name="sql_query" :label="__('mfw::sql.query')" :height="110"
                class="font-monospace mfw-sql-query-input" :value="e(old('sql_query', $result?->query ?? ''))" :params="[
                    'placeholder' => __('mfw::sql.placeholder'),
                    'spellcheck' => 'false',
                    'wrap' => 'off',
                ]" />

            <div class="d-flex align-items-center justify-content-between mt-2 gap-3">
                <div>
                    <x-mfw-inputable::checkbox name="sql_query_affects_index" :label="__('mfw::sql.affect_index')" :affected="(bool) old('sql_query_affects_index', $result?->affectsIndex ?? false)"
                        :params="$indexImpactAvailable ? [] : ['disabled' => 'disabled']" />
                    <div class="form-text">
                        {{ $indexImpactAvailable ? __('mfw::sql.help') : __('mfw::sql.index_unavailable') }}
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-play-fill"></i> {{ __('mfw::sql.execute') }}
                </button>
            </div>
        </form>
    </div>

    @if ($result)
        <div class="border-top">
            <div class="d-flex align-items-center justify-content-between px-3 py-2">
                <span>{{ __('mfw::sql.results') }}</span>
                <span class="badge text-bg-secondary">{{ count($result->rows) }}</span>
            </div>
            <div class="table-responsive">
                @if ($result->error)
                    <div class="alert alert-danger rounded-0 mb-0">{{ $result->error }}</div>
                @elseif ($result->columns === [])
                    <div class="text-muted p-3">{{ __('mfw::sql.no_results') }}</div>
                @else
                    <table class="table-striped table-hover table-sm mb-0 table">
                        <thead>
                            <tr>
                                @foreach ($result->columns as $column)
                                    <th scope="col" class="font-monospace">{{ $column }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($result->rows as $row)
                                <tr>
                                    @foreach ($result->columns as $column)
                                        <td class="font-monospace text-nowrap">{{ $row[$column] ?? 'NULL' }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
            @if ($result->truncated)
                <div class="card-footer text-warning">
                    {{ __('mfw::sql.truncated', ['limit' => 500]) }}
                </div>
            @endif
        </div>
    @endif
</section>

@pushonce('css')
    <style>
        .mfw-sql-query-input {
            font-size: .875rem;
            line-height: 1.5;
            tab-size: 4;
            white-space: pre;
        }
    </style>
@endpushonce

@pushonce('js')
    <script>
        $(function() {
            const toggle = $('#mfw-sql-query-toggle');
            const panel = $('#mfw-sql-query-panel');
            const topbar = $('#topbar');

            if (topbar.length) {
                if (!toggle.closest(topbar).length) {
                    toggle.appendTo(topbar);
                }
                panel.insertAfter(topbar);
            }
            toggle.show();
            if (panel.data('open')) {
                panel.show();
            }

            toggle.on('click', function() {
                panel.stop(true, true).slideToggle(180, function() {
                    toggle.attr('aria-expanded', panel.is(':visible') ? 'true' : 'false');
                });
            });

            $('[data-mfw-sql-query-close]').on('click', function() {
                panel.stop(true, true).slideUp(180);
                toggle.attr('aria-expanded', 'false');
            });
        });
    </script>
@endpushonce
