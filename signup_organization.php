<?php
// Include database connection
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and retrieve form inputs
    $org_name = mysqli_real_escape_string($connection, $_POST['org_name']);
    $org_type = mysqli_real_escape_string($connection, $_POST['org_type']);
    $org_registration = mysqli_real_escape_string($connection, $_POST['org_registration']);
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    $phone = mysqli_real_escape_string($connection, $_POST['phone']);
    $website = mysqli_real_escape_string($connection, $_POST['website']);
    $address = mysqli_real_escape_string($connection, $_POST['address']);
    $services = mysqli_real_escape_string($connection, $_POST['services']);
    $password = $_POST['org_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate that passwords match
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match.');</script>";
        exit();
    }

    // Ensure the password has a minimum length (e.g., 8 characters)
    if (strlen($password) < 8) {
        echo "<script>alert('Password must be at least 8 characters long.');</script>";
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Handle file upload for proof_registration
    $proof_registration = null;
    if (isset($_FILES['proof_registration']['tmp_name']) && $_FILES['proof_registration']['tmp_name']) {
        $proof_registration = file_get_contents($_FILES['proof_registration']['tmp_name']);
    }

    // Handle profile picture: Check if an image was uploaded
    $profile_pic = null;
    if (isset($_FILES['profile_pic']['tmp_name']) && $_FILES['profile_pic']['tmp_name']) {
        // User uploaded a profile picture
        $profile_pic = file_get_contents($_FILES['profile_pic']['tmp_name']);
    } else {
        // No image was uploaded, use the default profile picture
        $default_image_path = 'SiteIcons/default_org.png'; // Ensure this file exists
        $profile_pic = file_get_contents($default_image_path); // Read the default image content
    }

    // Check if the organization already exists (by email or registration number)
    $check_query = "SELECT * FROM organization WHERE email = ? OR org_registration = ?";
    $check_stmt = mysqli_prepare($connection, $check_query);
    mysqli_stmt_bind_param($check_stmt, 'ss', $email, $org_registration);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        echo "<script>alert('An organization with this email or registration number already exists.');</script>";
        exit();
    }
    mysqli_stmt_close($check_stmt);

    // Insert organization data with hashed password and profile picture
    $query = "INSERT INTO organization 
    (org_name, org_type, org_registration, email, phone, website, address, 
    proof_registration, services, status, org_password, profile_pic) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)";

    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, 'sssssssssss', 
        $org_name, $org_type, $org_registration, $email, $phone, $website, $address, 
        $proof_registration, $services, $hashed_password, $profile_pic
    );

    // Execute the query
    if (mysqli_stmt_execute($stmt)) {
        // Redirect to index page with a success message after successful registration
        echo "<script>
            alert('Organization registered successfully!');
            window.location.href = 'index.php?success=1';
        </script>";
        exit();
    } else {
        echo "<script>alert('Error: " . mysqli_error($connection) . "');</script>";
    }

    // Close the statement and connection
    mysqli_stmt_close($stmt);
    mysqli_close($connection);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Signup</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            padding: 20px;
        }
        .container {
            background-color: white;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #4f358e;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }
        input[type="text"], input[type="email"], input[type="tel"], input[type="url"], input[type="file"], input[type="password"], textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .file-upload label {
            margin-right: 10px;
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
    </style>
</head>
<body>

<div class="container">
    <h2>Organization Signup Form</h2>
    <form action="signup_organization.php" method="post" enctype="multipart/form-data">
        
        <!-- Organization Information -->
        <div class="form-group">
            <label for="org-name">Organization Name</label>
            <input type="text" id="org-name" name="org_name" required>
        </div>

        <div class="form-group">
            <label for="org-type">Organization Type</label>
            <input type="text" id="org-type" name="org_type" placeholder="Non-profit, Charity, Business, etc." required>
        </div>

        <div class="form-group">
            <label for="org-registration">Registration Number</label>
            <input type="text" id="org-registration" name="org_registration" required>
        </div>

        <!-- Contact Information -->
        <div class="form-group">
            <label for="email">Official Email Address</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" required>
        </div>

        <div class="form-group">
            <label for="website">Website URL</label>
            <input type="url" id="website" name="website" placeholder="https://example.com">
        </div>

        <div class="form-group">
            <label for="address">Physical Address</label>
            <textarea id="address" name="address" rows="3" required></textarea>
        </div>

        <!-- Password Fields -->
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="org_password" required>
        </div>

        <div class="form-group">
            <label for="confirm-password">Confirm Password</label>
            <input type="password" id="confirm-password" name="confirm_password" required>
        </div>

        <!-- Legal and Verification Documents -->
        <div class="form-group file-upload">
            <label for="proof-registration">Proof of Registration</label>
            <input type="file" id="proof-registration" name="proof_registration" accept=".pdf,.jpg,.jpeg,.png" required>
        </div>

        <!-- Operational Details -->
        <div class="form-group">
            <label for="services">Services Provided</label>
            <textarea id="services" name="services" rows="3" placeholder="Describe your organization's services" required></textarea>
        </div>

        <!-- Agreement -->
        <div class="form-group">
            <input type="checkbox" id="terms" name="terms" required>
            <label for="terms">I agree to the <a href="#">Terms and Conditions</a> and <a href="#">Privacy Policy</a>.</label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn">Submit</button>
    </form>
</div>

</body>
</html>
