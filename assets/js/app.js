// Base URL for API endpoints
const API_BASE_URL = 'api'; // Uses relative path, works on localhost and live servers

/**
 * Utility function to show alert messages
 */
function showAlert(elementId, message, isError = true) {
    const alertEl = document.getElementById(elementId);
    alertEl.textContent = message;
    alertEl.style.display = 'block';

    if (isError) {
        alertEl.classList.remove('alert-success');
        alertEl.classList.add('alert-danger');
    } else {
        alertEl.classList.remove('alert-danger');
        alertEl.classList.add('alert-success');
    }

    // Hide after 5 seconds
    setTimeout(() => {
        alertEl.style.display = 'none';
    }, 5000);
}

/**
 * Helper function to ask the browser for exact GPS/Wi-Fi location
 */
function getExactLocation() {
    return new Promise((resolve) => {
        if (!navigator.geolocation) {
            resolve({ lat: null, lon: null });
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
                console.warn("Geolocation failed/denied, falling back to IP:", error.message);
                resolve({ lat: null, lon: null });
            },
            { timeout: 5000, enableHighAccuracy: true }
        );
    });
}

/**
 * Handle Login Form Submission
 */
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const username_or_email = document.getElementById('username_or_email').value;
        const password = document.getElementById('password').value;
        const btn = loginForm.querySelector('button[type="submit"]');
        const originalBtnText = btn.innerHTML;

        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Authenticating...';
        btn.disabled = true;

        try {
            // Ask for exact location first
            const exactLocation = await getExactLocation();
            
            let exactAddress = null;
            if (exactLocation.lat && exactLocation.lon) {
                try {
                    const nom = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${exactLocation.lat}&lon=${exactLocation.lon}`);
                    const nomData = await nom.json();
                    if (nomData && nomData.display_name) {
                        exactAddress = nomData.display_name;
                    }
                } catch(e) {
                    console.error("Reverse geocoding failed during login:", e);
                }
            }

            const response = await fetch(`${API_BASE_URL}/auth/login.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ 
                    username_or_email, 
                    password,
                    exact_lat: exactLocation.lat,
                    exact_lon: exactLocation.lon,
                    exact_address: exactAddress
                }) 
            });

            const data = await response.json();

            if (response.ok && data && data.status === 'success') {
                showAlert('loginAlert', 'Login successful! Redirecting to Dashboard...', false);
                
                // Redirect to the new dashboard
                setTimeout(() => { window.location.href = 'dashboard.php'; }, 1500);
            } else {
                showAlert('loginAlert', data.message || 'Login failed.');
                btn.innerHTML = originalBtnText;
                btn.disabled = false;
            }
        } catch (error) {
            showAlert('loginAlert', 'Network error occurred. Please try again.');
            btn.innerHTML = originalBtnText;
            btn.disabled = false;
        }
    });
}

/**
 * Handle Signup Form Submission
 */
const signupForm = document.getElementById('signupForm');
if (signupForm) {
    signupForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const username = document.getElementById('username').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const btn = signupForm.querySelector('button[type="submit"]');
        const originalBtnText = btn.innerHTML;

        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Creating Account...';
        btn.disabled = true;

        try {
            const response = await fetch(`${API_BASE_URL}/auth/signup.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ username, email, password })
            });

            const data = await response.json();

            if (response.ok && data && data.status === 'success') {
                showAlert('signupAlert', 'Account created successfully! You can now login.', false);
                signupForm.reset();
                setTimeout(() => { window.location.href = 'index.php'; }, 2000);
            } else {
                showAlert('signupAlert', (data && data.message) ? data.message : 'Signup failed.');
            }
        } catch (error) {
            showAlert('signupAlert', 'Network error occurred. Please try again.');
        } finally {
            btn.innerHTML = originalBtnText;
            btn.disabled = false;
        }
    });
}

/**
 * Demo fetching IP tracking info using the separate API
 */
async function fetchIPInfo() {
    try {
        const response = await fetch(`${API_BASE_URL}/tracking/get-ip-info.php`);
        const data = await response.json();

        if (response.ok) {
            console.log("IP Info Retrieved:", data);

            // Optionally display this on the page to prove it works
            const alertEl = document.getElementById('loginAlert');
            if (data.geo && data.geo.status === 'success') {
                alertEl.textContent = `Welcome! Login logged from IP: ${data.ip} (${data.geo.city}, ${data.geo.country})`;
            } else {
                alertEl.textContent = `Welcome! Login logged from IP: ${data.ip}`;
            }
            alertEl.classList.remove('alert-danger');
            alertEl.classList.add('alert-success');
            alertEl.style.display = 'block';
        }
    } catch (error) {
        console.error("Could not fetch IP info:", error);
    }
}
