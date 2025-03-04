<div class="container"  wire:ignore>
    <div id="geocoder" style="margin-bottom:20px; width:300px;"></div>
    <div id="map" style="width: 100%; height: 300px;"></div>
</div>

<script>
    //init map
    var map=null;
    var marker=null;
    document.addEventListener("DOMContentLoaded", function() {
        // Mapbox hozzáférési token
        mapboxgl.accessToken = 'pk.eyJ1Ijoid2ViZWRpdG9yODgiLCJhIjoiY2t3Mjd1ZXgxMXNsYTJ1cWk2ZHZiN2IxMyJ9.YbHW3Qa0mDqDavYn8pXzdg';

        // Térkép inicializálása
        map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/streets-v12',
            center: [19.0402, 47.4979], // Budapest középpont
            zoom: 12
        });

        marker = new mapboxgl.Marker({ draggable: true })
            .setLngLat([19.0402, 47.4979])
            .addTo(map);

        // Kattintás a térképre -> Marker pozíció frissítése + koordináták beírása
        map.on('click', function(e) {
            const { lng, lat } = e.lngLat;
            marker.setLngLat([lng, lat]);

            setCoordinates(lat, lng);
            reverseGeocode(lat, lng);
        });

        // Marker mozgatása -> Koordináták frissítése
        marker.on('dragend', function() {
            const { lng, lat } = marker.getLngLat();

            setCoordinates(lat, lng);
            reverseGeocode(lat, lng);
        });

        // Geocoder inicializálása (helykeresés)
        const geocoder = new MapboxGeocoder({
            accessToken: mapboxgl.accessToken,
            mapboxgl: mapboxgl,
            placeholder: "Keresd meg a címet...",
        });
        document.getElementById('geocoder').appendChild(geocoder.onAdd(map));

        // Esemény figyelése a geocoderben
        geocoder.on('result', function(e) {
            const [lng, lat] = e.result.center; // [lng, lat]
            marker.setLngLat([lng, lat]);
            map.flyTo({ center: [lng, lat], zoom: 14 });

            setCoordinates(lat, lng);
            setAddress(e.result.place_name);
        });

        /**
         * Koordináták beállítása + Filament input event kiváltása
         */
        function setCoordinates(lat, lng) {
            const latitudeInput = document.getElementById('data.latitude');
            const longitudeInput = document.getElementById('data.longitude');

            latitudeInput.value = lat;
            longitudeInput.value = lng;

            // Filament / Livewire akkor érzékeli a változást, ha input eseményt küldünk
            latitudeInput.dispatchEvent(new Event('input'));
            longitudeInput.dispatchEvent(new Event('input'));
        }

        /**
         * Cím beállítása + Filament input event kiváltása
         */
        function setAddress(address) {
            const addressInput = document.getElementById('data.location_address');

            addressInput.value = address;
            // Filament / Livewire akkor érzékeli a változást, ha input eseményt küldünk
            addressInput.dispatchEvent(new Event('input'));
        }

        /**
         * Reverse geocoding: koordinátákból cím keresése (Mapbox API)
         */
        function reverseGeocode(lat, lng) {
            fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/${lng},${lat}.json?access_token=${mapboxgl.accessToken}`)
                .then(response => response.json())
                .then(data => {
                    if (data.features && data.features.length > 0) {
                        const placeName = data.features[0].place_name;
                        setAddress(placeName);
                    } else {
                        setAddress('Nem található cím');
                    }
                })
                .catch(err => console.error('Reverse geocoding error:', err));
        }
    });
    //fly map to points if on edit page settimeot
    function flyToPoints() {
        var latitude = document.getElementById('data.latitude').value;
        var longitude = document.getElementById('data.longitude').value;
        if (latitude && longitude) {
            if(map){
                map.flyTo({
                    center: [longitude, latitude],
                    zoom: 14
                });
                if(marker) {
                    marker.setLngLat([longitude, latitude]);
                }
            }
        }
    }
    setTimeout(() => {
        flyToPoints();
    }, 3000);
</script>
