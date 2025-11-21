// Greeting Weather Functionality
// Handles weather API integration, data caching, and DOM updates

// Weather icon mapping
const weatherIconMap = {
    sunny: { icon: "heroicon-o-sun", color: "text-primary-500" },
    clear: { icon: "heroicon-o-sun", color: "text-primary-500" },
    cloud: { icon: "heroicon-o-cloud", color: "text-gray-500" },
    overcast: { icon: "heroicon-o-cloud", color: "text-gray-500" },
    rain: { icon: "heroicon-o-cloud-rain", color: "text-blue-500" },
    drizzle: { icon: "heroicon-o-cloud-rain", color: "text-blue-500" },
    shower: { icon: "heroicon-o-cloud-rain", color: "text-blue-500" },
    storm: { icon: "heroicon-o-bolt", color: "text-purple-500" },
    thunder: { icon: "heroicon-o-bolt", color: "text-purple-500" },
    lightning: { icon: "heroicon-o-bolt", color: "text-purple-500" },
    snow: { icon: "heroicon-o-snowflake", color: "text-blue-300" },
    blizzard: { icon: "heroicon-o-snowflake", color: "text-blue-300" },
    sleet: { icon: "heroicon-o-snowflake", color: "text-blue-300" },
    fog: { icon: "heroicon-o-eye-slash", color: "text-gray-400" },
    mist: { icon: "heroicon-o-eye-slash", color: "text-gray-400" },
    haze: { icon: "heroicon-o-eye-slash", color: "text-gray-400" },
};

