<div id="{{ $reference }}"
     class="modal fade {{ $class }}"
     tabindex="-1"
     aria-hidden="true"
     data-bs-backdrop="static"
     data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="{{ $route }}">
                @if (str_contains($reference, 'destroy'))
                    @method('delete')
                @elseif (str_contains($reference, 'put'))
                    @method('put')
                @endif
                @csrf
                <div class="modal-header">
                    <h5>{{ $title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true" aria-label="{{ __('mfw::mfw.close') }}">
                    </button>
                </div>
                <div class="modal-body text-start">
                    <p>{!! $question !!}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel btn btn-sm" data-bs-dismiss="modal" aria-hidden="true">{{ __('mfw::mfw.cancel') }}</button>
                    <button class="btn-confirm btn btn-warning btn-sm">{{ __('mfw::mfw.confirm') }}</button>
                </div>
                @if ($params)
                    @foreach($params as $key=>$value)
                        <input type="hidden" name="{{$key}}" value="{{ $value }}">
                    @endforeach
                @endif
            </form>
        </div>
    </div>
</div>
