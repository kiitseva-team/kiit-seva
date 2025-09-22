<?php
$pageTitle = 'Vehicle Tracking';
require_once '../includes/header.php';
require_login();

// Handle GPS location updates (for staff/admin updating vehicle locations)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'update_location') {
        $vehicle_id = (int)$_POST['vehicle_id'];
        $latitude = (float)$_POST['latitude'];
        $longitude = (float)$_POST['longitude'];
        
        try {
            // Update vehicle location
            $db->query('UPDATE vehicles SET latitude = :latitude, longitude = :longitude, last_updated = NOW() 
                       WHERE id = :vehicle_id');
            $db->bind(':latitude', $latitude);
            $db->bind(':longitude', $longitude);
            $db->bind(':vehicle_id', $vehicle_id);
            $db->execute();
            
            // Add to location history
            $db->query('INSERT INTO vehicle_locations (vehicle_id, latitude, longitude) 
                       VALUES (:vehicle_id, :latitude, :longitude)');
            $db->bind(':vehicle_id', $vehicle_id);
            $db->bind(':latitude', $latitude);
            $db->bind(':longitude', $longitude);
            $db->execute();
            
            echo json_encode(['success' => true, 'message' => 'Location updated successfully']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to update location']);
        }
        exit();
    } elseif ($_POST['action'] === 'get_vehicles') {
        try {
            $db->query('SELECT v.*, 
                              TIMESTAMPDIFF(MINUTE, v.last_updated, NOW()) as minutes_ago
                       FROM vehicles v 
                       WHERE v.status = "active" 
                       ORDER BY v.vehicle_type, v.route_name');
            $vehicles = $db->resultSet();
            echo json_encode(['success' => true, 'vehicles' => $vehicles]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to fetch vehicles']);
        }
        exit();
    }
}

// Get all active vehicles
try {
    $db->query('SELECT v.*, 
                      TIMESTAMPDIFF(MINUTE, v.last_updated, NOW()) as minutes_ago
               FROM vehicles v 
               WHERE v.status = "active" 
               ORDER BY v.vehicle_type, v.route_name');
    $vehicles = $db->resultSet();
} catch (Exception $e) {
    $vehicles = [];
}

// Get vehicle routes for selected vehicle
$selected_vehicle_routes = [];
if (isset($_GET['vehicle_id'])) {
    try {
        $db->query('SELECT * FROM vehicle_routes WHERE vehicle_id = :vehicle_id ORDER BY stop_order');
        $db->bind(':vehicle_id', (int)$_GET['vehicle_id']);
        $selected_vehicle_routes = $db->resultSet();
    } catch (Exception $e) {
        $selected_vehicle_routes = [];
    }
}

$user_role = getUserRole();
?>

<section class="section">
    <div class="container">
        <div class="level">
            <div class="level-left">
                <div class="level-item">
                    <div>
                        <h1 class="title is-2">
                            <i class="mdi mdi-map-marker-radius has-text-info"></i> 
                            Vehicle Tracking
                        </h1>
                        <p class="subtitle is-5">Real-time location tracking of campus vehicles</p>
                    </div>
                </div>
            </div>
            <div class="level-right">
                <div class="level-item">
                    <button class="button is-info" id="refreshBtn">
                        <i class="mdi mdi-refresh"></i>&nbsp; Refresh
                    </button>
                    <?php if ($user_role === 'admin' || $user_role === 'staff'): ?>
                        <button class="button is-primary" id="updateLocationBtn">
                            <i class="mdi mdi-crosshairs-gps"></i>&nbsp; Update My Location
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="columns">
            <!-- Vehicle List -->
            <div class="column is-one-third">
                <div class="box">
                    <h4 class="title is-4">
                        <i class="mdi mdi-format-list-bulleted"></i> Available Vehicles
                    </h4>
                    
                    <div id="vehicle-list">
                        <?php if (empty($vehicles)): ?>
                            <div class="notification is-info is-light">
                                <i class="mdi mdi-information"></i> No active vehicles found.
                            </div>
                        <?php else: ?>
                            <?php foreach ($vehicles as $vehicle): ?>
                                <div class="box vehicle-card" data-vehicle-id="<?php echo $vehicle->id; ?>" 
                                     data-lat="<?php echo $vehicle->latitude; ?>" 
                                     data-lng="<?php echo $vehicle->longitude; ?>"
                                     style="cursor: pointer; margin-bottom: 1rem;">
                                    <div class="media">
                                        <div class="media-left">
                                            <figure class="image is-48x48">
                                                <i class="mdi mdi-<?php echo $vehicle->vehicle_type === 'bus' ? 'bus' : 'car'; ?> 
                                                         is-size-2 has-text-<?php echo $vehicle->vehicle_type === 'bus' ? 'primary' : 'info'; ?>"></i>
                                            </figure>
                                        </div>
                                        <div class="media-content">
                                            <p class="title is-6"><?php echo htmlspecialchars($vehicle->vehicle_number); ?></p>
                                            <p class="subtitle is-7">
                                                <?php echo htmlspecialchars($vehicle->route_name ?: 'No route assigned'); ?>
                                            </p>
                                            <div class="tags">
                                                <span class="tag is-small vehicle-status <?php echo $vehicle->status; ?>">
                                                    <?php echo ucfirst($vehicle->status); ?>
                                                </span>
                                                <span class="tag is-small is-light">
                                                    <?php 
                                                    if ($vehicle->minutes_ago < 1) {
                                                        echo 'Just now';
                                                    } elseif ($vehicle->minutes_ago < 60) {
                                                        echo $vehicle->minutes_ago . ' min ago';
                                                    } else {
                                                        echo floor($vehicle->minutes_ago / 60) . 'h ago';
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if ($vehicle->driver_name): ?>
                                        <div class="content is-small">
                                            <p><strong>Driver:</strong> <?php echo htmlspecialchars($vehicle->driver_name); ?></p>
                                            <?php if ($vehicle->driver_contact): ?>
                                                <p><strong>Contact:</strong> 
                                                   <a href="tel:<?php echo $vehicle->driver_contact; ?>">
                                                       <?php echo htmlspecialchars($vehicle->driver_contact); ?>
                                                   </a>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Vehicle Selection for Location Update (Staff/Admin only) -->
                <?php if ($user_role === 'admin' || $user_role === 'staff'): ?>
                    <div class="box">
                        <h5 class="title is-5">
                            <i class="mdi mdi-cog"></i> Update Vehicle Location
                        </h5>
                        <div class="field">
                            <label class="label">Select Vehicle</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select id="vehicleSelect">
                                        <option value="">Choose a vehicle to update</option>
                                        <?php foreach ($vehicles as $vehicle): ?>
                                            <option value="<?php echo $vehicle->id; ?>">
                                                <?php echo htmlspecialchars($vehicle->vehicle_number . ' - ' . $vehicle->route_name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button class="button is-success is-fullwidth" id="updateSelectedVehicleBtn" disabled>
                            <i class="mdi mdi-crosshairs-gps"></i>&nbsp; Update Selected Vehicle Location
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Map Display -->
            <div class="column is-two-thirds">
                <div class="box">
                    <h4 class="title is-4">
                        <i class="mdi mdi-map"></i> Live Vehicle Map
                    </h4>
                    
                    <div id="map" class="map-container">
                        <div class="notification is-info">
                            <i class="mdi mdi-loading mdi-spin"></i> Loading map...
                        </div>
                    </div>
                </div>

                <!-- Vehicle Details Panel -->
                <div class="box" id="vehicle-details" style="display: none;">
                    <h5 class="title is-5">
                        <i class="mdi mdi-information"></i> Vehicle Details
                    </h5>
                    <div id="vehicle-info"></div>
                </div>

                <!-- Route Information -->
                <?php if (!empty($selected_vehicle_routes)): ?>
                    <div class="box">
                        <h5 class="title is-5">
                            <i class="mdi mdi-map-marker-path"></i> Route Stops
                        </h5>
                        <div class="table-container">
                            <table class="table is-fullwidth is-striped">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Stop Name</th>
                                        <th>Estimated Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($selected_vehicle_routes as $route): ?>
                                        <tr>
                                            <td><?php echo $route->stop_order; ?></td>
                                            <td><?php echo htmlspecialchars($route->stop_name); ?></td>
                                            <td><?php echo date('g:i A', strtotime($route->estimated_time)); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Loading Modal -->
<div class="modal" id="loadingModal">
    <div class="modal-background"></div>
    <div class="modal-content">
        <div class="box has-text-centered">
            <i class="mdi mdi-loading mdi-spin is-size-1 has-text-primary"></i>
            <p class="mt-3">Updating location...</p>
        </div>
    </div>
</div>

<script>
let map;
let vehicleMarkers = {};
let infoWindow;

// Initialize map
function initMap() {
    // Default location: KIIT University
    const defaultLocation = { lat: 20.3554, lng: 85.8315 };
    
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 13,
        center: defaultLocation,
        styles: [
            {
                featureType: 'poi',
                elementType: 'labels',
                stylers: [{ visibility: 'off' }]
            }
        ]
    });

    infoWindow = new google.maps.InfoWindow();

    // Load vehicle markers
    loadVehicleMarkers();
    
    // Set up auto-refresh
    setInterval(refreshVehicles, 30000); // Refresh every 30 seconds
}

// Load vehicle markers on map
function loadVehicleMarkers() {
    <?php foreach ($vehicles as $vehicle): ?>
        <?php if ($vehicle->latitude && $vehicle->longitude): ?>
            addVehicleMarker({
                id: <?php echo $vehicle->id; ?>,
                lat: <?php echo $vehicle->latitude; ?>,
                lng: <?php echo $vehicle->longitude; ?>,
                vehicle_number: '<?php echo addslashes($vehicle->vehicle_number); ?>',
                route_name: '<?php echo addslashes($vehicle->route_name ?: 'No route'); ?>',
                vehicle_type: '<?php echo $vehicle->vehicle_type; ?>',
                status: '<?php echo $vehicle->status; ?>',
                driver_name: '<?php echo addslashes($vehicle->driver_name ?: 'N/A'); ?>',
                driver_contact: '<?php echo addslashes($vehicle->driver_contact ?: ''); ?>',
                last_updated: '<?php echo $vehicle->last_updated; ?>',
                minutes_ago: <?php echo $vehicle->minutes_ago; ?>
            });
        <?php endif; ?>
    <?php endforeach; ?>
}

// Add vehicle marker to map
function addVehicleMarker(vehicle) {
    const position = { lat: vehicle.lat, lng: vehicle.lng };
    
    const icon = {
        url: vehicle.vehicle_type === 'bus' ? 
             'data:image/svg+xml;base64,' + btoa(`<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="#3273dc"><path d="M18,11H6V6H18M16.5,17A1.5,1.5 0 0,1 15,15.5A1.5,1.5 0 0,1 16.5,14A1.5,1.5 0 0,1 18,15.5A1.5,1.5 0 0,1 16.5,17M7.5,17A1.5,1.5 0 0,1 6,15.5A1.5,1.5 0 0,1 7.5,14A1.5,1.5 0 0,1 9,15.5A1.5,1.5 0 0,1 7.5,17M4,16C4,16.88 4.39,17.67 5,18.22V20A1,1 0 0,0 6,21H7A1,1 0 0,0 8,20V19H16V20A1,1 0 0,0 17,21H18A1,1 0 0,0 19,20V18.22C19.61,17.67 20,16.88 20,16V6C20,2.5 16.42,2 12,2C7.58,2 4,2.5 4,6V16Z"/></svg>`) :
             'data:image/svg+xml;base64,' + btoa(`<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="#48c774"><path d="M5,11L6.5,6.5H17.5L19,11M17.5,16A1.5,1.5 0 0,1 16,14.5A1.5,1.5 0 0,1 17.5,13A1.5,1.5 0 0,1 19,14.5A1.5,1.5 0 0,1 17.5,16M6.5,16A1.5,1.5 0 0,1 5,14.5A1.5,1.5 0 0,1 6.5,13A1.5,1.5 0 0,1 8,14.5A1.5,1.5 0 0,1 6.5,16M18.92,6C18.72,5.42 18.16,5 17.5,5H6.5C5.84,5 5.28,5.42 5.08,6L3,12V20A1,1 0 0,0 4,21H5A1,1 0 0,0 6,20V19H18V20A1,1 0 0,0 19,21H20A1,1 0 0,0 21,20V12L18.92,6Z"/></svg>`),
        scaledSize: new google.maps.Size(32, 32)
    };

    const marker = new google.maps.Marker({
        position: position,
        map: map,
        title: vehicle.vehicle_number,
        icon: icon
    });

    const contentString = `
        <div style="max-width: 300px;">
            <h6 style="margin-bottom: 10px; color: #3273dc; font-weight: bold;">
                ${vehicle.vehicle_number}
            </h6>
            <p><strong>Route:</strong> ${vehicle.route_name}</p>
            <p><strong>Type:</strong> ${vehicle.vehicle_type.charAt(0).toUpperCase() + vehicle.vehicle_type.slice(1)}</p>
            <p><strong>Status:</strong> <span style="color: ${vehicle.status === 'active' ? 'green' : 'red'}">${vehicle.status.charAt(0).toUpperCase() + vehicle.status.slice(1)}</span></p>
            <p><strong>Driver:</strong> ${vehicle.driver_name}</p>
            ${vehicle.driver_contact ? `<p><strong>Contact:</strong> <a href="tel:${vehicle.driver_contact}">${vehicle.driver_contact}</a></p>` : ''}
            <p><strong>Last Updated:</strong> ${vehicle.minutes_ago < 1 ? 'Just now' : 
                vehicle.minutes_ago < 60 ? vehicle.minutes_ago + ' minutes ago' : 
                Math.floor(vehicle.minutes_ago / 60) + ' hours ago'}</p>
        </div>
    `;

    marker.addListener('click', () => {
        infoWindow.setContent(contentString);
        infoWindow.open(map, marker);
        
        // Show vehicle details panel
        showVehicleDetails(vehicle);
        
        // Highlight vehicle card
        highlightVehicleCard(vehicle.id);
    });

    vehicleMarkers[vehicle.id] = marker;
}

// Show vehicle details panel
function showVehicleDetails(vehicle) {
    const detailsPanel = document.getElementById('vehicle-details');
    const infoDiv = document.getElementById('vehicle-info');
    
    infoDiv.innerHTML = `
        <div class="columns">
            <div class="column">
                <div class="field">
                    <label class="label">Vehicle Number</label>
                    <div class="control">
                        <input class="input" type="text" value="${vehicle.vehicle_number}" readonly>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Route</label>
                    <div class="control">
                        <input class="input" type="text" value="${vehicle.route_name}" readonly>
                    </div>
                </div>
            </div>
            <div class="column">
                <div class="field">
                    <label class="label">Driver</label>
                    <div class="control">
                        <input class="input" type="text" value="${vehicle.driver_name}" readonly>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Contact</label>
                    <div class="control">
                        <input class="input" type="text" value="${vehicle.driver_contact}" readonly>
                    </div>
                </div>
            </div>
        </div>
        <div class="field">
            <label class="label">Current Location</label>
            <div class="control">
                <input class="input" type="text" value="Lat: ${vehicle.lat.toFixed(6)}, Lng: ${vehicle.lng.toFixed(6)}" readonly>
            </div>
        </div>
    `;
    
    detailsPanel.style.display = 'block';
}

// Highlight vehicle card
function highlightVehicleCard(vehicleId) {
    // Remove previous highlights
    document.querySelectorAll('.vehicle-card').forEach(card => {
        card.classList.remove('has-background-primary-light');
    });
    
    // Add highlight to selected card
    const card = document.querySelector(`[data-vehicle-id="${vehicleId}"]`);
    if (card) {
        card.classList.add('has-background-primary-light');
        card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

// Refresh vehicles data
function refreshVehicles() {
    fetch('vehicles.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_vehicles'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear existing markers
            Object.values(vehicleMarkers).forEach(marker => {
                marker.setMap(null);
            });
            vehicleMarkers = {};
            
            // Add updated markers
            data.vehicles.forEach(vehicle => {
                if (vehicle.latitude && vehicle.longitude) {
                    addVehicleMarker({
                        id: parseInt(vehicle.id),
                        lat: parseFloat(vehicle.latitude),
                        lng: parseFloat(vehicle.longitude),
                        vehicle_number: vehicle.vehicle_number,
                        route_name: vehicle.route_name || 'No route',
                        vehicle_type: vehicle.vehicle_type,
                        status: vehicle.status,
                        driver_name: vehicle.driver_name || 'N/A',
                        driver_contact: vehicle.driver_contact || '',
                        last_updated: vehicle.last_updated,
                        minutes_ago: parseInt(vehicle.minutes_ago)
                    });
                }
            });
            
            // Update vehicle list
            updateVehicleList(data.vehicles);
        }
    })
    .catch(error => {
        console.error('Error refreshing vehicles:', error);
    });
}

// Update vehicle list in sidebar
function updateVehicleList(vehicles) {
    const vehicleList = document.getElementById('vehicle-list');
    // Update the vehicle cards with new data
    vehicles.forEach(vehicle => {
        const card = document.querySelector(`[data-vehicle-id="${vehicle.id}"]`);
        if (card) {
            const timeTag = card.querySelector('.tag.is-light');
            if (timeTag) {
                const minutesAgo = parseInt(vehicle.minutes_ago);
                timeTag.textContent = minutesAgo < 1 ? 'Just now' : 
                                     minutesAgo < 60 ? minutesAgo + ' min ago' : 
                                     Math.floor(minutesAgo / 60) + 'h ago';
            }
        }
    });
}

// Update vehicle location using GPS
function updateVehicleLocation(vehicleId = null) {
    const selectedVehicleId = vehicleId || document.getElementById('vehicleSelect')?.value;
    
    if (!selectedVehicleId) {
        showNotification('Please select a vehicle to update.', 'warning');
        return;
    }

    document.getElementById('loadingModal').classList.add('is-active');

    getCurrentLocation(
        (position) => {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;

            fetch('vehicles.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_location&vehicle_id=${selectedVehicleId}&latitude=${latitude}&longitude=${longitude}`
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loadingModal').classList.remove('is-active');
                if (data.success) {
                    showNotification('Vehicle location updated successfully!', 'success');
                    setTimeout(refreshVehicles, 1000); // Refresh after 1 second
                } else {
                    showNotification('Failed to update location: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                document.getElementById('loadingModal').classList.remove('is-active');
                showNotification('Error updating location: ' + error.message, 'danger');
            });
        },
        (error) => {
            document.getElementById('loadingModal').classList.remove('is-active');
            let errorMessage = 'Unable to get your location. ';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage += 'Please allow location access.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage += 'Location information is unavailable.';
                    break;
                case error.TIMEOUT:
                    errorMessage += 'Location request timed out.';
                    break;
                default:
                    errorMessage += 'Unknown error occurred.';
                    break;
            }
            showNotification(errorMessage, 'danger');
        }
    );
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Vehicle card click handlers
    document.querySelectorAll('.vehicle-card').forEach(card => {
        card.addEventListener('click', function() {
            const vehicleId = this.dataset.vehicleId;
            const lat = parseFloat(this.dataset.lat);
            const lng = parseFloat(this.dataset.lng);
            
            if (lat && lng) {
                map.setCenter({ lat: lat, lng: lng });
                map.setZoom(15);
                
                // Trigger marker click
                if (vehicleMarkers[vehicleId]) {
                    google.maps.event.trigger(vehicleMarkers[vehicleId], 'click');
                }
            }
        });
    });

    // Refresh button
    document.getElementById('refreshBtn').addEventListener('click', function() {
        this.classList.add('is-loading');
        refreshVehicles();
        setTimeout(() => {
            this.classList.remove('is-loading');
        }, 2000);
    });

    <?php if ($user_role === 'admin' || $user_role === 'staff'): ?>
    // Update location button
    const updateLocationBtn = document.getElementById('updateLocationBtn');
    if (updateLocationBtn) {
        updateLocationBtn.addEventListener('click', function() {
            const vehicleSelect = document.getElementById('vehicleSelect');
            if (vehicleSelect.value) {
                updateVehicleLocation(vehicleSelect.value);
            } else {
                showNotification('Please select a vehicle first.', 'warning');
            }
        });
    }

    // Update selected vehicle button
    const updateSelectedVehicleBtn = document.getElementById('updateSelectedVehicleBtn');
    if (updateSelectedVehicleBtn) {
        updateSelectedVehicleBtn.addEventListener('click', function() {
            updateVehicleLocation();
        });
    }

    // Vehicle select change handler
    const vehicleSelect = document.getElementById('vehicleSelect');
    if (vehicleSelect) {
        vehicleSelect.addEventListener('change', function() {
            const updateBtn = document.getElementById('updateSelectedVehicleBtn');
            updateBtn.disabled = !this.value;
        });
    }
    <?php endif; ?>

    // Close modal handlers
    document.querySelectorAll('.modal-background, .modal-close').forEach(element => {
        element.addEventListener('click', function() {
            this.closest('.modal').classList.remove('is-active');
        });
    });
});

// Load Google Maps
function loadGoogleMaps() {
    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap`;
    script.async = true;
    script.defer = true;
    document.head.appendChild(script);
}

// For demo purposes, initialize a simple map without Google Maps API
if (typeof google === 'undefined') {
    document.getElementById('map').innerHTML = `
        <div class="notification is-info">
            <div class="content has-text-centered">
                <i class="mdi mdi-map is-size-1 mb-3"></i>
                <h5 class="title is-5">Interactive Map</h5>
                <p>This would show a real Google Maps with vehicle locations.</p>
                <p class="is-size-7 has-text-grey">
                    Demo Mode: Click on vehicle cards to see their details.<br>
                    In production, add your Google Maps API key to enable the interactive map.
                </p>
                <div class="buttons is-centered mt-4">
                    ${<?php echo json_encode($vehicles); ?>.map(vehicle => 
                        vehicle.latitude && vehicle.longitude ? 
                        `<button class="button is-small" onclick="showVehicleOnMap(${vehicle.id})">
                            <i class="mdi mdi-${vehicle.vehicle_type === 'bus' ? 'bus' : 'car'}"></i>&nbsp;
                            ${vehicle.vehicle_number}
                        </button>` : ''
                    ).join('')}
                </div>
            </div>
        </div>
    `;
}

function showVehicleOnMap(vehicleId) {
    const vehicle = <?php echo json_encode($vehicles); ?>.find(v => v.id == vehicleId);
    if (vehicle) {
        showVehicleDetails({
            id: parseInt(vehicle.id),
            lat: parseFloat(vehicle.latitude),
            lng: parseFloat(vehicle.longitude),
            vehicle_number: vehicle.vehicle_number,
            route_name: vehicle.route_name || 'No route',
            vehicle_type: vehicle.vehicle_type,
            status: vehicle.status,
            driver_name: vehicle.driver_name || 'N/A',
            driver_contact: vehicle.driver_contact || '',
            last_updated: vehicle.last_updated,
            minutes_ago: parseInt(vehicle.minutes_ago || 0)
        });
        highlightVehicleCard(vehicleId);
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>