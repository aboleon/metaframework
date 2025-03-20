<x-pdf-layout>

        {!! Mediaclass::forModel($parsed->event())->render() !!}
        {!! $parsed->content()['content'] !!}

</x-pdf-layout>
