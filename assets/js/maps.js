mapboxgl.accessToken = 'pk.eyJ1IjoicXVpY2twaWNrLWFkbWluIiwiYSI6ImNtaDZidzJ2ajBkd20yanM3bG92am1pNWMifQ.8etGH_JhqWwCv3uJaYgQ8Q';

// Initialize map
const map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/streets-v11',
    center: [121.236955, 13.844793],
    zoom: 15
});

(async () => {
    try {
        const response = await fetch('../../api/mapbox.php');
        const text = await response.text();

        let data;
        try {
            data = JSON.parse(text);
        } catch (err) {
            console.error("❌ Invalid JSON from backend:", text);
            return;
        }

        if (!data.success || !Array.isArray(data.data) || data.data.length === 0) {
            console.warn("⚠️ No store locations found in backend:", data);
            return;
        }

        const bounds = new mapboxgl.LngLatBounds();

        data.data.forEach(loc => {
            const coords = [parseFloat(loc.longitude), parseFloat(loc.latitude)];
            const popupHTML = `
                <b>${loc.name}</b><br>
                ${loc.details || ''}<br>
                <i>(${loc.latitude}, ${loc.longitude})</i>
            `;

            new mapboxgl.Marker({ color: 'blue' })
                .setLngLat(coords)
                .setPopup(new mapboxgl.Popup().setHTML(popupHTML))
                .addTo(map);

            bounds.extend(coords);
            
        });

    } catch (error) {
        console.error("Fetch error:", error);
    }
})();

// Add navigation and fullscreen controls
map.addControl(new mapboxgl.NavigationControl());
map.addControl(new mapboxgl.FullscreenControl());

// Try to get the user’s exact current location
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
        (position) => {
            const userLng = position.coords.longitude;
            const userLat = position.coords.latitude;

            // Center map on user's current position
            map.flyTo({
                center: [userLng, userLat],
                zoom: 15,
                essential: true
            });

            // Add marker for user's current location
            new mapboxgl.Marker({
                color: "#ff1e1eff"
            })
                .setLngLat([userLng, userLat])
                .setPopup(new mapboxgl.Popup().setHTML("<b>You are here</b>"))
                .addTo(map);

            console.log("User location detected:", userLng, userLat);

            
        },
        (error) => {
            console.warn("Geolocation failed:", error.message);
            alert("Unable to retrieve your location. Please allow location access or set manually.");
        },
        {
            enableHighAccuracy: true, 
            timeout: 10000,         
            maximumAge: 0
        }
    );
} else {
    alert("Geolocation is not supported by your browser.");
}