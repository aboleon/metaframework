<div class="messages" id="{{ $id }}"{!! $ajax ? ' data-ajax="'.$ajax.'"' : '' !!}>
    {!! MetaFramework\Accessors\ResponseParser::parseResponse(session('session_response')) !!}
    @php
    session()->forget('session_response');
    @endphp
</div>
