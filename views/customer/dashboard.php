<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickPick Dashboard</title>

    <!-- Mapbox CSS -->
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.0.1/mapbox-gl.css" rel="stylesheet">

    <style>
        #map {
            width: 100%;
            height: 500px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <h2>📍 Store Locations</h2>
    <div id="map"></div>

    <!-- Mapbox JS -->
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.0.1/mapbox-gl.js"></script>

    <!-- Custom JS -->
    <script src="../../assets/js/maps.js"></script>
</body>

</html>