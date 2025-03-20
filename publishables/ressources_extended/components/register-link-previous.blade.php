<!-- resources/views/components/register-link.blade.php -->
<a href="{{  route('front.register-public-account-form', [
            'locale' => app()->getLocale(),
            'token' => $token,
            'event_group_id' => $eventGroupId,
            'step' => $step,
        ]) }}"
   class="btn btn-secondary next-btn mb-0">
    {{ __('front/register.previous') }}
</a>
