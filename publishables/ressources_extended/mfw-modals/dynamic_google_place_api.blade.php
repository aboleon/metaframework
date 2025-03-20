<script src="{{ asset('vendor/mfw/components/google-places-geolocate.js') }}"></script>
<script>
    setTimeout(function () {
        function isGoogleMapsApiLoaded() {
            return typeof google !== 'undefined' && typeof google.maps !== 'undefined';
        }

        if (isGoogleMapsApiLoaded()) {
            delete google.maps;
        }
        if (!isGoogleMapsApiLoaded()) {
            $.getScript("https://maps.googleapis.com/maps/api/js?key={{ config('mfw.google_places_api_key') }}&libraries=places&callback=initialize");
        }
    }, 1000);
</script>
