<?php
// Start the session (make sure session_start() is called)
session_start();

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "demo"; // Replace with your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    // If the user is not logged in, redirect them to the login page or show an error
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];  // Get the user ID from the session

// Process form submission if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Your form data processing here
}
?>


// If the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $food_title = isset($_POST['food_title']) ? $conn->real_escape_string($_POST['food_title']) : null;
    $description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : null;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    $pickup_time = isset($_POST['pickup-time']) ? $conn->real_escape_string($_POST['pickup-time']) : null;
    $pickup_instruction = isset($_POST['pickup_instruction']) ? $conn->real_escape_string($_POST['pickup_instruction']) : null;
    $latitude = isset($_POST['latitude']) ? floatval($_POST['latitude']) : 0.0;
    $longitude = isset($_POST['longitude']) ? floatval($_POST['longitude']) : 0.0;
    $show_up_duration = isset($_POST['show_up_duration']) ? $conn->real_escape_string($_POST['show_up_duration']) : null;

    // Insert food details along with user_id
    $stmt = $conn->prepare("INSERT INTO free_food (user_id, food_title, description, quantity, pickup_time, pickup_instruction, latitude, longitude, show_up_duration) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississdds", $user_id, $food_title, $description, $quantity, $pickup_time, $pickup_instruction, $latitude, $longitude, $show_up_duration);

    if ($stmt->execute()) {
        // Get the last inserted ID for free_food table
        $food_id = $stmt->insert_id;

        // Handle image uploads
        if (!empty($_FILES['food_images']['name'][0])) {
            foreach ($_FILES['food_images']['tmp_name'] as $key => $tmp_name) {
                $file_name = $_FILES['food_images']['name'][$key];
                $file_type = $_FILES['food_images']['type'][$key];
                $image_data = file_get_contents($tmp_name);

                // Insert each image into the free_food_images table
                $stmt_img = $conn->prepare("INSERT INTO free_food_images (food_id, food_image, image_type) VALUES (?, ?, ?)");
                $stmt_img->bind_param("iss", $food_id, $image_data, $file_type);
                $stmt_img->execute();
            }
        }

        echo "Food item  uploaded successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Free Food Form</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css' rel='stylesheet' />
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        .container {
            background-color: white;
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #4f358e;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }
        input[type="text"],
        input[type="number"],
        input[type="file"],
        input[type="datetime-local"],
        input[type="url"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .file-upload input[type="file"] {
            padding: 0;
        }
        .btn {
            display: block;
            width: 100%;
            background-color: #4f358e;
            color: white;
            border: none;
            padding: 15px;
            font-size: 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #3d276c;
        }
        .form-group-inline {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        .form-group-inline div {
            flex: 1;
        }
        .img-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
            height: fit-content;
            width: auto;
        }
        .img-preview {
            width: 150px;
            height: 180px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #ccc;
            position: relative;
        }
        .img-preview-wrapper {
            position: relative;
            display: inline-block;
        }
        .map-container {
            margin-top: 20px;
            height: 300px;
        }
        .location-icon {
            margin-right: 5px;
        }
        .remove-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: #ff4d4d;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            text-align: center;
            font-size: 12px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Free Food</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <!-- Food Images -->
        <div class="form-group">
            <label for="food-images">Upload Food Images</label>
            <input type="file" id="food-images" name="food_images[]" accept="image/*" multiple onchange="previewMultipleImages(event)">
            <div id="image-previews" class="img-preview-container"></div>
        </div>

        <!-- Food Title -->
        <div class="form-group">
            <label for="food-title">Food Title</label>
            <input type="text" id="food-title" name="food_title" placeholder="Enter the name of the food" required>
        </div>

        <!-- Food Description -->
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4" placeholder="Describe the food (e.g., ingredients, condition, etc.)" required></textarea>
        </div>

        <!-- Quantity -->
        <div class="form-group">
            <label for="quantity">Quantity</label>
            <input type="number" id="quantity" name="quantity" placeholder="Enter the quantity" required>
        </div>

        <!-- Pickup Time -->
        <div class="form-group">
            <label for="pickup-time">Pickup Time</label>
            <input type="text" id="pickup-time" name="pickup-time" placeholder="4pm - 10pm" required>
        </div>

        <!-- Pickup Instructions -->
        <div class="form-group">
            <label for="pickup-instruction">Your Pickup Instruction</label>
            <textarea id="pickup-instruction" name="pickup_instruction" rows="3" placeholder="Eg: Don't ring the bell, send me a message when you arrive" required></textarea>
        </div>

        <!-- Location Search Input -->
        <div class="form-group">
            <label for="location">
                <i class="fa fa-map-marker location-icon"></i>Your Location
            </label>
            <div id="geocoder" class="geocoder"></div>
            <br>
            <small><a href="javascript:void(0)" onclick="getCurrentLocation()">Use my current location</a></small>
        </div>

        <!-- Map Preview -->
        <div id="map" class="map-container"></div>

        <!-- Hidden fields to store lat/lng -->
        <input type="hidden" id="latitude" name="latitude">
        <input type="hidden" id="longitude" name="longitude">

        <!-- Show Up For (Visibility Period) -->
        <div class="form-group">
            <label for="show-up">Show Up For</label>
            <select id="show-up" name="show_up_duration" required>
                <!-- Add options dynamically as needed -->
                <option value="midnight">Until Midnight</option>
                <option value="1 hour">1 Hour</option>
                <option value="2 hours">2 Hours</option>
                <option value="4 hours">4 Hours</option>
                <!-- More options... -->
            </select>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn">Submit</button>
    </form>
</div>

<script src='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js'></script>
<script src="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.7.2/mapbox-gl-geocoder.min.js"></script>
<link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.7.2/mapbox-gl-geocoder.css" type="text/css">

<script>
    let selectedFiles = [];

    // Function to display multiple image previews with remove button
    function previewMultipleImages(event) {
        const files = event.target.files;
        const previewContainer = document.getElementById('image-previews');
        previewContainer.innerHTML = ""; // Clear previous previews
        selectedFiles = Array.from(files); // Store selected files in an array

        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imgWrapper = document.createElement('div');
                imgWrapper.classList.add('img-preview-wrapper');

                const imgElement = document.createElement('img');
                imgElement.src = e.target.result;
                imgElement.classList.add('img-preview');

                // Add remove button for each image
                const removeBtn = document.createElement('button');
                removeBtn.textContent = 'x';
                removeBtn.classList.add('remove-btn');
                removeBtn.onclick = function() {
                    removeImage(index);
                };

                imgWrapper.appendChild(imgElement);
                imgWrapper.appendChild(removeBtn);
                previewContainer.appendChild(imgWrapper);
            };
            reader.readAsDataURL(file);
        });
    }

    // Function to remove a specific image from the preview and array
    function removeImage(index) {
        selectedFiles.splice(index, 1); // Remove the selected file from array
        document.getElementById('food-images').files = new FileListItems(selectedFiles); // Update file input with remaining files
        previewMultipleImages({ target: { files: selectedFiles } }); // Re-preview remaining images
    }

    // Helper function to create a FileList object
    function FileListItems(files) {
        const b = new ClipboardEvent("").clipboardData || new DataTransfer();
        for (let i = 0; i < files.length; i++) {
            b.items.add(files[i]);
        }
        return b.files;
    }

    // Initialize Mapbox map without a default location
    mapboxgl.accessToken = 'pk.eyJ1IjoicGVvcGxlcGxhdGZvcm0iLCJhIjoiY20ybGphZHk0MGNmdzJpcHdrcHVyMzh5ZSJ9.t_fL-Pv4n1zsteW466ksTg';

    let map = new mapboxgl.Map({
        container: 'map', // Map container element ID
        style: 'mapbox://styles/mapbox/streets-v11', // Map style
        center: [0, 0], // Initially set to [0, 0]
        zoom: 2 // World view by default
    });

    // Create a draggable marker, initially not placed on the map
    let marker = new mapboxgl.Marker({
        draggable: true
    });

    // Geocoder for searching locations
    let geocoder = new MapboxGeocoder({
        accessToken: mapboxgl.accessToken,
        mapboxgl: mapboxgl,
        marker: false // Prevent default marker
    });

    // Add geocoder search box to the map
    document.getElementById('geocoder').appendChild(geocoder.onAdd(map));

    // When a location is selected from the search box
    geocoder.on('result', function(event) {
        const coordinates = event.result.geometry.coordinates;
        map.setCenter(coordinates);
        marker.setLngLat(coordinates).addTo(map); // Add or move marker to the selected place
        document.getElementById('latitude').value = coordinates[1]; // Set latitude
        document.getElementById('longitude').value = coordinates[0]; // Set longitude
    });

    // Update form fields when the marker is dragged manually
    marker.on('dragend', function() {
        const lngLat = marker.getLngLat();
        document.getElementById('latitude').value = lngLat.lat;
        document.getElementById('longitude').value = lngLat.lng;
    });

    // Function to get the user's current location
    function getCurrentLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const { latitude, longitude } = position.coords;
                map.setCenter([longitude, latitude]);
                map.setZoom(13); // Zoom in to a closer view
                marker.setLngLat([longitude, latitude]).addTo(map);
                document.getElementById('latitude').value = latitude;
                document.getElementById('longitude').value = longitude;
            }, function(error) {
                alert('Unable to retrieve your location: ' + error.message);
            });
        } else {
            alert('Geolocation is not supported by your browser.');
        }
    }
</script>

</body>
</html>