// Custom heroicon SVG generation
function getHeroiconSVG(iconName) {
    const svgPaths = {
        "heroicon-o-sun":
            '<path fill="currentColor" d="M16 12.005a4 4 0 1 1-4 4a4.005 4.005 0 0 1 4-4m0-2a6 6 0 1 0 6 6a6 6 0 0 0-6-6ZM5.394 6.813L6.81 5.399l3.505 3.506L8.9 10.319zM2 15.005h5v2H2zm3.394 10.193L8.9 21.692l1.414 1.414l-3.505 3.506zM15 25.005h2v5h-2zm6.687-1.9l1.414-1.414l3.506 3.506l-1.414 1.414zm3.313-8.1h5v2h-5zm-3.313-6.101l3.506-3.506l1.414 1.414l-3.506 3.506zM15 2.005h2v5h-2z"/>',
        "heroicon-o-cloud":
            '<path fill="currentColor" d="M30 15.5a6.532 6.532 0 0 0-5.199-6.363a8.994 8.994 0 0 0-17.6 0A6.532 6.532 0 0 0 2 15.5a6.454 6.454 0 0 0 1.688 4.35A5.983 5.983 0 0 0 8 30h11a5.976 5.976 0 0 0 5.61-8.102A6.505 6.505 0 0 0 30 15.501ZM19 28H8a3.993 3.993 0 0 1-.673-7.93l.663-.112l.146-.656a5.496 5.496 0 0 1 10.73 0l.145.656l.663.113A3.993 3.993 0 0 1 19 28Zm4.5-8h-.055a5.956 5.956 0 0 0-2.796-1.756a7.495 7.495 0 0 0-14.299 0a5.988 5.988 0 0 0-1.031.407A4.445 4.445 0 0 1 4 15.5a4.517 4.517 0 0 1 4.144-4.481l.816-.064l.099-.812a6.994 6.994 0 0 1 13.883 0l.099.812l.815.064A4.497 4.497 0 0 1 23.5 20Z"/>',
        "heroicon-o-cloud-rain":
            '<path fill="currentColor" d="M23.5 22h-15A6.5 6.5 0 0 1 7.2 9.14a9 9 0 0 1 17.6 0A6.5 6.5 0 0 1 23.5 22zM16 4a7 7 0 0 0-6.94 6.14L9 11h-.86a4.5 4.5 0 0 0 .36 9h15a4.5 4.5 0 0 0 .36-9H23l-.1-.82A7 7 0 0 0 16 4zm-2 26a.93.93 0 0 1-.45-.11a1 1 0 0 1-.44-1.34l2-4a1 1 0 1 1 1.78.9l-2 4A1 1 0 0 1 14 30zm6 0a.93.93 0 0 1-.45-.11a1 1 0 0 1-.44-1.34l2-4a1 1 0 1 1 1.78.9l-2 4A1 1 0 0 1 20 30zM8 30a.93.93 0 0 1-.45-.11a1 1 0 0 1-.44-1.34l2-4a1 1 0 1 1 1.78.9l-2 4A1 1 0 0 1 8 30z"/>',
        "heroicon-o-bolt":
            '<path fill="currentColor" d="M21 30a1 1 0 0 1-.894-1.447l2-4a1 1 0 1 1 1.788.894l-2 4A.998.998 0 0 1 21 30zM9 32a1 1 0 0 1-.894-1.447l2-4a1 1 0 1 1 1.788.894l-2 4A.998.998 0 0 1 9 32zm6.901-1.504l-1.736-.992L17.31 24h-6l4.855-8.496l1.736.992L14.756 22h6.001l-4.856 8.496z"/><path fill="currentColor" d="M24.8 9.136a8.994 8.994 0 0 0-17.6 0a6.493 6.493 0 0 0 .23 12.768l-1.324 2.649a1 1 0 1 0 1.788.894l2-4a1 1 0 0 0-.446-1.341A.979.979 0 0 0 9 20.01V20h-.5a4.497 4.497 0 0 1-.356-8.981l.816-.064l.099-.812a6.994 6.994 0 0 1 13.883 0l.099.812l.815.064A4.497 4.497 0 0 1 23.5 20H23v2h.5a6.497 6.497 0 0 0 1.3-12.864Z"/>',
        "heroicon-o-snowflake":
            '<path fill="currentColor" d="M23.5 22h-15A6.5 6.5 0 0 1 7.2 9.14a9 9 0 0 1 17.6 0A6.5 6.5 0 0 1 23.5 22zM16 4a7 7 0 0 0-6.94 6.14L9 11h-.86a4.5 4.5 0 0 0 .36 9h15a4.5 4.5 0 0 0 .36-9H23l-.1-.82A7 7 0 0 0 16 4zm-4 21.05L10.95 24L9.5 25.45L8.05 24L7 25.05l1.45 1.45L7 27.95L8.05 29l1.45-1.45L10.95 29L12 27.95l-1.45-1.45L12 25.05zm14 0L24.95 24l-1.45 1.45L22.05 24L21 25.05l1.45 1.45L21 27.95L22.05 29l1.45-1.45L24.95 29L26 27.95l-1.45-1.45L26 25.05zm-7 2L17.95 26l-1.45 1.45L15.05 26L14 27.05l1.45 1.45L14 29.95L15.05 31l1.45-1.45L17.95 31L19 29.95l-1.45-1.45L19 27.05z"/>',
        "heroicon-o-eye-slash":
            '<path fill="currentColor" d="M24.8 11.138a8.994 8.994 0 0 0-17.6 0A6.533 6.533 0 0 0 2 17.5V19a1 1 0 0 0 1 1h12a1 1 0 0 0 0-2H4v-.497a4.518 4.518 0 0 1 4.144-4.482l.816-.064l.099-.812a6.994 6.994 0 0 1 13.883 0l.099.813l.815.063A4.496 4.496 0 0 1 23.5 22H7a1 1 0 0 0 0 2h16.5a6.496 6.496 0 0 0 1.3-12.862Z"/><rect width="18" height="2" x="2" y="26" fill="currentColor" rx="1"/>',
    };

    return svgPaths[iconName] || svgPaths["heroicon-o-sun"];
}

// Get weather icon from condition
function getWeatherIcon(condition) {
    if (!condition)
        return { icon: "heroicon-o-sun", color: "text-primary-500" };

    const conditionLower = condition.toLowerCase();

    for (const [key, value] of Object.entries(weatherIconMap)) {
        if (conditionLower.includes(key)) {
            return value;
        }
    }

    return { icon: "heroicon-o-sun", color: "text-primary-500" };
}

// Update weather data with batched DOM updates
function updateWeatherData(weatherData) {
    const weatherSection =
        (window.modalCache && window.modalCache.weatherSection) ||
        document.querySelector(".weather-section");

    if (!weatherSection || !weatherData) {
        return;
    }

    const { current, forecast, error } = weatherData;

    if (error) {
        showWeatherError();
        return;
    }

    // Batch DOM updates using requestAnimationFrame
    requestAnimationFrame(() => {
        // Update current weather
        updateCurrentWeather(current);

        // Update forecast
        updateForecastData(forecast);

        // Update footer with weather timestamp
        updateWeatherFooter(current);
    });
}

