// Google Maps Location Viewer Alpine.js Component (Static Map)
if (typeof window.googleMapsLocationViewer === "undefined") {
    window.googleMapsLocationViewer = function (
        mapId,
        title,
        address,
        zoom,
        apiKey
    ) {
        return {
            mapId: mapId,
            statusMessage: "",
            statusType: "info",
            apiKey: apiKey || window.GOOGLE_MAPS_API_KEY || "",

            init() {
                // console.log("=== GOOGLE MAPS VIEWER INIT ===");
                // console.log("Map ID:", this.mapId);
                // console.log("Title:", title);
                // console.log("Address:", address);

                // Read API key dynamically
                this.apiKey = this.apiKey || window.GOOGLE_MAPS_API_KEY || "";

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

                // If we have an address, geocode it to show the static map
                if (address && address.trim() !== "") {
                    this.geocodeAddress();
                } else {
                    this.showFallbackMap();
                }
            },

            geocodeAddress() {
                if (!address || address.trim() === "") return;

                // Use fetch to call Google Geocoding API directly for static map
                const geocodingUrl = `https://maps.googleapis.com/maps/api/geocode/json?address=${encodeURIComponent(
                    address
                )}&key=${encodeURIComponent(this.apiKey)}`;

                fetch(geocodingUrl)
                    .then((response) => response.json())
                    .then((data) => {
                        if (
                            data.status === "OK" &&
                            data.results &&
                            data.results[0]
                        ) {
                            const location = data.results[0].geometry.location;
                            const lat = location.lat;
                            const lng = location.lng;

                            // Generate high-quality static map URL
                            const staticMapUrl = `https://maps.googleapis.com/maps/api/staticmap?center=${lat},${lng}&zoom=15&size=1200x600&scale=2&maptype=roadmap&markers=color:red%7Csize:mid%7C${lat},${lng}&key=${encodeURIComponent(
                                this.apiKey
                            )}`;

                            // Set the static map as background image
                            const mapElement = document.getElementById(
                                this.mapId
                            );
                            if (mapElement) {
                                mapElement.style.backgroundImage = `url('${staticMapUrl}')`;
                                mapElement.style.backgroundSize = "cover";
                                mapElement.style.backgroundPosition = "center";
                                mapElement.style.backgroundRepeat = "no-repeat";

                                // Remove loading placeholder
                                const loadingPlaceholder =
                                    mapElement.querySelector(
                                        ".absolute.inset-0"
                                    );
                                if (loadingPlaceholder) {
                                    loadingPlaceholder.remove();
                                }
                            }

                            // console.log("Viewer generated static map for:", lat, lng);
                        } else {
                            console.warn(
                                "Viewer geocoding failed for address:",
                                address,
                                "Status:",
                                data.status
                            );
                            this.showFallbackMap();
                        }
                    })
                    .catch((error) => {
                        console.error(
                            "Viewer geocoding request failed:",
                            error
                        );
                        this.showFallbackMap();
                    });
            },

            showFallbackMap() {
                const mapElement = document.getElementById(this.mapId);
                if (mapElement) {
                    // Default Kuala Lumpur location
                    const lat = 3.139;
                    const lng = 101.6869;
                    const staticMapUrl = `https://maps.googleapis.com/maps/api/staticmap?center=${lat},${lng}&zoom=10&size=1200x600&scale=2&maptype=roadmap&markers=color:red%7Csize:mid%7C${lat},${lng}&key=${encodeURIComponent(
                        this.apiKey
                    )}`;

                    mapElement.style.backgroundImage = `url('${staticMapUrl}')`;
                    mapElement.style.backgroundSize = "cover";
                    mapElement.style.backgroundPosition = "center";
                    mapElement.style.backgroundRepeat = "no-repeat";

                    // Remove loading placeholder
                    const loadingPlaceholder =
                        mapElement.querySelector(".absolute.inset-0");
                    if (loadingPlaceholder) {
                        loadingPlaceholder.remove();
                    }
                }
            },

            showStatus(message, type = "info") {
                this.statusMessage = message;
                this.statusType = type;
            },
        };
    };
}

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
            autocomplete: null,
            statusMessage: "",
            statusType: "info",
            apiKey: apiKey || window.GOOGLE_MAPS_API_KEY || "",
            isInitializing: false,
            isInitialized: false,

            init() {
                // console.log("=== GOOGLE MAPS COMPONENT INIT ===");
                //console.log("Map ID:", this.mapId);

                // Check if map element already has a map instance (from previous initialization)
                const mapElement = document.getElementById(this.mapId);
                if (mapElement && mapElement._googleMapInstance) {
                    // console.log("Map already exists, reusing...");
                    // Map already exists, just sync the component state
                    this.map = mapElement._googleMapInstance;
                    this.marker = mapElement._googleMapMarker || null;
                    this.geocoder = new google.maps.Geocoder();
                    this.isInitialized = true;
                    this.isInitializing = false;

                    // Ensure click listener exists (atomic check to prevent duplicates)
                    if (
                        !mapElement._googleMapClickListener &&
                        !mapElement._addingClickListener
                    ) {
                        mapElement._addingClickListener = true;
                        const clickListener = this.map.addListener(
                            "click",
                            (event) => {
                                this.placeMarker(event.latLng, true); // User action, update fields
                            }
                        );
                        mapElement._googleMapClickListener = clickListener;
                        mapElement._addingClickListener = false;
                    }

                    // If we have an address but no marker, geocode to place marker
                    if (
                        initialAddress &&
                        initialAddress !== "" &&
                        initialAddress !== "null" &&
                        !this.marker
                    ) {
                        this.geocodeAndPlaceMarker(initialAddress);
                    }

                    // Initialize Autocomplete if needed
                    this.initializeAutocomplete();
                    return;
                }

                // Read API key dynamically (in case it wasn't passed or was set after component creation)
                this.apiKey = this.apiKey || window.GOOGLE_MAPS_API_KEY || "";
                // console.log(
                //     "API Key:",
                //     this.apiKey
                //         ? "SET (" + this.apiKey.substring(0, 10) + "...)"
                //         : "NOT SET"
                // );

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
                    // console.log("Map already initializing or initialized");
                    return;
                }

                if (typeof google !== "undefined" && google.maps) {
                    // console.log("Google Maps already loaded");
                    this.initializeMap();
                    return;
                }

                // console.log("Loading Google Maps API...");
                this.isInitializing = true;

                // Check if script is already being loaded
                const existingScript = document.querySelector(
                    'script[src*="maps.googleapis.com/maps/api/js"]'
                );
                if (existingScript) {
                    // console.log(
                    //     "Google Maps script already loading, waiting..."
                    // );
                    // Poll for Google Maps to be available
                    const checkInterval = setInterval(() => {
                        if (typeof google !== "undefined" && google.maps) {
                            clearInterval(checkInterval);
                            // console.log("Google Maps became available");
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
                    // console.log("Google Maps API loaded via callback");
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
                    // console.log("Map already initialized, skipping...");
                    return;
                }

                // console.log("Initializing map...");

                // Wait for DOM to be ready if element doesn't exist yet
                const findMapElement = () => {
                    return document.getElementById(this.mapId);
                };

                let mapElement = findMapElement();

                // If element doesn't exist, wait a bit for Livewire to finish rendering
                if (!mapElement) {
                    // console.log(
                    //     "Map element not found yet, waiting for DOM..."
                    // );
                    let attempts = 0;
                    const maxAttempts = 50; // 5 seconds max wait

                    const waitForElement = setInterval(() => {
                        attempts++;
                        mapElement = findMapElement();

                        if (mapElement) {
                            clearInterval(waitForElement);
                            // console.log("Map element found after wait");
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
                    // console.log("Reusing existing map instance");
                    this.map = mapElement._googleMapInstance;
                    this.marker = mapElement._googleMapMarker || null;
                    this.geocoder = new google.maps.Geocoder();
                    this.isInitialized = true;
                    this.isInitializing = false;

                    // Ensure click listener exists (but don't add duplicate)
                    if (!mapElement._googleMapClickListener) {
                        const clickListener = this.map.addListener(
                            "click",
                            (event) => {
                                this.placeMarker(event.latLng);
                            }
                        );
                        mapElement._googleMapClickListener = clickListener;
                    }

                    // Only update marker if coordinates exist AND marker doesn't exist or is at different location
                    const lat =
                        initialLat && initialLat !== "null"
                            ? parseFloat(initialLat)
                            : null;
                    const lng =
                        initialLng && initialLng !== "null"
                            ? parseFloat(initialLng)
                            : null;

                    // When reusing map, don't create or modify markers
                    // Marker position is managed by user interactions (click, autocomplete, etc.)
                    // Form field values are updated BY marker placement, not the other way around
                    // Just sync the marker reference - don't place new markers or update positions
                    // This prevents duplicate markers when Livewire re-renders

                    // Initialize Autocomplete for reused map
                    this.initializeAutocomplete();
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

                    // console.log("Creating map at:", defaultLat, defaultLng);

                    this.map = new google.maps.Map(mapElement, {
                        center: { lat: defaultLat, lng: defaultLng },
                        zoom: mapZoom,
                        mapTypeControl: false, // Disable street map/satellite toggle
                        streetViewControl: false, // Disable drag pegman for Street View
                        fullscreenControl: false, // Disable fullscreen control
                        zoomControl: false, // Disable zoom buttons
                        panControl: false, // Disable pan control
                        rotateControl: false, // Disable rotate control
                        scaleControl: false, // Disable scale control
                        tilt: 0, // Lock camera tilt (no 3D view)
                        heading: 0, // Lock camera heading (no rotation)
                        gestureHandling: "cooperative", // Require Ctrl/Cmd for zoom/pan gestures
                        disableDoubleClickZoom: true, // Disable double-click zoom
                        keyboardShortcuts: false, // Disable keyboard shortcuts
                    });

                    // Store map instance on element for reuse
                    mapElement._googleMapInstance = this.map;
                    mapElement._googleMapMarker = null; // Will be set when marker is created

                    this.geocoder = new google.maps.Geocoder();

                    // Remove existing click listener if it exists
                    if (mapElement._googleMapClickListener) {
                        google.maps.event.removeListener(
                            mapElement._googleMapClickListener
                        );
                        mapElement._googleMapClickListener = null;
                    }

                    // Add click listener and store reference
                    const clickListener = this.map.addListener(
                        "click",
                        (event) => {
                            this.placeMarker(event.latLng, true); // User action, update fields
                        }
                    );
                    mapElement._googleMapClickListener = clickListener;

                    // Create marker if initial coordinates exist
                    if (
                        initialLat &&
                        initialLat !== "null" &&
                        initialLng &&
                        initialLng !== "null"
                    ) {
                        this.placeMarker(
                            { lat: defaultLat, lng: defaultLng },
                            false
                        ); // Display only
                    } else if (
                        initialAddress &&
                        initialAddress !== "" &&
                        initialAddress !== "null"
                    ) {
                        // If we have an address but no coordinates, geocode it to place marker
                        this.geocodeAndPlaceMarker(initialAddress);
                    }

                    // Initialize Places Autocomplete
                    this.initializeAutocomplete();

                    // console.log("Map initialized successfully");
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

            geocodeAndPlaceMarker(address) {
                if (!this.geocoder || !address) return;

                this.geocoder.geocode(
                    { address: address },
                    (results, status) => {
                        if (status === "OK" && results[0]) {
                            const location = results[0].geometry.location;
                            const lat = location.lat();
                            const lng = location.lng();

                            // Center map on the geocoded location
                            if (this.map) {
                                this.map.setCenter(location);
                                this.map.setZoom(15);
                            }

                            // Place marker at the geocoded location (display only, don't update fields)
                            this.placeMarker(location, false);

                            // Update form fields with the geocoded location
                            // We already have the address, so just update coordinates
                            // But since we don't store coordinates, we don't need to update them
                            // The marker placement will handle the form updates

                            console.log(
                                "Geocoded existing address and placed marker at:",
                                lat,
                                lng
                            );
                        } else {
                            console.warn(
                                "Geocoding failed for address:",
                                address,
                                "Status:",
                                status
                            );
                            // Fallback: just center on default location without marker
                            if (this.map) {
                                this.map.setCenter({
                                    lat: 3.139,
                                    lng: 101.6869,
                                });
                                this.map.setZoom(10);
                            }
                        }
                    }
                );
            },

            initializeAutocomplete() {
                // Wait for search input to be available
                this.$nextTick(() => {
                    const searchInput = this.$refs.searchInput;
                    if (!searchInput) {
                        console.warn(
                            "Search input not found, autocomplete not initialized"
                        );
                        return;
                    }

                    // Check if autocomplete already exists on this input
                    if (searchInput._googleAutocomplete) {
                        // console.log("Autocomplete already exists, reusing...");
                        this.autocomplete = searchInput._googleAutocomplete;
                        // Rebind to map bounds in case map was recreated
                        if (this.map) {
                            this.autocomplete.bindTo("bounds", this.map);
                        }
                        return;
                    }

                    if (!google || !google.maps || !google.maps.places) {
                        console.warn(
                            "Google Maps Places library not available"
                        );
                        return;
                    }

                    // Create Autocomplete instance
                    this.autocomplete = new google.maps.places.Autocomplete(
                        searchInput,
                        {
                            types: ["geocode", "establishment"], // Include addresses and places
                            fields: [
                                "geometry",
                                "formatted_address",
                                "name",
                                "place_id",
                            ],
                        }
                    );

                    // Store autocomplete instance on input element for reuse
                    searchInput._googleAutocomplete = this.autocomplete;

                    // Bind autocomplete to map bounds
                    if (this.map) {
                        this.autocomplete.bindTo("bounds", this.map);
                    }

                    // Handle place selection
                    this.autocomplete.addListener("place_changed", () => {
                        const place = this.autocomplete.getPlace();

                        if (!place.geometry || !place.geometry.location) {
                            console.warn(
                                "No geometry found for selected place"
                            );
                            this.showStatus(
                                "No location found for selected place",
                                "error"
                            );
                            return;
                        }

                        // Get location
                        const location = place.geometry.location;
                        const lat = location.lat();
                        const lng = location.lng();

                        // Update map center and zoom
                        this.map.setCenter(location);
                        this.map.setZoom(15);

                        // Place marker at selected location (user action, update fields)
                        this.placeMarker(location, true);

                        // Update form fields with the place name as title and formatted address
                        const placeName = place.name || "";
                        const formattedAddress = place.formatted_address || "";
                        this.updateLocationFields(location, placeName);

                        // Also update the full address field directly
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

                            if (field) {
                                field.value = value;
                                ["input", "change", "blur"].forEach(
                                    (eventType) => {
                                        field.dispatchEvent(
                                            new Event(eventType, {
                                                bubbles: true,
                                            })
                                        );
                                    }
                                );
                            }
                        };
                        updateField("location_full_address", formattedAddress);

                        // Store the place_id if available (for autocomplete selections)
                        if (place.place_id) {
                            updateField("location_place_id", place.place_id);
                        }

                        this.showStatus(
                            `Location set: ${placeName}`,
                            "success"
                        );
                    });
                });
            },

            placeMarker(location, updateFields = true) {
                if (!this.map) return;

                const mapElement = document.getElementById(this.mapId);
                if (!mapElement) return;

                // Prevent rapid successive calls (debounce) - use timestamp for more reliable checking
                const now = Date.now();
                if (
                    mapElement._placingMarker &&
                    typeof mapElement._placingMarker === "number" &&
                    now - mapElement._placingMarker < 500
                ) {
                    // console.log(
                    //     "Marker placement already in progress, skipping (debounced)"
                    // );
                    return;
                }
                mapElement._placingMarker = now;

                // Get or create the single marker instance
                // If marker exists, just update its position (don't create a new one)
                let marker = this.marker || mapElement._googleMapMarker;

                if (marker) {
                    // Marker exists - just update its position
                    try {
                        marker.setPosition(location);
                        // Ensure it's on the map
                        if (!marker.getMap()) {
                            marker.setMap(this.map);
                        }
                        // Sync references
                        this.marker = marker;
                        mapElement._googleMapMarker = marker;
                    } catch (e) {
                        console.warn("Error updating marker position:", e);
                        // If update fails, remove old marker and create new one
                        try {
                            marker.setMap(null);
                            google.maps.event.clearInstanceListeners(marker);
                        } catch (e2) {
                            // Ignore
                        }
                        marker = null;
                    }
                }

                // Only create new marker if one doesn't exist
                if (!marker) {
                    // Remove any orphaned markers first
                    if (
                        mapElement._allMarkers &&
                        Array.isArray(mapElement._allMarkers)
                    ) {
                        mapElement._allMarkers.forEach((m) => {
                            try {
                                if (m && typeof m.setMap === "function") {
                                    m.setMap(null);
                                    google.maps.event.clearInstanceListeners(m);
                                }
                            } catch (e) {
                                // Ignore errors
                            }
                        });
                    }

                    // Create the single marker instance
                    marker = new google.maps.Marker({
                        position: location,
                        map: this.map,
                        draggable: true,
                        title: "Selected Location",
                    });

                    // Store references
                    this.marker = marker;
                    mapElement._googleMapMarker = marker;

                    // Track marker in array for cleanup
                    if (!mapElement._allMarkers) {
                        mapElement._allMarkers = [];
                    }
                    mapElement._allMarkers = [marker]; // Only one marker in array

                    // Add drag listener (only if not already added)
                    marker.addListener("dragend", (event) => {
                        this.updateLocationFields(event.latLng);
                    });
                }

                // Update form fields only if requested (not for existing event display)
                if (updateFields) {
                    this.updateLocationFields(location);
                }

                // Reset flag after a short delay
                setTimeout(() => {
                    mapElement._placingMarker = null;
                }, 300);
            },

            updateLocationFields(latLng, placeTitle = null) {
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

                // console.log("Updating location fields:", latValue, lngValue);

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
                                            // console.log(
                                            //     `Updated Livewire field via path: ${path}`
                                            // );
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

                // If place title was provided (from search/autocomplete), use it directly
                if (placeTitle) {
                    updateField("location_title", placeTitle);
                    // For autocomplete selections, we still need to reverse geocode to get the full address
                    // but we pass the existing title so it doesn't get overwritten
                    this.reverseGeocode(latLng, placeTitle);
                } else {
                    // For map clicks, reverse geocode to get both title and address
                    this.reverseGeocode(latLng);
                }
            },

            reverseGeocode(latLng, existingTitle = null) {
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

                            // For map clicks/drags, also search for nearby places to get place_id
                            this.findNearbyPlaceId(location, address);

                            // Extract title from address components with improved logic for map clicks
                            let title = existingTitle;
                            if (!title && results[0].address_components) {
                                const components =
                                    results[0].address_components;
                                const titleCandidates = [];

                                // Priority order for title extraction (most specific to least specific)
                                const priorityTypes = [
                                    "establishment", // Business/POI name
                                    "point_of_interest", // General POI
                                    "park", // Parks
                                    "neighborhood", // Neighborhoods
                                    "sublocality", // Sub-districts
                                    "sublocality_level_1", // More specific sub-districts
                                    "locality", // City/Town
                                    "route", // Street names
                                    "administrative_area_level_2", // County/District
                                    "administrative_area_level_1", // State/Province
                                    "country", // Country (fallback)
                                ];

                                // Find the most specific available component
                                for (const priorityType of priorityTypes) {
                                    for (const component of components) {
                                        if (
                                            component.types.includes(
                                                priorityType
                                            )
                                        ) {
                                            titleCandidates.push(
                                                component.long_name
                                            );
                                            break; // Found one for this priority level, move to next priority
                                        }
                                    }
                                    if (titleCandidates.length > 0) {
                                        break; // We found at least one candidate
                                    }
                                }

                                // If we found candidates, use the most specific one
                                if (titleCandidates.length > 0) {
                                    title = titleCandidates[0];
                                } else {
                                    // Last resort: use the first meaningful part of the formatted address
                                    // Skip common prefixes like numbers or generic terms
                                    const parts = address.split(", ");
                                    for (const part of parts) {
                                        // Skip parts that are just numbers, postal codes, or country names
                                        if (
                                            part.length > 2 && // Skip very short parts
                                            !/^\d+$/.test(part) && // Skip pure numbers
                                            !/^\d{5}/.test(part) && // Skip postal codes
                                            ![
                                                "Malaysia",
                                                "US",
                                                "USA",
                                                "United Kingdom",
                                                "UK",
                                            ].includes(part) // Skip country names
                                        ) {
                                            title = part;
                                            break;
                                        }
                                    }

                                    // If still no title, just use the first part
                                    if (!title && parts.length > 0) {
                                        title = parts[0];
                                    }
                                }

                                // console.log("Extracted title from map click:", title, "from address:", address);
                            }

                            // console.log("Reverse geocoded - Title:", title, "Address:", address);

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
                                                        // console.log(
                                                        //     `Updated Livewire field via path: ${path}`
                                                        // );
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

                            // Update both title and full address fields
                            let titleUpdated = true;
                            let addressUpdated = true;

                            if (title) {
                                titleUpdated = updateField(
                                    "location_title",
                                    title
                                );
                            }
                            addressUpdated = updateField(
                                "location_full_address",
                                address
                            );

                            if (titleUpdated && addressUpdated) {
                                this.showStatus(
                                    `Location set: ${title || address}`,
                                    "success"
                                );
                            } else {
                                this.showStatus(
                                    "Location found but fields not updated. Please check field names.",
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

            findNearbyPlaceId(location, address) {
                if (!google || !google.maps || !google.maps.places) {
                    console.warn(
                        "Places API not available for finding place_id"
                    );
                    return;
                }

                // Create Places service
                const placesService = new google.maps.places.PlacesService(
                    document.createElement("div")
                );

                // Search for places near the clicked location
                const request = {
                    location: location,
                    radius: 50, // Search within 50 meters
                    query: address, // Use the reverse geocoded address as query
                    fields: ["place_id", "name", "formatted_address"],
                };

                placesService.textSearch(request, (results, status) => {
                    if (
                        status === google.maps.places.PlacesServiceStatus.OK &&
                        results &&
                        results.length > 0
                    ) {
                        const nearestPlace = results[0]; // Take the first (nearest) result

                        // Update the location_place_id field
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

                            if (field) {
                                field.value = value;
                                ["input", "change", "blur"].forEach(
                                    (eventType) => {
                                        field.dispatchEvent(
                                            new Event(eventType, {
                                                bubbles: true,
                                            })
                                        );
                                    }
                                );
                            }
                        };

                        if (nearestPlace.place_id) {
                            updateField(
                                "location_place_id",
                                nearestPlace.place_id
                            );
                            // console.log("Updated location_place_id from nearby search:", nearestPlace.place_id);
                        }
                    } else {
                        // If nearby search fails, clear the place_id field
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

                            if (field) {
                                field.value = value || "";
                                ["input", "change", "blur"].forEach(
                                    (eventType) => {
                                        field.dispatchEvent(
                                            new Event(eventType, {
                                                bubbles: true,
                                            })
                                        );
                                    }
                                );
                            }
                        };
                        updateField("location_place_id", "");
                        // console.log("Cleared location_place_id - no nearby place found");
                    }
                });
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
                            new google.maps.LatLng(location.lat, location.lng),
                            true // User action, update fields
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
                const mapElement = document.getElementById(this.mapId);
                if (!mapElement) return;

                // Remove marker from this.marker
                if (this.marker) {
                    try {
                        this.marker.setMap(null);
                        google.maps.event.clearInstanceListeners(this.marker);
                    } catch (e) {
                        console.warn("Error removing marker:", e);
                    }
                    this.marker = null;
                }

                // Remove marker stored on DOM element
                if (mapElement._googleMapMarker) {
                    try {
                        mapElement._googleMapMarker.setMap(null);
                        google.maps.event.clearInstanceListeners(
                            mapElement._googleMapMarker
                        );
                    } catch (e) {
                        console.warn("Error removing stored marker:", e);
                    }
                    mapElement._googleMapMarker = null;
                }

                // Remove all tracked markers
                if (mapElement._allMarkers) {
                    mapElement._allMarkers.forEach((marker) => {
                        try {
                            if (marker && marker.getMap) {
                                marker.setMap(null);
                                google.maps.event.clearInstanceListeners(
                                    marker
                                );
                            }
                        } catch (e) {
                            // Ignore errors
                        }
                    });
                    mapElement._allMarkers = [];
                }

                // Clear form fields
                const titleField = document.querySelector(
                    '[name="location_title"]'
                );
                if (titleField) {
                    titleField.value = "";
                    titleField.dispatchEvent(
                        new Event("input", { bubbles: true })
                    );
                }

                const addressField = document.querySelector(
                    '[name="location_full_address"]'
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
