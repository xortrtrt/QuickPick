async function loadMap() {
    try {
        // Fetch token and locations from backend
        const res = await fetch('../../api/mapbox.php');
        const data = await res.json();

        mapboxgl.accessToken = data.token;

        const map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/streets-v11',
            center: [121.1167, 13.9400], // default center
            zoom: 11
        });

        // Add zoom & rotation controls
        map.addControl(new mapboxgl.NavigationControl());

        // Loop through locations and add markers
        data.locations.forEach(loc => {
            const popup = new mapboxgl.Popup({ offset: 25 })
                .setHTML(`<strong>${loc.name}</strong><br>${loc.details}`);

            new mapboxgl.Marker({ color: 'red' })
                .setLngLat([loc.lng, loc.lat])
                .setPopup(popup)
                .addTo(map);
        });

    } catch (error) {
        console.error('Error loading Mapbox data:', error);
    }
}

// Load map when DOM is ready
document.addEventListener('DOMContentLoaded', loadMap);
