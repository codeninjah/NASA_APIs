<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NASA</title>
    <link rel="stylesheet" href="nasa_styles.css"> <!-- Link to CSS file -->
</head>
<body>
    <h1>NASA API</h1>
    <form name="form" action="" method="post">
        <input type="text" name="subject" id="subject" value="">
    </form>


<?php
// The main container 
echo '<div class="main-container">';

// Get the search term from the form submission or the current query parameter
$search_term = isset($_POST['subject']) ? $_POST['subject'] : (isset($_GET['subject']) ? $_GET['subject'] : '');
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page); // Ensure the page is at least 1

// Set the number of results per page
$page_size = 20;

// Set the API endpoint with proper URL encoding for the search term
$url = "https://images-api.nasa.gov/search?q=" . urlencode($search_term) . "&page_size=$page_size&page=$current_page";

// Initialize cURL
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the request
$response = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
} else {
    // Decode the JSON response
    $data = json_decode($response, true);
    

    // Get the total number of items from the metadata
    $total_items = isset($data['collection']['metadata']['total_hits']) ? $data['collection']['metadata']['total_hits'] : 0;
    $total_pages = ceil($total_items / $page_size); // Calculate total pages

    // Add a text outputting what the search keyword is
    echo "<h3>Results for <em>" . htmlspecialchars($search_term) . "</em></h3>";
    echo "<h4> Your search has returned <em>" . $total_items . "</em> items. There are totally <em>" . $total_pages . "</em> pages. ";


// Display the results
if (isset($data['collection']['items']) && count($data['collection']['items']) > 0) {
    // Add navigation links
    echo '<div class="filter-links">';
        echo '<a href="videos.php?subject=' . urlencode($search_term) . '&page=' . 1 . '">Show Only Videos</a> | ';
        echo '<a href="images.php?subject=' . urlencode($search_term) . '&page=' . 1 . '">Show Only Images</a>';
    echo '</div>';
    foreach ($data['collection']['items'] as $item) {
        echo '<div class="div-container">';
        echo "<h2>Title: " . htmlspecialchars($item['data'][0]['title']) . "</h2>";
        echo '<button type="button" class="collapsible">Description: </button>';
        echo '<div class="content">';
        echo "<p>" . htmlspecialchars($item['data'][0]['description']) . "</p>";

        // Check if 'links' are present in the item
        if (isset($item['href'])) {
            $manifestUrl = htmlspecialchars($item['href']);
            $mp4Found = false;

            // Fetch the manifest.json file
            $manifestResponse = file_get_contents($manifestUrl);
            if ($manifestResponse !== false) {
                $manifestData = json_decode($manifestResponse, true);

                // Check if the manifest contains an .mp4 link
                if (is_array($manifestData)) {
                    foreach ($manifestData as $resource) {
                        if (is_string($resource) && preg_match('/\.mp4$/i', $resource)) {
                            // Display the video player if an .mp4 link is found
                            echo '<video width="100%" height="auto" controls>';
                            echo '<source src="' . htmlspecialchars($resource) . '" type="video/mp4">';
                            echo 'Your browser does not support the video tag.';
                            echo '</video>';
                            $mp4Found = true;
                            break; // Stop searching if an .mp4 link is found
                        }
                    }
                }
            }

            // If no .mp4 link was found, display the image
            if (!$mp4Found && isset($item['links']) && is_array($item['links'])) {
                foreach ($item['links'] as $link) {
                    if (isset($link['href'])) {
                        echo '<img width="100%" height="auto" src="' . htmlspecialchars($link['href']) . '" alt="Image">';
                        break;
                    }
                }
            }
        }

        echo "</div>";
        echo '</div>';
    }

        // Pagination controls
        echo '<div class="pagination">';
        // Previous page link
        if ($current_page > 1) {
            $prev_page = $current_page - 1;
            echo '<a href="?page=' . $prev_page . '&subject=' . urlencode($search_term) . '">Previous</a> ';
        }
        // Next page link
        if (count($data['collection']['items']) == $page_size) {
            $next_page = $current_page + 1;
            echo '<a href="?page=' . $next_page . '&subject=' . urlencode($search_term) . '">Next</a>';
        }
        echo '</div>';
    } else {
        echo "No items found.";
    }
}

echo '</div>'; // Close the main div

echo '<div id="copyright"> &copy; Alexandru Florin </div>';

// Close cURL session
curl_close($ch);

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