// Update current weather display
function updateCurrentWeather(weatherData) {
    const actualCurrentDetails = weatherData.current || {};
    const locationDetails = weatherData.location || {};

    // Get elements (cached for performance)
    const tempElement = document.querySelector(".current-temp");
    const feelsLikeElement = document.querySelector(".feels-like");
    const conditionElement = document.querySelector(".weather-condition");
    const locationElement = document.querySelector(".weather-location");

    if (
        !tempElement ||
        !feelsLikeElement ||
        !conditionElement ||
        !locationElement
    ) {
        return;
    }

    // Update temperature
    tempElement.textContent = actualCurrentDetails.temperature + "°C";

    // Update feels like
    const feelsLikeText =
        window.weatherLocalization?.feels_like || "Feels like";
    feelsLikeElement.textContent =
        feelsLikeText + " " + actualCurrentDetails.feels_like + "°C";

    // Update condition
    conditionElement.textContent = actualCurrentDetails.condition;

    // Update location
    locationElement.textContent =
        locationDetails.city + ", " + locationDetails.country;

    // Update weather icon
    updateWeatherIcon(
        actualCurrentDetails.icon,
        actualCurrentDetails.condition
    );

    // Update weather details
    updateWeatherDetails(actualCurrentDetails);
}

// Update weather icon display
function updateWeatherIcon(iconCode, condition) {
    const iconContainer = document.querySelector(".weather-icon-container");
    if (!iconContainer) return;

    const weatherIcon = getWeatherIcon(condition);

    // Determine background color based on weather condition
    let bgClass = "bg-yellow-100 dark:bg-yellow-900/30";

    if (!condition) {
        condition = "sunny";
    }

    switch (condition.toLowerCase()) {
        case "clear":
        case "sunny":
            bgClass = "bg-yellow-100 dark:bg-yellow-900/30";
            break;
        case "clouds":
        case "cloudy":
            bgClass = "bg-gray-100 dark:bg-gray-700/30";
            break;
        case "rain":
        case "drizzle":
            bgClass = "bg-blue-100 dark:bg-blue-900/30";
            break;
        case "thunderstorm":
            bgClass = "bg-purple-100 dark:bg-purple-900/30";
            break;
        case "snow":
            bgClass = "bg-blue-50 dark:bg-blue-800/30";
            break;
        default:
            bgClass = "bg-yellow-100 dark:bg-yellow-900/30";
    }

    iconContainer.className = `w-12 h-12 rounded-full flex items-center justify-center weather-icon-container ${bgClass}`;

    // Update icon with heroicon SVG
    iconContainer.innerHTML = `
        <svg class="w-8 h-8 ${
            weatherIcon.color
        }" fill="currentColor" viewBox="0 0 32 32">
            ${getHeroiconSVG(weatherIcon.icon)}
        </svg>
    `;
}

// Update weather details (humidity, wind, UV, sunset)
function updateWeatherDetails(current) {
    // Update humidity
    const humidityElement = document.querySelector(".humidity-value");
    if (humidityElement) {
        humidityElement.textContent = current.humidity + "%";
    }

    // Update wind
    const windElement = document.querySelector(".wind-value");
    if (windElement) {
        windElement.textContent = current.wind_speed + " km/h";
    }

    // Update UV index
    const uvElement = document.querySelector(".uv-value");
    if (uvElement) {
        uvElement.textContent = current.uv_index;
    }

    // Update sunset
    const sunsetElement = document.querySelector(".sunset-value");
    if (sunsetElement) {
        sunsetElement.textContent = current.sunset;
    }
}

// Update forecast data
function updateForecastData(weatherData) {
    const forecast = weatherData.forecast || [];
    const forecastContainer = document.getElementById("forecast-container");
    if (!forecastContainer || !Array.isArray(forecast)) {
        return;
    }

    let forecastHTML = "";
    const today = new Date();
    const dayNames = [
        "Sunday",
        "Monday",
        "Tuesday",
        "Wednesday",
        "Thursday",
        "Friday",
        "Saturday",
    ];

    // Limit to 5 days
    const limitedForecast = forecast.slice(0, 5);

    limitedForecast.forEach((day, index) => {
        const isLastItem = index === limitedForecast.length - 1;
        const isToday = index === 0;
        const borderClass =
            isLastItem || isToday
                ? ""
                : "border-b border-gray-200 dark:border-gray-600";

        // Day labeling
        let dayLabel;
        if (index === 0) {
            dayLabel = "Today";
        } else if (index === 1) {
            dayLabel = "Tomorrow";
        } else {
            const forecastDate = new Date(today);
            forecastDate.setDate(today.getDate() + index);
            dayLabel = dayNames[forecastDate.getDay()];
        }

        const todayClasses = isToday
            ? "rounded-lg bg-gray-100 dark:bg-gray-700"
            : "";
        const weatherIcon = getWeatherIcon(day.condition);

        forecastHTML += `
            <div class="flex items-center justify-between p-3 ${todayClasses} ${borderClass}">
                <div class="flex items-center space-x-3">
                    <span class="text-sm text-gray-600 dark:text-gray-400 w-24">${dayLabel}</span>
                    <div class="w-6 h-6 ${weatherIcon.color}">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 32 32">
                            ${getHeroiconSVG(weatherIcon.icon)}
                        </svg>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 flex-1 min-w-0">${
                        day.description
                    }</span>
                </div>
                <div class="text-sm font-medium text-gray-900 dark:text-white text-right">
                    <div>${day.max_temp}°C / ${day.min_temp}°C</div>
                </div>
            </div>
        `;
    });

    forecastContainer.innerHTML = forecastHTML;
}

