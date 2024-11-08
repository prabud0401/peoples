<?php
// Include the database connection
include 'connection.php';

// Check if `org_id` is provided in the URL, otherwise redirect back to manage organizations
if (!isset($_GET['org_id'])) {
    header('Location: manageorganization.php');
    exit();
}

$org_id = intval($_GET['org_id']);

// Fetch the organization details from the database
$query = "SELECT * FROM organization WHERE org_id = ?";
$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, 'i', $org_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Check if the organization exists
if (mysqli_num_rows($result) == 0) {
    echo "<p>Organization not found.</p>";
    exit();
}

$organization = mysqli_fetch_assoc($result);

// Function to display file as a download link or image if it's an image
function displayFile($file_content, $file_type) {
    $base64_data = base64_encode($file_content);
    if (in_array($file_type, ['jpg', 'jpeg', 'png'])) {
        echo '<img src="data:image/' . $file_type . ';base64,' . $base64_data . '" width="300"/>';
    } else {
        echo '<a href="data:application/octet-stream;base64,' . $base64_data . '" download="file.' . $file_type . '">Download File</a>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Organization Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            border-radius: 8px;
        }
        h1 {
            text-align: center;
            color: #4CAF50;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .file-preview {
            margin-top: 15px;
        }
        .file-preview img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Organization Details</h1>

    <table>
        <tr>
            <th>Organization Name</th>
            <td><?php echo htmlspecialchars($organization['org_name']); ?></td>
        </tr>
        <tr>
            <th>Organization Type</th>
            <td><?php echo htmlspecialchars($organization['org_type']); ?></td>
        </tr>
        <tr>
            <th>Registration Number</th>
            <td><?php echo htmlspecialchars($organization['org_registration']); ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?php echo htmlspecialchars($organization['email']); ?></td>
        </tr>
        <tr>
            <th>Phone</th>
            <td><?php echo htmlspecialchars($organization['phone']); ?></td>
        </tr>
        <tr>
            <th>Website</th>
            <td><?php echo htmlspecialchars($organization['website'] ?: 'N/A'); ?></td>
        </tr>
        <tr>
            <th>Address</th>
            <td><?php echo htmlspecialchars($organization['address']); ?></td>
        </tr>
        <tr>
            <th>Services</th>
            <td><?php echo htmlspecialchars($organization['services']); ?></td>
        </tr>
        <tr>
            <th>Organization Status</th>
            <td><?php echo htmlspecialchars($organization['status']); ?></td>
        </tr>
    </table>

    <!-- Display the uploaded files -->
    <h3>Proof of Registration</h3>
    <div class="file-preview">
        <?php if ($organization['proof_registration']): ?>
            <?php displayFile($organization['proof_registration'], 'pdf'); ?>
        <?php else: ?>
            <p>No proof of registration uploaded.</p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>

<?php
// Close the statement and connection
mysqli_stmt_close($stmt);
mysqli_close($connection);
?>
