<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pickup Locations | QuickPick</title>
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.1.2/mapbox-gl.css" rel="stylesheet" />

    <style>
        body {
            margin: 0;
            font-family: "Poppins", sans-serif;
            background: #f4f4f4;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: start;
            min-height: 100vh;
            padding: 20px;
        }

        h2 {
            color: #2193b0;
            margin-bottom: 10px;
        }

        #map {
            width: 90%;
            height: 450px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            margin-bottom: 20px;
        }

        .btn {
            background: #2193b0;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            margin: 5px;
        }

        .btn:hover {
            background: #6dd5ed;
        }
    </style>
</head>

<body>
    <h2>📍 Available Pickup Locations</h2>
    <p>View all QuickPick branches and their pickup hubs on the map below.</p>

    <div id="map"></div>

    <button class="btn" onclick="window.location.href='dashboard.php'">Back</button>

    <!-- Mapbox JS -->
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.1.2/mapbox-gl.js"></script>

    <!-- Custom Map Script -->
    <script src="../../assets/js/maps.js"></script>
</body>

</html>