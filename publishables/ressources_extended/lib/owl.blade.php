@once
    @push('css')
        <link rel="stylesheet" href="{{ asset('vendor/owl/dist/assets/owl.carousel.min.css') }}">
        <link rel="stylesheet" href="{{ asset('vendor/owl/dist/assets/owl.theme.default.min.css') }}">
    @endpush
    @push('js')
        <script src="{{ asset('vendor/owl/dist/owl.carousel.min.js') }}"></script>
    @endpush
@endonce
