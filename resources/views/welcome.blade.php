<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affiliates Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        #map { height: 500px; }
    </style>
</head>
<body class="bg-gray-100">
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">Affiliates Map</h1>

    <div id="map" class="w-full rounded-lg shadow-lg mb-6"></div>

    <form id="searchForm" class="bg-white p-6 rounded-lg shadow-md mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="number" id="latSearch" placeholder="Latitude" value="53.3340285" step="any" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="number" id="lonSearch" placeholder="Longitude" value="-6.2535495" step="any" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="number" id="distance" placeholder="Distance (KM)" value="100" step="any" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <button type="submit" class="mt-4 w-full bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300">Search</button>
    </form>

    <div id="closestAffiliates" class="bg-white p-4 rounded-lg shadow-md text-center text-lg font-semibold text-gray-700">

    </div>
</div>

<script>
    const affiliates = @json($affiliates);

    const map = L.map('map').setView([53.1424, -7.6921], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    affiliates.forEach(affiliate => {
        L.marker([affiliate.latitude, affiliate.longitude])
            .addTo(map)
            .bindPopup(affiliate.name);
    });

    document.getElementById('searchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const lat = parseFloat(document.getElementById('latSearch').value);
        const lon = parseFloat(document.getElementById('lonSearch').value);
        const dis = parseFloat(document.getElementById('distance').value) ?? 100;

        if (!isNaN(lat) && !isNaN(lon) && !isNaN(dis)) {
            findClosestAffiliates(lat, lon, dis);
        }
    });

    function updateMap(filteredAffiliates) {
        map.eachLayer(layer => {
            if (layer instanceof L.Marker) {
                map.removeLayer(layer);
            }
        });

        filteredAffiliates.forEach(affiliate => {
            L.marker([affiliate.latitude, affiliate.longitude])
                .addTo(map)
                .bindPopup(affiliate.name);
        });
    }
    function findClosestAffiliates(lat, lon, dis) {
        fetch(`affiliates/find?lat=${lat}&lon=${lon}&dis=${dis}`)
            .then(response => response.json())
            .then(data => {
                //sort by distance
                data.sort((a, b) => a.distance - b.distance);
                document.getElementById('closestAffiliates').innerHTML = data.map(affiliate => {
                    return `<div class="p-2 border-b text-left">${affiliate.name} - ${affiliate.distance} KM</div>`;
                }).join('');

                updateMap(data);
            });
    }
</script>
</body>
</html>
