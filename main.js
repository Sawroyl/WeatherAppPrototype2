const apiKey = "5cb1e7c1958db1327fd255ff75b6264b";
const apiUrl = "https://api.openweathermap.org/data/2.5/weather?units=metric&q=";

var defaultLocation = "Bedworth"
d = new Date()
document.getElementById("fullDate").innerHTML = d;
const searchBox = document.querySelector(".search input");
const searchButton = document.querySelector(".search button");
const weatherIcon = document.querySelector(".weather-icon");
async function checkWeather(city) {
    try {
        const response = await fetch(apiUrl + city + `&appid=${apiKey}`);
        var data = await response.json();
        if (data.cod == "200") {
            console.log(data);
            localStorage.setItem(city, JSON.stringify(data));

            document.getElementById("city").innerHTML = "City" + ": " + data.name;
            document.getElementById("temp").innerHTML = data.main.temp + " °C";
            document.getElementById("humidity").innerHTML = data.main.humidity + " %";
            document.getElementById("wind").innerHTML = data.wind.speed + " Km/H";
            document.getElementById("pressure").innerHTML = data.main.pressure + " Pa";

            if (data.weather[0].main == "Clouds") {
                weatherIcon.src = "https://sawroyl.github.io/WeatherApp/Weather/img/cloudy.png"
            }
            else if (data.weather[0].main == "Clear") {
                weatherIcon.src = "https://sawroyl.github.io/WeatherApp/Weather/img/haze.png"
            }
            else if (data.weather[0].main == "Rain") {
                weatherIcon.src = "https://sawroyl.github.io/WeatherApp/Weather/img/rain.png"
            }
            else if (data.weather[0].main == "Drizzle") {
                weatherIcon.src = "https://sawroyl.github.io/WeatherApp/Weather/img/drizzle.png"
            }
            else if (data.weather[0].main == "Snow") {
                weatherIcon.src = "https://sawroyl.github.io/WeatherApp/Weather/img/snow.png"
            }
            else if (data.weather[0].main == "Thunderstorm") {
                weatherIcon.src = "https://sawroyl.github.io/WeatherApp/Weather/img/storm.png"
            }
            else if (data.weather[0].main == "Atmosphere") {
                weatherIcon.src = "https://sawroyl.github.io/WeatherApp/Weather/img/foggy.png"
            }
            document.querySelector(".error").style.display = "none";
            // document.querySelector(".weather").style.display = "block";

        }
        else if (data.cod == "400") {
            document.querySelector(".error").style.display = "block";
            document.querySelector(".weather").style.display = "none";

        }

    } catch (error) {
        console.error("Error fetching weather data:", error);
    }

}
// Add an event listener to the form submission
document.querySelector("form").addEventListener("submit", (event) => {
    event.preventDefault(); // Prevent form submission
    const city = searchBox.value || defaultLocation; // Use the submitted city or default
    checkWeather(city);
});
checkWeather(defaultLocation)
searchButton.addEventListener("click", () => {
    checkWeather(searchBox.value)
})



