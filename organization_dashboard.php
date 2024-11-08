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

// Check if the user is logged in as an organization
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'organization') {
    header("Location: index.php"); // Redirect to login page if not logged in as an organization
    exit();
}

// Fetch organization details from the session and database
$org_id = $_SESSION['id'];
$query = "SELECT * FROM organization WHERE org_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param('i', $org_id);
$stmt->execute();
$result = $stmt->get_result();
$organization = $result->fetch_assoc();

// Debugging: Check if data is retrieved
if (!$organization) {
    die("Organization data not found for org_id: " . $org_id);
}

// Validate organization data before displaying
$org_name = !empty($organization['org_name']) ? $organization['org_name'] : "Not Available";
$org_type = !empty($organization['org_type']) ? $organization['org_type'] : "Not Available";
$org_website = !empty($organization['website']) ? $organization['website'] : "Not Available";
$org_services = !empty($organization['services']) ? $organization['services'] : "Not Available";

// Convert the profile picture from the database (BLOB) to a base64-encoded image for preview
$profile_pic = !empty($organization['profile_pic']) ? 'data:image/jpeg;base64,' . base64_encode($organization['profile_pic']) : 'default.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Home | People: Community Sharing Platform</title>
    <link rel="icon" type="image/png" href="people.png">
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="-logo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            padding-top: 100px;
            background-color: #f9f9f9;
            font-family: 'Poppins', sans-serif;
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
        /* Organization Dashboard Styles */
        .dashboard-container {
            text-align: center;
            margin: 50px auto;
            max-width: 800px;
            background-color: #fff;
            padding: 30px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .section-title {
            font-size: 2.5rem;
            color: #06C167;
        }

        .organization-details {
            margin-top: 20px;
        }

        .organization-details h3 {
            font-size: 1.8rem;
            color: #4f358e;
        }

        .organization-details p {
            font-size: 1.2rem;
            margin: 10px 0;
            color: #333;
        }

        /* Section for actions or organization-related functions */
        .actions-section {
            margin-top: 40px;
        }

        .actions-section a {
            background-color: #4f358e;
            color: white;
            padding: 12px 25px;
            margin: 15px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            font-size: 1.1rem;
            transition: background-color 0.3s ease;
        }

        .actions-section a:hover {
            background-color: #45a049;
        }

        .logout-link {
            display: inline-block;
            margin-top: 20px;
            font-size: 1rem;
            text-decoration: none;
            color: #333;
        }

        .logout-link:hover {
            text-decoration: underline;
        }

        /* Footer Styles */
        .footer {
            background-color: #333;
            color: white;
            padding: 40px 0;
        }

        .footer-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .footer h2 {
            color: #06C167;
            font-size: 1.5rem;
        }

        .footer p {
            font-size: 1rem;
            color: #bbb;
            margin-top: 10px;
        }

        .footer-menu {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .footer-menu ul {
            list-style: none;
            padding: 0;
        }

        .footer-menu ul li {
            margin: 5px 0;
        }

        .footer-menu ul li a {
            color: #bbb;
            text-decoration: none;
        }

        .footer-menu ul li a:hover {
            text-decoration: underline;
        }

        .social-icons {
            margin-top: 20px;
        }

        .social-icons a {
            color: white;
            margin-right: 15px;
            font-size: 1.5rem;
        }

        .social-icons a:hover {
            color: #06C167;
        }

        .profile-btn {
            position: fixed;
            top: 25px;
            right: 60px;
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
            z-index: 9999; /* Ensure it is in front of other elements */
        }

        .profile-btn:hover {
            transform: scale(1.05);
        }

        .profile-btn img {
            width: 4.1rem;
            height: 4.1rem;
            border: 4px pink solid;
            border-radius: 50%;
        }

        .lg{
        display: none;
    }

       /* Responsive Styles */
@media (max-width: 1200px) {
    .footer-menu {
        flex-direction: column;
        align-items: center;
        gap: 15px;
    }
}

@media (max-width: 768px) {
    .dashboard-container {
        width: 90%;
    }

    .section-title {
        font-size: 1.8rem;
    }

    .organization-details h3 {
        font-size: 1.4rem;
    }

    .organization-details p {
        font-size: 1rem;
    }

    .actions-section a {
        padding: 10px 20px;
        font-size: 1rem;
        margin: 10px 5px;
    }

    .footer-menu {
        flex-direction: column;
        align-items: center;
    }

    .footer-menu ul {
        margin-bottom: 20px;
    }

    .social-icons {
        margin-top: 30px;
    }

    .profile-btn img {
        width: 3.5rem;
        height: 3.5rem;
    }
}

@media (max-width: 480px) {
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

    .lg {
        display: flex;
        z-index: 1000;
    }

    .lg img {
        width: 60px;
        margin-left: -12.9rem;
        margin-top: 12px;
    }

    .header {
        flex-direction: column;
        text-align: center;
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

    .nav {
        margin-top: 10px;
    }

    .nav a {
        padding: 5px 10px;
        font-size: 0.9rem;
    }

    .dashboard-container {
        padding: 20px;
        background-color: white;
    }

    .section-title {
        margin-top: 5px;
        font-size: 1.7rem;
    }

    .organization-details h3 {
        font-size: 1.3rem;
    }

    .organization-details p {
        font-size: 0.9rem;
    }

    .actions-section a {
        font-size: 0.9rem;
        padding: 8px 15px;
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
            <a href="#dashboard">Dashboard</a>
            <a href="#">Manage Services</a>
            <a href="#">Community</a>
            <a href="#">Contact Admin</a>
        </nav>
    </header>

    <section class="dashboard-container">
        <h2 class="section-title">Hello, <span><?php echo ucfirst($org_name);?>!</span></h2>
        <p>Welcome to your organization dashboard.</p>
        
        <!-- Display Organization Details -->
        <div class="organization-details">
            <h3>Organization Name: <?php echo ucfirst($org_name); ?></h3>
            <p>Organization Type: <?php echo ucfirst($org_type); ?></p>
            <?php if ($org_website): ?>
                <p>Website: <a href="<?php echo $org_website; ?>" target="_blank"><?php echo $org_website; ?></a></p>
            <?php endif; ?>
            <p>Services: <?php echo ucfirst($org_services); ?></p>
        </div>

        <div class="actions-section">
            <h3>What would you like to do?</h3>
            <a href="manage_services.php">Manage Services</a>
            <a href="view_community.php">View Community</a>
            <a href="contact_admin.php">Contact Admin</a>
        </div>

    </section>


<!-- Organization Profile Picture Link -->

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
<div class="profile">
        <a href="orgProfile.php" class="profile-btn">
            <img src="<?php echo $profile_pic; ?>" alt="Organization Profile Picture">
        </a>
    </div>
<!-- Floating Contact Button -->
<div class="floating-contact">
        <a href="organization_dashboard.php" class="contact-btn">
            <img src="SiteIcons/logo.png" alt="logo Us">
        </a>

<div class="icon-lg">
    <a href="organization_dashboard.php" class="lg">
        <img src="SiteIcons/logo.png" alt="logo">
    </a>
</div>


</html>
