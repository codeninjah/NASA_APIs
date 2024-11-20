<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NASA</title>
    <link rel="stylesheet" href="nasa_styles.css"> <!-- Link to CSS file -->
</head>
<body>
    <h1>NASA API - Images</h1>
    <a href="index.php" id="apod-link">To Astronomy Picture of the Day</a>
    <form name="form" action="" method="post">
        <input type="text" name="subject" id="subject" value="">
    </form>


    <?php
// The main container 
echo '<div class="main-container">';

// Set the search term and page number
$search_term = $_GET['subject'] ?? '';
$current_page = $_GET['page'] ?? 1;

// Set the API endpoint
$url = "https://images-api.nasa.gov/search?q=" . urlencode($search_term) . "&page=" . $current_page . "&page_size=20";

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

// Decode the JSON response
$data = json_decode($response, true);

// Add navigation links
echo '<div class="filter-links">';
    echo '<a href="search.php?subject=' . urlencode($search_term) . '&page=' . 1 . '">Show All Results</a> | ';
    echo '<a href="images.php?subject=' . urlencode($search_term) . '&page=' . 1 . '">Show Only Images</a> | ';
    echo '<a href="videos.php?subject=' . urlencode($search_term) . '&page=' . 1 . '">Show Only Videos</a>';
echo '</div>';

// Display the results
if (isset($data['collection']['items']) && count($data['collection']['items']) > 0) {
    foreach ($data['collection']['items'] as $item) {
        $manifestUrl = $item['href'] ?? '';
        $mp4Found = false;

        // Fetch the manifest.json file
        if ($manifestUrl) {
            $manifestResponse = file_get_contents($manifestUrl);
            if ($manifestResponse !== false) {
                $manifestData = json_decode($manifestResponse, true);

                // Check if the manifest contains an .mp4 link
                if (is_array($manifestData)) {
                    foreach ($manifestData as $resource) {
                        if (is_string($resource) && preg_match('/\.mp4$/i', $resource)) {
                            $mp4Found = true;
                            break;
                        }
                    }
                }
            }
        }

        // If no .mp4 link was found, display the image
        if (!$mp4Found && isset($item['links']) && is_array($item['links'])) {
            echo '<div class="div-container">';
            echo "<h2>Title: " . htmlspecialchars($item['data'][0]['title']) . "</h2>";
            echo '<button type="button" class="collapsible">Description: </button>';
            echo '<div class="content">';
            echo "<p style='font-family:  Arial; font-size: 15px; font-weight: 500;'>" . htmlspecialchars($item['data'][0]['description']) . "</p>";

            // Display the image
            foreach ($item['links'] as $link) {
                if (isset($link['href'])) {
                    echo '<img width="100%" height="auto" src="' . htmlspecialchars($link['href']) . '" alt="Image">';
                    break;
                }
            }
            echo "</div>";
            echo '</div>';
        }
    }
    // Pagination controls
    echo '<div class="pagination">';
    if ($current_page > 1) {
        $prev_page = $current_page - 1;
        echo '<a href="?page=' . $prev_page . '&subject=' . urlencode($search_term) . '">Previous</a> ';
    }
    if (count($data['collection']['items']) == 20) {
        $next_page = $current_page + 1;
        echo '<a href="?page=' . $next_page . '&subject=' . urlencode($search_term) . '">Next</a>';
    }
    echo '</div>';
} else {
    echo "No image items found.";
}

echo '</div>'; // Close the main div

// Footer
echo '<footer style="font-family: Arial";>';
echo '<p>This page uses the NASA API and its purpose is to search for videos and images posted by NASA. All media copyright goes to the copyright holder(s).</p>';
echo '<div id="copyright">&copy; Alexandru Florin </div>';
echo '</footer>';
?>


<script>

/* Code for the dynamic collapse */
var coll = document.getElementsByClassName("collapsible");
var i;

for (i = 0; i < coll.length; i++) {
  coll[i].addEventListener("click", function() {
    this.classList.toggle("active");
    var content = this.nextElementSibling;
    if (content.style.display === "block") {
      content.style.display = "none";
    } else {
      content.style.display = "block";
    }
  });
}
</script>
</body>
</html>