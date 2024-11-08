<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "demo"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: index.php"); // Redirect to login page if not logged in
    exit();
}

// Fetch user details from the database using the session 'id'
$id = $_SESSION['id'];
$query = "SELECT * FROM login WHERE id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    die("User not found.");
}
$user = $result->fetch_assoc();

// Convert the profile picture from the database (BLOB) to a base64-encoded image for preview
if (!empty($user['profile_pic'])) {
    $profile_pic = 'data:image/jpeg;base64,' . base64_encode($user['profile_pic']);
} else {
    // Default image if no profile picture is available
    $profile_pic = 'default.png'; // Ensure this file exists in your project directory
}

// Handle profile update form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : $user['password'];
    
    // Handle file upload (profile picture)
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['size'] > 0) {
        $profile_pic_data = file_get_contents($_FILES['profile_pic']['tmp_name']);
        $update_query = "UPDATE login SET name = ?, email = ?, password = ?, profile_pic = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param('sssbi', $name, $email, $password, $null, $id);
        $stmt->send_long_data(3, $profile_pic_data);
    } else {
        // Update without changing profile picture
        $update_query = "UPDATE login SET name = ?, email = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param('sssi', $name, $email, $password, $id);
    }
    
    if ($stmt->execute()) {
        // Update session with the new email and name
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;

        header("Location: home.php"); // Redirect after update
        exit();
    } else {
        echo "Error updating profile: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | People: Community Sharing Platform</title>
    <link rel="icon" type="image/png" href="people.png">
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            background-color: whitesmoke;
        }
        
        /* Form Container */
        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 400px;
            width: 90%;
            position: fixed; /* Fixed position to stay on the screen */
            top: 50%;  /* Center vertically */
            left: 50%; /* Center horizontally */
            transform: translate(-50%, -50%); /* Adjust for width and height of the form */
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            text-align: center;
            transition: transform 0.5s ease, opacity 0.5s ease;
        }

        .form-container.active {
            transform: translateY(0); 
            opacity: 1; 
        }
        h2 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: #06C167;
        }
        input[type="text"], input[type="email"], input[type="password"], input[type="file"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        input[type="file"] {
            display: none; /* Hide the file input field */
        }
        button {
            background-color: #4f358e;
            color: white;
            border: none;
            margin-bottom: 10px;
            margin-top: 10px;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #4f358e;
            transform: scale(1.05);
        }
        .profile-preview {
            text-align: center;
            margin-bottom: 20px;
        }
        label {
            display: block;
            text-align: left; /* Align the labels to the left */
            margin-bottom: 5px; /* Add a little space below the label */
            font-weight: bold; /* Make the labels bold for better readability */
        }
        .profile-preview img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 7px pink solid;
            cursor: pointer; 
        }
     /*  back Button Styles */

        .back-btn {
            position: fixed;
            top: 105px;
            left:  38rem;
            background-color: none; 
            border-radius: 50%;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s, transform 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            width: auto;
            height: 4rem;
            text-decoration: none; 
        }

        .back-btn:hover {
            transform: scale(1.05);
        }

        .back-btn img {
            width: 2.8rem;
            height: 2.8rem;
        }


     /*  logout Button Styles */

        .logout-btn {
            position: fixed;
            top: 103px;
            right:  38.5rem;
            background-color: none; 
            border-radius: 50%;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s, transform 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            width: auto;
            height: 4rem;
            text-decoration: none; 
        }

        .logout-btn:hover {
            transform: scale(1.05);
        }

        .logout-btn img {
            width: 2.3rem;
            height: 2.3rem;
        }

        /* uploadimg */
        .upload-btn {
            position: fixed;
            top: 294px;
            left:  51rem;
            background-color: none; 
            border-radius: 50%;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s, transform 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            width: auto;
            height: 4rem;
            text-decoration: none; 
        }

        .upload-btn:hover {
            transform: scale(1.05);
        }

        .upload-btn img {
            width: 2.8rem;
            height: 2.8rem;
        }

        span{
            color: #ff009a;
        }

        /* Responsive Design for Tablets (768px and below) */
    @media (max-width: 768px) {
        /* Form Container */
        .form-container {
            width: 80%; /* Adjust form width for tablets */
            padding: 20px; /* Reduce padding for smaller screens */
        }

        h2 {
            font-size: 1.5rem; /* Smaller heading size */
        }

        input[type="text"], input[type="email"], input[type="password"], input[type="file"] {
            font-size: 0.9rem; /* Adjust input field font size */
            padding: 8px; /* Reduce padding for inputs */
        }

        .profile-preview img {
            width: 120px; /* Smaller profile image on tablets */
            height: 120px;
        }

        button {
            padding: 8px 15px; /* Adjust button padding */
            font-size: 0.9rem;
        }

        /* Adjust position of floating buttons */
        .back-btn, .logout-btn, .upload-btn {
            width: 3rem;
            height: 3rem;
        }

        .back-btn img, .logout-btn img, .upload-btn img {
            width: 30px; /* Adjust image size for buttons */
            height: 30px;
        }

        /* Floating buttons position adjustments */
        .back-btn {
            top: 15px;
            left: 10px;
        }

        .logout-btn {
            top: 15px;
            right: 10px;
        }

        .upload-btn {
            top: 240px;
            left: calc(50% - 1.5rem); /* Center the upload button horizontally */
        }
    }

    /* Responsive Design for Mobile Devices (435px and below) */
    @media (max-width: 435px) {
        /* Form Container */
        .form-container {
            position: fixed;
            width: 76%; /* Make form full-width for mobile */
            padding: 18px; /* Reduce padding further */
        }

        h2 {
            font-size: 1.2rem; 
        }

        input[type="text"], input[type="email"], input[type="password"], input[type="file"] {
            font-size: 0.8rem; /* Reduce input font size */
            padding: 6px; /* Further reduce padding for inputs */
            width: 95%;
        }

        .profile-preview img {
            width: 100px; /* Smaller profile image for mobile */
            height: 100px;
        }

        button {
            padding: 6px 10px; /* Smaller buttons for mobile */
            font-size: 0.8rem;
        }

        /* Adjust position of floating buttons */
        .back-btn, .logout-btn, .upload-btn {
            width: 2.5rem;
            height: 2.5rem;
        }

        .back-btn img, .logout-btn img, .upload-btn img {
            width: 2rem; /* Further reduce button image size */
            height: 2rem;
        }

        /* Floating buttons position adjustments for mobile */
        .back-btn {
            top: 70px;
            margin-left: 25px;
        }

        .logout-btn {
            top: 45px;
            margin-right: 20px;
        }

        .upload-btn {
            top: 203px;
            left: calc(50% - 1.25rem); 
        }
    }

    </style>