// Show weather error state
function showWeatherError() {
    const weatherSection =
        (window.modalCache && window.modalCache.weatherSection) ||
        document.querySelector(".weather-section");
    if (weatherSection) {
        const errorTitle =
            window.weatherLocalization?.error_title ||
            "Failed to retrieve weather data";
        const errorMessage =
            window.weatherLocalization?.error_message ||
            "Weather information temporarily unavailable";

        weatherSection.innerHTML = `
            <div class="p-6">
                <div class="text-center text-gray-500 dark:text-gray-400 mb-4">
                    <p>${errorTitle}</p>
                    <p class="text-sm mt-2">${errorMessage}</p>
                </div>
            </div>
        `;
    }
}

// Update weather footer with timestamp
function updateWeatherFooter(weatherData) {
    const weatherElement = document.getElementById("weather-last-updated");
    if (!weatherElement || !weatherData) {
        return;
    }

    // Get the localized text from the global localization object
    const lastUpdatedText =
        window.weatherLocalization?.last_weather_updated ||
        "Last Weather updated";

    // Extract timestamp from weather data
    const timestamp = weatherData.timestamp;
    if (!timestamp) {
        return;
    }

    try {
        // Parse the ISO timestamp and format it
        const date = new Date(timestamp);
        const day = date.getDate();
        const month = date.getMonth() + 1;
        const year = date.getFullYear().toString().slice(-2);
        const formattedDate = `${day}/${month}/${year}`;

        const formattedTime = date.toLocaleTimeString("en-US", {
            hour: "numeric",
            minute: "2-digit",
            hour12: true,
        });

        weatherElement.textContent = `${lastUpdatedText}: ${formattedDate}, ${formattedTime}`;
    } catch (error) {
        console.error("Error formatting timestamp:", error);
        weatherElement.textContent = `${lastUpdatedText}: Unknown`;
    }
}

// Fetch weather data (optimized with client-side caching)
async function fetchWeatherData(retryCount = 0) {
    try {
        const weatherSection =
            (window.modalCache && window.modalCache.weatherSection) ||
            document.querySelector(".weather-section");

        if (!weatherSection) {
            if (retryCount < 5) {
                setTimeout(() => fetchWeatherData(retryCount + 1), 100);
                return;
            }
            return;
        }

        // Check client-side weather cache (valid for 15 minutes)
        const cachedWeatherData = getCachedWeatherData();
        if (cachedWeatherData) {
            updateWeatherData(cachedWeatherData);
            return;
        }

        const response = await fetch("/weather/data", {
            method: "GET",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN":
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content") || "",
            },
        });

        if (!response.ok) {
            throw new Error(`Weather API request failed: ${response.status}`);
        }

        const result = await response.json();

        if (result.success) {
            // Cache the weather data for future use
            cacheWeatherData(result.data);
            updateWeatherData(result.data);
        } else {
            showWeatherError();
        }
    } catch (error) {
        showWeatherError();
    }
}

// Client-side weather data caching
function cacheWeatherData(data) {
    try {
        const cacheData = {
            ...data,
            cachedAt: Date.now(),
            expiresAt: Date.now() + 15 * 60 * 1000, // 15 minutes
        };
        localStorage.setItem("weatherDataCache", JSON.stringify(cacheData));
    } catch (error) {
        // Silent fail
    }
}

function getCachedWeatherData() {
    try {
        const cached = localStorage.getItem("weatherDataCache");
        if (!cached) return null;

        const cacheData = JSON.parse(cached);
        if (Date.now() > cacheData.expiresAt) {
            localStorage.removeItem("weatherDataCache");
            return null;
        }

        return cacheData;
    } catch (error) {
        return null;
    }
}

