// Base URL for API endpoints (should match app.js)
const API_BASE_URL = 'api'; // Uses relative path, works on localhost and live servers 

document.addEventListener('DOMContentLoaded', () => {
    fetchDashboardData();

    document.getElementById('logoutBtn').addEventListener('click', () => {
        // In a real app with sessions, you'd call a logout API here.
        // For our demo, we just redirect back to the login page.
        window.location.href = 'index.php';
    });
});

/**
 * Helper function to ask the browser for exact GPS/Wi-Fi location
 */
function getExactLocation() {
    return new Promise((resolve) => {
        if (!navigator.geolocation) {
            resolve(null);
            return;
        }
        
        navigator.geolocation.getCurrentPosition(
            (position) => {
                resolve({ 
                    lat: position.coords.latitude, 
                    lon: position.coords.longitude 
                });
            },
            (error) => {
                console.warn("Geolocation failed/denied:", error.message);
                resolve(null);
            },
            { timeout: 5000, enableHighAccuracy: true }
        );
    });
}

async function fetchDashboardData() {
    try {
        const response = await fetch(`${API_BASE_URL}/tracking/get-ip-info.php`);
        const data = await response.json();
        
        const exactLocation = await getExactLocation();
        
        if (response.ok && data.geo) {
            populateDashboard(data.ip, data.geo, exactLocation);
        } else {
            showError("Could not retrieve location data.");
        }
    } catch (error) {
        console.error("Dashboard fetch error:", error);
        showError("Network error while fetching data.");
    }
}

function populateDashboard(ip, geo, exactLocation) {
    document.getElementById('loadingIndicator').style.display = 'none';
    document.getElementById('dashboardContent').style.display = 'block';

    document.getElementById('displayIp').textContent = ip || 'Unknown';
    
    if (geo.status === 'success') {
        document.getElementById('displayIsp').textContent = geo.isp || geo.org || 'Unknown';
        document.getElementById('displayCountry').textContent = geo.country || 'Unknown';
        document.getElementById('displayRegion').textContent = geo.regionName || geo.region || 'Unknown';
        document.getElementById('displayCity').textContent = geo.city || 'Unknown';
        document.getElementById('displayZip').textContent = geo.zip || 'Unknown';
        document.getElementById('displayTimezone').textContent = geo.timezone || 'Unknown';
        
        let lat = geo.lat || 0;
        let lon = geo.lon || 0;

        if (exactLocation) {
            lat = exactLocation.lat;
            lon = exactLocation.lon;
            
            // Try to reverse geocode using Nominatim (OpenStreetMap)
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`)
                .then(res => res.json())
                .then(nomData => {
                    if (nomData && nomData.display_name) {
                        document.getElementById('exactAddressContainer').style.display = 'block';
                        document.getElementById('displayExactAddress').textContent = nomData.display_name;
                    }
                })
                .catch(e => console.error("Reverse geocoding failed", e));
        }

        document.getElementById('displayCoords').textContent = `${lat}, ${lon}`;

        // Update the iframe to show an OpenStreetMap of the coordinates
        // Zoom level 15 is good for street-level view if we have GPS, else 13 for city
        const zoom = exactLocation ? 17 : 13;
        const diff = exactLocation ? 0.005 : 0.05;
        const mapUrl = `https://www.openstreetmap.org/export/embed.html?bbox=${lon-diff},${lat-diff},${lon+diff},${lat+diff}&layer=mapnik&marker=${lat},${lon}`;
        document.getElementById('mapFrame').src = mapUrl;

    } else {
        showError("Geo-location lookup failed for this IP.");
    }
}

function showError(msg) {
    document.getElementById('loadingIndicator').innerHTML = `<p class="text-danger">Error: ${msg}</p>`;
}
