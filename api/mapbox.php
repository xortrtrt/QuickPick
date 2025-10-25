<?php
header("Content-Type: application/json");

$mapboxToken = "pk.eyJ1IjoicXVpY2twaWNrLWFkbWluIiwiYSI6ImNtaDZidzJ2ajBkd20yanM3bG92am1pNWMifQ.8etGH_JhqWwCv3uJaYgQ8Q";

// Example: sample locations (replace with SELECT * FROM stores)
$locations = [
    [
        "name" => "QuickPick Lipa City",
        "lng" => 121.1167,
        "lat" => 13.9400,
        "details" => "Main branch in Lipa City"
    ],
    [
        "name" => "QuickPick Batangas City",
        "lng" => 121.0583,
        "lat" => 13.7567,
        "details" => "Pickup hub at SM Batangas"
    ]
];

// Return JSON for frontend
echo json_encode([
    "token" => $mapboxToken,
    "locations" => $locations
]);
