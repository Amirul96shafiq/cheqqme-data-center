// Google Maps Location Picker Alpine.js Component
if (typeof window.googleMapsLocationPicker === "undefined") {
    window.googleMapsLocationPicker = function (
        mapId,
        initialLat,
        initialLng,
        initialAddress,
        zoom,
        apiKey
    ) {
        return {
            mapId: mapId,
            map: null,
            marker: null,
            geocoder: null,
            statusMessage: "",
            statusType: "info",
            apiKey: apiKey || window.GOOGLE_MAPS_API_KEY || "",
            isInitializing: false,
            isInitialized: false,

            init() {
                console.log("=== GOOGLE MAPS COMPONENT INIT ===");
                console.log("Map ID:", this.mapId);

                // Read API key dynamically (in case it wasn't passed or was set after component creation)
                this.apiKey = this.apiKey || window.GOOGLE_MAPS_API_KEY || "";
                console.log(
                    "API Key:",
                    this.apiKey
                        ? "SET (" + this.apiKey.substring(0, 10) + "...)"
                        : "NOT SET"
                );

                if (!this.apiKey) {
                    console.error(
                        "Google Maps API key not found. Check .env file for GOOGLE_MAPS_API_KEY"
                    );
                    this.showStatus(
                        "Google Maps API key not configured. Please check your .env file.",
                        "error"
                    );
                    return;
                }

                this.loadGoogleMaps();
            },

            loadGoogleMaps() {
                if (this.isInitializing || this.isInitialized) {
                    console.log("Map already initializing or initialized");
                    return;
                }

                if (typeof google !== "undefined" && google.maps) {
                    console.log("Google Maps already loaded");
                    this.initializeMap();
                    return;
                }

                console.log("Loading Google Maps API...");
                this.isInitializing = true;

                // Check if script is already being loaded
                const existingScript = document.querySelector(
                    'script[src*="maps.googleapis.com/maps/api/js"]'
                );
                if (existingScript) {
                    console.log(
                        "Google Maps script already loading, waiting..."
                    );
                    // Poll for Google Maps to be available
                    const checkInterval = setInterval(() => {
                        if (typeof google !== "undefined" && google.maps) {
                            clearInterval(checkInterval);
                            console.log("Google Maps became available");
                            this.initializeMap();
                        }
                    }, 100);

                    // Timeout after 10 seconds
                    setTimeout(() => {
                        clearInterval(checkInterval);
                        if (typeof google === "undefined" || !google.maps) {
                            this.isInitializing = false;
                            this.showStatus(
                                "Google Maps API loading timed out",
                                "error"
                            );
                        }
                    }, 10000);
                    return;
                }

                // Sanitize mapId for callback name (remove hyphens and special chars)
                const callbackName = `initGoogleMap_${this.mapId.replace(
                    /[^a-zA-Z0-9_]/g,
                    "_"
                )}`;

                // Create global callback BEFORE loading script
                window[callbackName] = () => {
                    console.log("Google Maps API loaded via callback");
                    this.initializeMap();
                    // Clean up callback after use
                    try {
                        delete window[callbackName];
                    } catch (e) {
                        // Ignore cleanup errors
                    }
                };

                const script = document.createElement("script");
                script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(
                    this.apiKey
                )}&libraries=places&loading=async&callback=${callbackName}`;
                script.async = true;
                script.defer = true;
                script.onerror = () => {
                    console.error("Failed to load Google Maps API script");
                    this.isInitializing = false;
                    // Clean up callback on error
                    try {
                        delete window[callbackName];
                    } catch (e) {
                        // Ignore cleanup errors
                    }
                    this.showStatus(
                        "Failed to load Google Maps API. Check your API key and internet connection.",
                        "error"
                    );
                };

                document.head.appendChild(script);
            },

            initializeMap() {
                // Prevent double initialization
                if (this.isInitialized) {
                    console.log("Map already initialized, skipping...");
                    return;
                }

                console.log("Initializing map...");

                // Wait for DOM to be ready if element doesn't exist yet
                const findMapElement = () => {
                    return document.getElementById(this.mapId);
                };

                let mapElement = findMapElement();

                // If element doesn't exist, wait a bit for Livewire to finish rendering
                if (!mapElement) {
                    console.log(
                        "Map element not found yet, waiting for DOM..."
                    );
                    let attempts = 0;
                    const maxAttempts = 50; // 5 seconds max wait

                    const waitForElement = setInterval(() => {
                        attempts++;
                        mapElement = findMapElement();

                        if (mapElement) {
                            clearInterval(waitForElement);
                            console.log("Map element found after wait");
                            this.proceedWithInitialization(mapElement);
                        } else if (attempts >= maxAttempts) {
                            clearInterval(waitForElement);
                            console.error(
                                "Map element not found after waiting:",
                                this.mapId
                            );
                            this.isInitializing = false;
                            this.showStatus("Map container not found", "error");
                        }
                    }, 100);

                    return;
                }

                this.proceedWithInitialization(mapElement);
            },

            proceedWithInitialization(mapElement) {
                // Check if map already exists for this element (from previous initialization)
                if (mapElement._googleMapInstance) {
                    console.log("Reusing existing map instance");
                    this.map = mapElement._googleMapInstance;
                    this.marker = mapElement._googleMapMarker || null;
                    this.geocoder = new google.maps.Geocoder();
                    this.isInitialized = true;
                    this.isInitializing = false;

                    // Update map center and marker if coordinates exist
                    const lat =
                        initialLat && initialLat !== "null"
                            ? parseFloat(initialLat)
                            : null;
                    const lng =
                        initialLng && initialLng !== "null"
                            ? parseFloat(initialLng)
                            : null;
                    if (lat && lng) {
                        this.map.setCenter({ lat, lng });
                        this.map.setZoom(zoom || 15);
                        this.placeMarker({ lat, lng });
                    }
                    return;
                }

                try {
                    const defaultLat =
                        initialLat && initialLat !== "null"
                            ? parseFloat(initialLat)
                            : 3.139;
                    const defaultLng =
                        initialLng && initialLng !== "null"
                            ? parseFloat(initialLng)
                            : 101.6869;
                    const mapZoom = zoom || 15;

                    console.log("Creating map at:", defaultLat, defaultLng);

                    this.map = new google.maps.Map(mapElement, {
                        center: { lat: defaultLat, lng: defaultLng },
                        zoom: mapZoom,
                        mapTypeControl: true,
                        streetViewControl: true,
                        fullscreenControl: true,
                    });

                    // Store map instance on element for reuse
                    mapElement._googleMapInstance = this.map;
                    mapElement._googleMapMarker = null; // Will be set when marker is created

                    this.geocoder = new google.maps.Geocoder();

                    // Add click listener
                    this.map.addListener("click", (event) => {
                        this.placeMarker(event.latLng);
                    });

                    // Create marker if initial coordinates exist
                    if (
                        initialLat &&
                        initialLat !== "null" &&
                        initialLng &&
                        initialLng !== "null"
                    ) {
                        this.placeMarker({ lat: defaultLat, lng: defaultLng });
                    }

                    console.log("Map initialized successfully");
                    this.isInitialized = true;
                    this.isInitializing = false;
                    this.showStatus("Map loaded successfully", "success");
                } catch (error) {
                    console.error("Error initializing map:", error);
                    this.isInitializing = false;
                    this.showStatus(
                        "Error initializing map: " + error.message,
                        "error"
                    );
                }
            },

            placeMarker(location) {
                if (!this.map) return;

                // Remove existing marker
                if (this.marker) {
                    this.marker.setMap(null);
                }

                // Create new marker
                this.marker = new google.maps.Marker({
                    position: location,
                    map: this.map,
                    draggable: true,
                    title: "Selected Location",
                });

                // Store marker reference on map element for reuse
                const mapElement = document.getElementById(this.mapId);
                if (mapElement) {
                    mapElement._googleMapMarker = this.marker;
                }

                // Add drag listener
                this.marker.addListener("dragend", (event) => {
                    this.updateLocationFields(event.latLng);
                });

                // Update form fields
                this.updateLocationFields(location);
            },

            updateLocationFields(latLng) {
                // Handle both google.maps.LatLng objects and plain objects
                let latitude, longitude;
                if (
                    typeof latLng.lat === "function" &&
                    typeof latLng.lng === "function"
                ) {
                    // It's a google.maps.LatLng object
                    latitude = latLng.lat();
                    longitude = latLng.lng();
                } else if (
                    typeof latLng.lat === "number" &&
                    typeof latLng.lng === "number"
                ) {
                    // It's a plain object with lat/lng properties
                    latitude = latLng.lat;
                    longitude = latLng.lng;
                } else {
                    console.error("Invalid latLng object:", latLng);
                    return;
                }

                const latValue = latitude.toFixed(6);
                const lngValue = longitude.toFixed(6);

                console.log("Updating location fields:", latValue, lngValue);

                // Helper function to update a field
                const updateField = (fieldName, value) => {
                    // Try multiple selectors
                    const selectors = [
                        `[name="${fieldName}"]`,
                        `input[name="${fieldName}"]`,
                        `[wire\\:model*="${fieldName}"]`,
                        `[data-field-name="${fieldName}"]`,
                    ];

                    let field = null;
                    for (const selector of selectors) {
                        field = document.querySelector(selector);
                        if (field) break;
                    }

                    if (!field) {
                        console.warn(`Field not found: ${fieldName}`);
                        return false;
                    }

                    // Update field value
                    field.value = value;

                    // Dispatch multiple events for better compatibility
                    const events = ["input", "change", "blur"];
                    events.forEach((eventType) => {
                        field.dispatchEvent(
                            new Event(eventType, {
                                bubbles: true,
                                cancelable: true,
                            })
                        );
                    });

                    // Trigger Livewire update
                    if (typeof Livewire !== "undefined") {
                        // Try to find Livewire component
                        let wireId = field
                            .closest("[wire\\:id]")
                            ?.getAttribute("wire:id");

                        // If not found, try finding parent Livewire component
                        if (!wireId) {
                            const livewireComponent =
                                field.closest("[wire\\:id]") ||
                                field
                                    .closest("[x-data]")
                                    ?.closest("[wire\\:id]");
                            wireId = livewireComponent?.getAttribute("wire:id");
                        }

                        if (wireId) {
                            try {
                                const component = Livewire.find(wireId);
                                if (component) {
                                    // Try different property paths
                                    const paths = [
                                        `data.${fieldName}`,
                                        `location_${fieldName
                                            .split("_")
                                            .pop()}`,
                                        fieldName,
                                    ];

                                    for (const path of paths) {
                                        try {
                                            component.set(path, value);
                                            console.log(
                                                `Updated Livewire field via path: ${path}`
                                            );
                                            break;
                                        } catch (e) {
                                            // Try next path
                                        }
                                    }
                                }
                            } catch (e) {
                                console.warn(
                                    "Failed to update Livewire field:",
                                    e
                                );
                            }
                        } else {
                            console.warn("Livewire component ID not found");
                        }
                    }

                    // Trigger Alpine.js update if field has x-model
                    if (
                        field.hasAttribute("x-model") ||
                        field.hasAttribute("wire:model")
                    ) {
                        const modelAttr =
                            field.getAttribute("x-model") ||
                            field.getAttribute("wire:model");
                        if (modelAttr && window.Alpine) {
                            try {
                                const alpineComponent = Alpine.$data(
                                    field.closest("[x-data]")
                                );
                                if (
                                    alpineComponent &&
                                    alpineComponent[modelAttr] !== undefined
                                ) {
                                    alpineComponent[modelAttr] = value;
                                }
                            } catch (e) {
                                // Ignore Alpine errors
                            }
                        }
                    }

                    return true;
                };

                // Update latitude and longitude fields
                updateField("location_latitude", latValue);
                updateField("location_longitude", lngValue);

                // Reverse geocode to get address
                this.reverseGeocode(latLng);
            },

            reverseGeocode(latLng) {
                if (!this.geocoder) return;

                // Convert to google.maps.LatLng if it's a plain object
                let location;
                if (
                    typeof latLng.lat === "function" &&
                    typeof latLng.lng === "function"
                ) {
                    // Already a google.maps.LatLng object
                    location = latLng;
                } else if (
                    typeof latLng.lat === "number" &&
                    typeof latLng.lng === "number"
                ) {
                    // Convert plain object to google.maps.LatLng
                    location = new google.maps.LatLng(latLng.lat, latLng.lng);
                } else {
                    console.error(
                        "Invalid latLng for reverse geocoding:",
                        latLng
                    );
                    return;
                }

                this.geocoder.geocode(
                    { location: location },
                    (results, status) => {
                        if (status === "OK" && results[0]) {
                            const address = results[0].formatted_address;
                            console.log("Reverse geocoded address:", address);

                            // Use the same update mechanism
                            const updateField = (fieldName, value) => {
                                const selectors = [
                                    `[name="${fieldName}"]`,
                                    `input[name="${fieldName}"]`,
                                    `[wire\\:model*="${fieldName}"]`,
                                    `[data-field-name="${fieldName}"]`,
                                ];

                                let field = null;
                                for (const selector of selectors) {
                                    field = document.querySelector(selector);
                                    if (field) break;
                                }

                                if (!field) {
                                    console.warn(
                                        `Field not found: ${fieldName}`
                                    );
                                    return false;
                                }

                                field.value = value;

                                // Dispatch multiple events
                                const events = ["input", "change", "blur"];
                                events.forEach((eventType) => {
                                    field.dispatchEvent(
                                        new Event(eventType, {
                                            bubbles: true,
                                            cancelable: true,
                                        })
                                    );
                                });

                                // Trigger Livewire update
                                if (typeof Livewire !== "undefined") {
                                    let wireId = field
                                        .closest("[wire\\:id]")
                                        ?.getAttribute("wire:id");

                                    if (!wireId) {
                                        const livewireComponent =
                                            field.closest("[wire\\:id]") ||
                                            field
                                                .closest("[x-data]")
                                                ?.closest("[wire\\:id]");
                                        wireId =
                                            livewireComponent?.getAttribute(
                                                "wire:id"
                                            );
                                    }

                                    if (wireId) {
                                        try {
                                            const component =
                                                Livewire.find(wireId);
                                            if (component) {
                                                const paths = [
                                                    `data.${fieldName}`,
                                                    `location_${fieldName
                                                        .split("_")
                                                        .pop()}`,
                                                    fieldName,
                                                ];

                                                for (const path of paths) {
                                                    try {
                                                        component.set(
                                                            path,
                                                            value
                                                        );
                                                        console.log(
                                                            `Updated Livewire field via path: ${path}`
                                                        );
                                                        break;
                                                    } catch (e) {
                                                        // Try next path
                                                    }
                                                }
                                            }
                                        } catch (e) {
                                            console.warn(
                                                "Failed to update Livewire field:",
                                                e
                                            );
                                        }
                                    }
                                }

                                // Trigger Alpine.js update
                                if (
                                    field.hasAttribute("x-model") ||
                                    field.hasAttribute("wire:model")
                                ) {
                                    const modelAttr =
                                        field.getAttribute("x-model") ||
                                        field.getAttribute("wire:model");
                                    if (modelAttr && window.Alpine) {
                                        try {
                                            const alpineComponent =
                                                Alpine.$data(
                                                    field.closest("[x-data]")
                                                );
                                            if (
                                                alpineComponent &&
                                                alpineComponent[modelAttr] !==
                                                    undefined
                                            ) {
                                                alpineComponent[modelAttr] =
                                                    value;
                                            }
                                        } catch (e) {
                                            // Ignore Alpine errors
                                        }
                                    }
                                }

                                return true;
                            };

                            // Update address field
                            if (updateField("location_address", address)) {
                                this.showStatus(
                                    "Address updated from location!",
                                    "success"
                                );
                            } else {
                                this.showStatus(
                                    "Address found but field not updated. Please check field name.",
                                    "warning"
                                );
                            }
                        } else {
                            this.showStatus(
                                "Could not determine address for this location.",
                                "warning"
                            );
                        }
                    }
                );
            },

            getCurrentLocation() {
                if (!navigator.geolocation) {
                    this.showStatus(
                        "Geolocation is not supported by this browser.",
                        "error"
                    );
                    return;
                }

                this.showStatus("Getting your current location...", "info");

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const location = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                        };

                        if (this.map) {
                            this.map.setCenter(location);
                            this.map.setZoom(16);
                        }
                        this.placeMarker(
                            new google.maps.LatLng(location.lat, location.lng)
                        );
                    },
                    (error) => {
                        let message = "Unable to get your location. ";
                        switch (error.code) {
                            case error.PERMISSION_DENIED:
                                message += "Location access denied by user.";
                                break;
                            case error.POSITION_UNAVAILABLE:
                                message += "Location information unavailable.";
                                break;
                            case error.TIMEOUT:
                                message += "Location request timed out.";
                                break;
                            default:
                                message += "An unknown error occurred.";
                                break;
                        }
                        this.showStatus(message, "error");
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 300000,
                    }
                );
            },

            clearLocation() {
                // Remove marker
                if (this.marker) {
                    this.marker.setMap(null);
                    this.marker = null;
                }

                // Clear stored marker reference
                const mapElement = document.getElementById(this.mapId);
                if (mapElement) {
                    mapElement._googleMapMarker = null;
                }

                // Clear form fields
                const latField = document.querySelector(
                    '[name="location_latitude"]'
                );
                if (latField) {
                    latField.value = "";
                    latField.dispatchEvent(
                        new Event("input", { bubbles: true })
                    );
                }

                const lngField = document.querySelector(
                    '[name="location_longitude"]'
                );
                if (lngField) {
                    lngField.value = "";
                    lngField.dispatchEvent(
                        new Event("input", { bubbles: true })
                    );
                }

                const addressField = document.querySelector(
                    '[name="location_address"]'
                );
                if (addressField) {
                    addressField.value = "";
                    addressField.dispatchEvent(
                        new Event("input", { bubbles: true })
                    );
                }

                this.showStatus("Location cleared.", "info");
            },

            showStatus(message, type = "info") {
                this.statusMessage = message;
                this.statusType = type;

                // Auto-hide success messages after 3 seconds
                if (type === "success") {
                    setTimeout(() => {
                        this.statusMessage = "";
                    }, 3000);
                }
            },
        };
    };
}
