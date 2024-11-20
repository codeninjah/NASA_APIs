<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apod - Astronomy Picture of the Day</title>
    <link rel="stylesheet" href="apod.css">
</head>
<body>
    <div class="main-container">
        <h1>Apod - Astronomy Picture of the Day</h1>
        <a href="search.php">To NASA images API</a>
            <div class="apod">
                <?php
                    // Set the API endpoint
                    $url = "https://api.nasa.gov/planetary/apod?api_key=DEMO_KEY";

                    // Initialize cURL
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $response = curl_exec($ch);
                    curl_close($ch);

                    // Decode the JSON response
                    $data = json_decode($response, true);

                    if(isset($data['url']) && isset($data['title']) && $data['explanation']) {
                        echo '<img src="' . $data['url'] . '"/>';
                        echo '<h4>' . $data['title'] . '</h4>';
                        echo '<p>' . $data['explanation'] . '</p>';
                    } else {
                        echo "<h1>No Picture of the day!";
                    }

                ?>
            </div>
    </div>
    <footer>
        <p>&copy; Alexandru Florin</p>
    </footer>
</body>
</html>