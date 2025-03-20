<x-mail-template>
{!! Mediaclass::forModel($parsed->event())->render() !!}
{!! $parsed->content()['content'] !!}
</x-mail-template>

