mapboxgl.accessToken = 'pk.eyJ1IjoicXVpY2twaWNrLWFkbWluIiwiYSI6ImNtaDZidzJ2ajBkd20yanM3bG92am1pNWMifQ.8etGH_JhqWwCv3uJaYgQ8Q';

// Initialize map
const map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/streets-v11',
    center: [121.236955, 13.844793], // Default center
    zoom: 12
});

// Add default pinned location (main QuickPick store)
const mainLocation = {
    name: 'QuickPick Lipa City',
    details: 'Main Pickup Location',
    longitude: 121.236955,
    latitude: 13.844793
};

// red default pin
new mapboxgl.Marker({ color: 'red' })
    .setLngLat([mainLocation.longitude, mainLocation.latitude])
    .setPopup(
        new mapboxgl.Popup().setHTML(`
            <b>${mainLocation.name}</b><br>${mainLocation.details}
        `)
    )
    .addTo(map);

// Fetch additional store locations from backend
fetch('/api/mapbox.php')
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('Map data load failed:', data);
            return;
        }

        // Add blue pins for each store in the database
        data.data.forEach(loc => {
            new mapboxgl.Marker({ color: 'blue' })
                .setLngLat([loc.longitude, loc.latitude])
                .setPopup(
                    new mapboxgl.Popup().setHTML(`
                        <b>${loc.name}</b><br>
                        ${loc.details}<br>
                        <i>(${loc.latitude}, ${loc.longitude})</i>
                    `)
                )
                .addTo(map);
        });
    })
    .catch(error => console.error('Fetch error:', error));
