@if (session('bad_access'))
    <div class="alert alert-warning text-center m-0" style="padding: 25px !important;">
        {{ __('mfw::errors.bad_access.'.session('bad_access')) }}
    </div>
@endif