</head>
<body>
    <div class="form-container">
         <h2 style="text-align: center;">Your <span>Profile</span> </h2>
        <!-- Profile Picture Preview -->
        <div class="profile-preview">
            <img id="profileImage" src="<?php echo $profile_pic; ?>" alt="Profile Picture" title="Click to change profile picture">
            <p>Profile Picture</p>
        </div>

        <!-- Form to Update Profile -->
        <form action="profile.php" method="POST" enctype="multipart/form-data">
            <label for="name">Name:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            
            <label for="email">Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            
            <label for="password">Password:</label>
            <input type="password" name="password" placeholder="Enter new password (leave blank if unchanged)">
            
            <input type="file" name="profile_pic" id="profilePicInput" accept="image/*">
            
            <button type="submit">Update <span style="color: yellowgreen;">Profile</span></button>
        </form>
        
    </div>

    <script>
        // Get the image element and the hidden file input
        const profileImage = document.getElementById('profileImage');
        const profilePicInput = document.getElementById('profilePicInput');

        // When the image is clicked, trigger the file input click
        profileImage.addEventListener('click', () => {
            profilePicInput.click();
        });

        // When a new file is selected, update the image preview
        profilePicInput.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profileImage.src = e.target.result; // Set the new image
                }
                reader.readAsDataURL(file); // Read the file and update preview
            }
        });
    </script>
</body>
       
<!--  back Button -->
<div class="floating-contact">
            <a href="home.php" class="back-btn">
                <img src="SiteIcons/left.png" alt="back"></a>
    </div>
<!-- logout Button -->
<div class="floating-contact">
            <a href="logout.php" class="logout-btn">
                <img src="SiteIcons/logout.png" alt="logout"></a>
    </div>
    <!-- upload Button -->
<div class="floating-contact">
            <a href="" id="profileImage" class="upload-btn">
                <img src="SiteIcons/upload.png" alt="logout"></a>
    </div>
</html>
