<?php
$apiKey = "5cb1e7c1958db1327fd255ff75b6264b";
$apiUrl = "https://api.openweathermap.org/data/2.5/weather?units=metric&q=";
$defaultLocation = "";
echo $defaultLocation;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['city'])) {
    $defaultLocation = $_POST['city'];
}
else{
    $defaultLocation = "Bedworth";
}
echo $defaultLocation;
// Connect to the database
$servername = "localhost";
$username = "admin";  // Replace with your actual database username
$password = "2358827";  // Replace with your actual database password
$dbname = "saral";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    echo "Connected to the database successfully<br>";

    function getWeatherData($city, $apiKey) {
        global $apiUrl;
        $url = $apiUrl . $city . "&appid=" . $apiKey;
        $response = file_get_contents($url);
        return json_decode($response, true);
    }

    $weatherData = getWeatherData($defaultLocation, $apiKey);

    if ($weatherData['cod'] == 200) {
        $city = $weatherData['name'];
        $date = date('Y-m-d H:i:s');
        $temperature = $weatherData['main']['temp'];
        $humidity = $weatherData['main']['humidity'];
        $windSpeed = $weatherData['wind']['speed'];
        $pressure = $weatherData['main']['pressure'];
        $weatherMain = $weatherData['weather'][0]['main'];

        echo "City: " . $city . "<br>";
        echo "Date: " . $date . "<br>";
        echo "Temperature: " . $temperature . " °C<br>";
        echo "Humidity: " . $humidity . "%<br>";
        echo "Wind Speed: " . $windSpeed . " Km/H<br>";
        echo "Pressure: " . $pressure . " Pa<br>";
        echo "Weather Main: " . $weatherMain . "<br>";

        // Check if a record with the same location and date exists
        $existingRecord = $conn->prepare("SELECT id FROM city_data WHERE location = ? AND date = ?");
        $existingRecord->bind_param("ss", $city, $date);
        $existingRecord->execute();
        $existingRecord->store_result();

        if ($existingRecord->num_rows > 0) {
            // Update the existing record
            $stmt = $conn->prepare("UPDATE city_data SET weather_main = ?, temperature = ?, humidity = ?, pressure = ?, wind_speed = ? 
                                    WHERE location = ? AND date = ?");
            $stmt->bind_param("sdiiisss", $weatherMain, $temperature, $humidity, $pressure, $windSpeed, $city, $date);
        } else {
            // Insert a new record
            $stmt = $conn->prepare("INSERT INTO city_data (location, date, weather_main, temperature, humidity, pressure, wind_speed) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssdiii", $city, $date, $weatherMain, $temperature, $humidity, $pressure, $windSpeed);
        }

        if ($stmt->execute()) {
            echo "Weather data inserted/updated successfully<br>";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
        
        // Extract 7 days worth of data for the specific city
        $sevenDaysAgo = date('Y-m-d H:i:s', strtotime('-7 days'));
$dataQuery = $conn->prepare("SELECT * FROM city_data WHERE location = ? AND date >= ? ORDER BY date DESC LIMIT 7");
$dataQuery->bind_param("ss", $city, $sevenDaysAgo);
$dataQuery->execute();
$result = $dataQuery->get_result();
        echo "<h2>Weather data for the last 7 days:</h2>";
        echo "<table border='1'>";
        echo "<tr><th>Location</th><th>Date</th><th>Temperature (°C)</th><th>Humidity (%)</th><th>Wind Speed (Km/H)</th><th>Pressure (Pa)</th><th>Weather</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['location'] . "</td>";
            echo "<td>" . $row['date'] . "</td>";
            echo "<td>" . $row['temperature'] . "</td>";
            echo "<td>" . $row['humidity'] . "</td>";
            echo "<td>" . $row['wind_speed'] . "</td>";
            echo "<td>" . $row['pressure'] . "</td>";
            echo "<td>" . $row['weather_main'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        $dataQuery->close(); // Close the data query statement
    }
} catch (mysqli_sql_exception $e) {
    die("Database error: " . $e->getMessage());
}

$conn->close(); // Close the database connection
?>
