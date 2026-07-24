// Base URL for API endpoints (should match app.js)
const API_BASE_URL = 'api'; // Uses relative path, works on localhost and live servers 

document.addEventListener('DOMContentLoaded', () => {
    fetchDashboardData();

    document.getElementById('logoutBtn').addEventListener('click', () => {
        // In a real app with sessions, you'd call a logout API here.
        // For our demo, we just redirect back to the login page.
        window.location.href = 'index.html';
    });
});

async function fetchDashboardData() {
    try {
        const response = await fetch(`${API_BASE_URL}/tracking/get-ip-info.php`);
        const data = await response.json();
        
        if (response.ok && data.geo) {
            populateDashboard(data.ip, data.geo);
        } else {
            showError("Could not retrieve location data.");
        }
    } catch (error) {
        console.error("Dashboard fetch error:", error);
        showError("Network error while fetching data.");
    }
}

function populateDashboard(ip, geo) {
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
        
        const lat = geo.lat || 0;
        const lon = geo.lon || 0;
        document.getElementById('displayCoords').textContent = `${lat}, ${lon}`;

        // Update the iframe to show an OpenStreetMap of the coordinates
        // Zoom level 13 is good for city-level view
        const mapUrl = `https://www.openstreetmap.org/export/embed.html?bbox=${lon-0.05},${lat-0.05},${lon+0.05},${lat+0.05}&layer=mapnik&marker=${lat},${lon}`;
        document.getElementById('mapFrame').src = mapUrl;

    } else {
        showError("Geo-location lookup failed for this IP.");
    }
}

function showError(msg) {
    document.getElementById('loadingIndicator').innerHTML = `<p class="text-danger">Error: ${msg}</p>`;
}
