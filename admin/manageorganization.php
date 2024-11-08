<?php
// Include database connection
include 'connection.php';

$search_term = '';

// Handle search input from the form
if (isset($_POST['search'])) {
    $search_term = mysqli_real_escape_string($connection, $_POST['search_term']);
}

// Build the search query based on the search term
$search_query = "";
if ($search_term) {
    $search_query = "AND (org_name LIKE '%$search_term%' OR org_registration LIKE '%$search_term%')";
}

// Handle admin actions (allow/block) if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $org_id = intval($_POST['org_id']);
    $action = $_POST['action'];

    // Determine the new status based on the admin's action
    if ($action == 'allow') {
        $status = 'allowed';
    } elseif ($action == 'block') {
        $status = 'blocked';
    } else {
        $status = 'pending';
    }

    // Update the organization's status
    $query = "UPDATE organization SET status = ? WHERE org_id = ?";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, 'si', $status, $org_id);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Organization status updated successfully!');</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($connection) . "');</script>";
    }

    // Close the prepared statement
    mysqli_stmt_close($stmt);
}

// Fetch all organizations categorized by their status and filtered by search term
$query_pending = "SELECT * FROM organization WHERE status = 'pending' $search_query";
$query_allowed = "SELECT * FROM organization WHERE status = 'allowed' $search_query";
$query_blocked = "SELECT * FROM organization WHERE status = 'blocked' $search_query";

$result_pending = mysqli_query($connection, $query_pending);
$result_allowed = mysqli_query($connection, $query_allowed);
$result_blocked = mysqli_query($connection, $query_blocked);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Organizations</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        button {
            padding: 5px 10px;
            margin: 0 5px;
            cursor: pointer;
        }
        .allow {
            background-color: #4CAF50;
            color: white;
        }
        .block {
            background-color: #f44336;
            color: white;
        }
        .header {
            text-align: center;
            padding: 20px;
        }
        .search-bar {
            margin-bottom: 20px;
            text-align: center;
        }
        .search-bar input[type="text"] {
            padding: 10px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .search-bar button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .view-details {
            text-decoration: none;
            padding: 5px 10px;
            background-color: #2196F3;
            color: white;
            border-radius: 5px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Manage Organizations</h1>
        <p>Review pending organizations and manage their status</p>
    </div>

    <!-- Search Bar -->
    <div class="search-bar">
        <form action="" method="POST">
            <input type="text" name="search_term" placeholder="Search by Name or Registration Number" value="<?php echo htmlspecialchars($search_term); ?>">
            <button type="submit" name="search">Search</button>
        </form>
    </div>

    <!-- Pending Organizations -->
    <h2>Pending Organizations</h2>
    <?php if (mysqli_num_rows($result_pending) > 0): ?>
        <table>
            <tr>
                <th>Organization Name</th>
                <th>Email</th>
                <th>Registration Number</th>
                <th>Action</th>
                <th>Details</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($result_pending)): ?>
                <tr>
                    <td><?php echo $row['org_name']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['org_registration']; ?></td>
                    <td>
                        <form action="" method="post">
                            <input type="hidden" name="org_id" value="<?php echo $row['org_id']; ?>">
                            <button type="submit" name="action" value="allow" class="allow">Allow</button>
                            <button type="submit" name="action" value="block" class="block">Block</button>
                        </form>
                    </td>
                    <td><a href="view_organization_details.php?org_id=<?php echo $row['org_id']; ?>" class="view-details">View Details</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No pending organizations found.</p>
    <?php endif; ?>

    <!-- Allowed Organizations -->
    <h2>Allowed Organizations</h2>
    <?php if (mysqli_num_rows($result_allowed) > 0): ?>
        <table>
            <tr>
                <th>Organization Name</th>
                <th>Email</th>
                <th>Registration Number</th>
                <th>Action</th>
                <th>Details</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($result_allowed)): ?>
                <tr>
                    <td><?php echo $row['org_name']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['org_registration']; ?></td>
                    <td>
                        <form action="" method="post">
                            <input type="hidden" name="org_id" value="<?php echo $row['org_id']; ?>">
                            <button type="submit" name="action" value="block" class="block">Block</button>
                        </form>
                    </td>
                    <td><a href="view_organization_details.php?org_id=<?php echo $row['org_id']; ?>" class="view-details">View Details</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No allowed organizations found.</p>
    <?php endif; ?>

    <!-- Blocked Organizations -->
    <h2>Blocked Organizations</h2>
    <?php if (mysqli_num_rows($result_blocked) > 0): ?>
        <table>
            <tr>
                <th>Organization Name</th>
                <th>Email</th>
                <th>Registration Number</th>
                <th>Action</th>
                <th>Details</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($result_blocked)): ?>
                <tr>
                    <td><?php echo $row['org_name']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['org_registration']; ?></td>
                    <td>
                        <form action="" method="post">
                            <input type="hidden" name="org_id" value="<?php echo $row['org_id']; ?>">
                            <button type="submit" name="action" value="allow" class="allow">Allow</button>
                        </form>
                    </td>
                    <td><a href="view_organization_details.php?org_id=<?php echo $row['org_id']; ?>" class="view-details">View Details</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No blocked organizations found.</p>
    <?php endif; ?>

</body>
</html>

<?php
// Close connection
mysqli_close($connection);
?>
