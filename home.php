<?php
session_start();
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


// Check if the user is logged in by checking the session
if ($_SESSION['user_type'] !== 'regular') {
    header("Location: index.php"); // Redirect to login page if not logged in
    exit();
}

// Fetch user details from the session
$name = $_SESSION['name'];
$id = $_SESSION['id'];
// Welcome message for the logged-in user

// Fetch user profile picture from the database
// Fetch user profile picture from the database
$query = "SELECT profile_pic FROM login WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!empty($user['profile_pic'])) {
    $profile_pic = 'data:image/jpeg;base64,' . base64_encode($user['profile_pic']);
} else {
    $profile_pic = 'default.png'; // Default image if no profile picture
}



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page | People: Community Sharing Platform</title>
    <link rel="stylesheet" href="index.css">
    <link rel="icon" type="image/png" href="people.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

   <style>
    
    .welcome-msg{
        font-weight: 4rem;
        size: 30px;
    }
    body {
    padding-top: 100px; /* This value should match or exceed the height of the header */
    }

    /* logo Styles */
    .floating-contact {
        position: fixed;
        top: 0;
        left:  19rem;
        text-align: center;
        z-index: 1000;
    }

    .contact-btn img {
        
        width: 7.5rem;
        height: auto;
    }

   

        /* Floating Contact Button Styles */

    .profile-btn {
        position: fixed;
        top: 25px;
        right:  60px;
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

    .profile-btn:hover {
        transform: scale(1.05);
    }

    .profile-btn img {
        width: 4.1rem;
        border: 4px pink solid;
        height: 4.1rem;
    }

  


    /* Overlay for Profile Form */
    .overlay {
        visibility: hidden;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
        opacity: 0;
        transition: opacity 0.5s ease, visibility 0.5s ease;
    }

    .overlay.active {
        visibility: visible;
        opacity: 1;
    }

    .form-container {
        background-color: white;
        padding: 30px;
        border-radius: 10px;
        max-width: 400px;
        width: 90%;
        text-align: center;
        transform: translateY(-50px);
        transition: transform 0.5s ease, opacity 0.5s ease;
    }

    .form-container.active {
        transform: translateY(0);
        opacity: 1;
    }

    .close-btn {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 2rem;
        color: #333;
        cursor: pointer;
        transition: color 0.3s;
    }

    .close-btn:hover {
        color: #ff009a;
    }
    .p{
        display: none;
    }

    /* Responsive Design for Tablets (768px and below) */
@media (max-width: 768px) {
    /* Adjust header layout */
    .header {
        flex-direction: column;
        align-items: center;
    }

    .logo {
        font-size: 2.5rem;
        margin-bottom: 10px;
    }

    .nav {
        flex-direction: column;
        gap: 10px;
    }

    .nav a {
        font-size: 1rem;
    }

    /* Profile and Contact Buttons */
    .profile-btn {
        top: 15px;
        right: 30px;
    }

    .profile-btn img {
        width: 3.5rem;
        height: 3.5rem;
    }

    .floating-contact {
        top: 20px;
        left: 10px;
    }

    .contact-btn img {
        width: 5rem;
        height: auto;
    }

    /* Footer adjustments */
    .footer-top {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .footer-menu {
        flex-direction: column;
        align-items: center;
        margin-top: 20px;
    }

    .menu-list {
        margin: 10px 0;
        text-align: center;
    }

    .social-icons {
        margin: 15px 0;
    }

    .footer-brand, .footer-bottom {
        text-align: center;
    }
}

/* Responsive Design for Mobile Devices (435px and below) */
@media (max-width: 435px) {
    /* Adjust layout for small screens */
    .logo {
        margin: 5px;
        font-size: 1.5rem;
        margin-left: -15rem;
    }

    .profile-btn {
        margin-top: 8px;
        margin-right: 20px;
    }
    .contact-btn{
        display: none;
    }

    .p{
        display: flex;
        
    }
    .p img{
        width: 60px;
        margin-left: 5.5rem;
        margin-top: -7px;
    }
    
    /* Hide the navigation bar on small screens */
    .nav {
        display: none;
    }

    /* Hero Section Adjustments */
    .hero-title {
        font-size: 1.8rem;
        text-align: center;
    }

    .hero p {
        font-size: 1rem;
        text-align: center;
        padding: 0 10px;
    }

    /* Profile and Contact Buttons */
    .profile-btn {
        top: 10px;
        right: 10px;
        width: 3rem;
        height: 3rem;
    }

    .profile-btn img {
        width: 3rem;
        height: 3rem;
    }
    .start .section-title {
        margin-top: -80px;
        font-size: 1.5rem; 
    }

    .start p {
        font-size: 0.8rem; /* Smaller font size for the paragraph */
    }
    /* Footer adjustments */
    .footer-container {
        max-width: 100%;
    }

    .footer-top {
        flex-direction: column;
        align-items: center;
    }

    .footer-menu {
        display: none; /* Hide the footer menu for small screens */
    }

    .footer ul {
        display: flex;
        justify-content: center;
        padding: 0;
        margin: 0;
        list-style: none;
        gap: 15px;
    }

    .footer ul li a {
        color: #aaa;
        font-size: 14px;
        text-decoration: none;
        transition: 0.3s ease;
    }

    .footer ul li a:hover {
        color: var(--main-color); /* Hover effect for mobile links */
    }

    .social-icons a {
        font-size: 1.2rem;
        margin: 0 8px;
    }

    .footer-bottom p {
        font-size: 0.8rem;
    }
}

   </style>
</head>
<body>
    <header class="header">
        <h1 class="logo">People</h1>
        <nav class="nav" id="nav">
            <a href="#food">GiveFood</a>
            <a href="#">Support Homeless</a>
            <a href="#">Community</a>
            <a href="#">Donate</a>
        </nav>
    </header>

   <section class="start">
    <div class="container">
        <!-- You can add more content or actions here -->
        <h2 class="section-title"> Hello, <span> 
            <?php echo ucfirst($name); ?>! </span> <br> 
            What would you like to do today?</h2>
        <p>Explore, share, or donate with the community.</p>

    </div>
    </section>
<!--  Footer Section -->
<footer class="footer">
    <div class="footer-container">
        <div class="footer-top">
            <div class="footer-brand">
                <h2>People</h2>
                <p>Connecting people, reducing waste, and supporting communities.</p>
            </div>
            <div class="footer-menu">
                <ul class="menu-list">
                    <li><a href="#">About</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
                <ul class="menu-list">
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                </ul>
                <ul class="menu-list">
                    <li><a href="#">Food Sharing</a></li>
                    <li><a href="#">Homeless Support</a></li>
                    <li><a href="#">Donate</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
        <ul class="list">
            <li><a href="#">About </a></li>
            <li><a href="#">Contact </a></li>
            <li><a href="#">Careers</a></li>
        </ul>
        <ul class="list">
            <li><a href="#">Help Center </a></li>
            <li><a href="#">Privacy Policy</a></li>
            <li><a href="#">Privacy Policy</a></li>
        </ul>
        
            <div class="social-icons">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fa fa-whatsapp" style="font-size:28px;" ></i></a>
            </div>
            <p>&copy; 2024 People. All Rights Reserved.</p>
        </div>
    </div>
</footer>
</body>
<script>
    // Get the elements
    const profileOverlay = document.getElementById('profileOverlay');
    const profileBtn = document.querySelector('.profile-btn');
    const closeProfileOverlay = document.getElementById('closeProfileOverlay');
    const profileImage = document.getElementById('profileImage');
    const profilePicInput = document.getElementById('profilePicInput');

    // Open the profile overlay when clicking the profile button
    profileBtn.addEventListener('click', function(event) {
        event.preventDefault();
        profileOverlay.classList.add('active');
    });

    // Close the profile overlay when clicking the close button
    closeProfileOverlay.addEventListener('click', function() {
        profileOverlay.classList.remove('active');
    });

    // Close the profile overlay when clicking outside the form
    window.addEventListener('click', function(event) {
        if (event.target === profileOverlay) {
            profileOverlay.classList.remove('active');
        }
    });

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

     <!-- Floating Contact Button -->
    <div class="floating-contact">
        <a href="home.php" class="contact-btn">
            <img src="SiteIcons/logo.png" alt="logo Us">
        </a>
    <!-- profile -->
    <div class="profile">
    <a href="profile.php" class="profile-btn">
        <img src="<?php echo $profile_pic; ?>" alt="Profile Picture" width="100" style="border-radius: 50%; object-fit: cover;">
        </a>
    </div>

    <!-- Floating Contact Button -->
    <div class="icon-p">
        <a href="home.php" class="p">
            <img src="SiteIcons/logo.png" alt="logo Us">
        </a>

</div>

</html>