// Refresh weather data
async function refreshWeatherData() {
    const refreshButton = document.querySelector(
        'button[onclick="refreshWeatherData()"]'
    );
    const originalIcon = refreshButton ? refreshButton.innerHTML : "";

    try {
        if (refreshButton) {
            refreshButton.disabled = true;
            // Show loading state with spinning icon
            refreshButton.innerHTML = `<svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>`;
        }

        // Clear both server-side and client-side caches
        await fetch("/weather/clear-cache", {
            method: "POST",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN":
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content") || "",
            },
        });

        // Clear client-side caches
        localStorage.removeItem("weatherDataCache");
        localStorage.removeItem("weatherLocationCache");

        // Fetch fresh data
        await fetchWeatherData();

        // Show success bubble after successful refresh
        if (refreshButton && window.showRefreshedBubble) {
            window.showRefreshedBubble(refreshButton);
        }
    } catch (error) {
        showWeatherError();
    } finally {
        if (refreshButton) {
            refreshButton.disabled = false;
            // Restore original icon
            refreshButton.innerHTML = originalIcon;
        }
    }
}

// Check user location and fetch weather (optimized with client-side caching)
async function checkUserLocationAndFetchWeather() {
    // Check client-side cache first (valid for 24 hours)
    const cachedLocationData = getCachedLocationData();
    if (
        cachedLocationData &&
        cachedLocationData.hasLocation &&
        cachedLocationData.latitude &&
        cachedLocationData.longitude
    ) {
        setTimeout(() => {
            fetchWeatherData();
        }, 200);
        return;
    }

    try {
        const response = await fetch("/weather/user-location", {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN":
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content") || "",
            },
        });

        const data = await response.json();

        // Cache the location data for future use
        cacheLocationData(data);

        if (data.hasLocation && data.latitude && data.longitude) {
            setTimeout(() => {
                fetchWeatherData();
            }, 200);
        } else {
            detectUserLocation();
            setTimeout(() => {
                fetchWeatherData();
            }, 1000);
        }
    } catch (error) {
        detectUserLocation();
        setTimeout(() => {
            fetchWeatherData();
        }, 1000);
    }
}

// Client-side location data caching
function cacheLocationData(data) {
    try {
        const cacheData = {
            ...data,
            cachedAt: Date.now(),
            expiresAt: Date.now() + 24 * 60 * 60 * 1000, // 24 hours
        };
        localStorage.setItem("weatherLocationCache", JSON.stringify(cacheData));
    } catch (error) {
        // Silent fail
    }
}

function getCachedLocationData() {
    try {
        const cached = localStorage.getItem("weatherLocationCache");
        if (!cached) return null;

        const cacheData = JSON.parse(cached);
        if (Date.now() > cacheData.expiresAt) {
            localStorage.removeItem("weatherLocationCache");
            return null;
        }

        return cacheData;
    } catch (error) {
        return null;
    }
}

// Detect user location
function detectUserLocation() {
    if (!navigator.geolocation) {
        return;
    }

    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const { latitude, longitude } = position.coords;

            try {
                // First, try to get city and country from reverse geocoding
                let city = null;
                let country = null;

                try {
                    const geocodeResponse = await fetch(
                        `https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${latitude}&longitude=${longitude}&localityLanguage=en`
                    );
                    if (geocodeResponse.ok) {
                        const geocodeData = await geocodeResponse.json();
                        city = geocodeData.city || geocodeData.locality || null;
                        country = geocodeData.countryCode || null;
                    }
                } catch (geocodeError) {
                    // Silent fail for reverse geocoding
                }

                // Update location with city and country if available
                await fetch("/weather/location", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                        "X-CSRF-TOKEN":
                            document
                                .querySelector('meta[name="csrf-token"]')
                                ?.getAttribute("content") || "",
                    },
                    body: JSON.stringify({
                        latitude: latitude,
                        longitude: longitude,
                        city: city,
                        country: country,
                    }),
                });
            } catch (error) {
                // Silent fail for location update
            }
        },
        (error) => {
            // Silent fail for geolocation error
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 300000, // 5 minutes
        }
    );
}

// Export functions globally for backward compatibility
window.getWeatherIcon = getWeatherIcon;
window.updateWeatherData = updateWeatherData;
window.updateCurrentWeather = updateCurrentWeather;
window.updateWeatherIcon = updateWeatherIcon;
window.updateWeatherDetails = updateWeatherDetails;
window.updateForecastData = updateForecastData;
window.showWeatherError = showWeatherError;
window.updateWeatherFooter = updateWeatherFooter;
window.fetchWeatherData = fetchWeatherData;
window.refreshWeatherData = refreshWeatherData;
window.checkUserLocationAndFetchWeather = checkUserLocationAndFetchWeather;
window.detectUserLocation = detectUserLocation;
