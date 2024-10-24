<?php
// view_men.php
session_start();
require 'config.php'; // Include the database configuration

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Get the member ID from the URL
$member_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch men member details
$stmt = $conn->prepare("SELECT * FROM members WHERE member_id = ? AND gender = 'male'");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();

// Check if member was found
if (!$member) {
    echo "No member found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View men - <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            margin: 0;
            display: flex;
            background-color: #f8f9fa;
        }

        .wrapper {
            display: flex;
            width: 100%;
        }

        .sidebar {
            width: 250px;
            background-color: #343a40;
            padding: 20px;
            position: fixed;
            height: 100vh;
            transition: all 0.3s ease;
            left: 0;
        }

        .sidebar a {
            color: white;
            padding: 10px;
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
        }

        .sidebar a:hover {
            background-color: #495057;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
            width: 100%;
            transition: all 0.3s;
        }

        .toggled .sidebar {
            left: -250px;
        }

        .toggled .content {
            margin-left: 0;
        }

        .profile-container {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .profile-container h2 {
            margin-bottom: 20px;
        }

        .profile-container img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
        }

        .profile-details {
            margin-top: 20px;
        }

        .profile-details p {
            font-size: 16px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Content -->
    <div class="content">
    <button class="btn btn-secondary mb-3" id="toggleSidebar">Toggle Sidebar</button>
    <div class="profile-container" style="text-align: center;">
        <h2>View Member - <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></h2>

        <!-- Profile Picture -->
        <?php if (!empty($member['picture'])): ?>
            <img src="<?php echo $member['picture']; ?>" alt="Member Picture" style="display: block; margin: 0 auto;">
        <?php else: ?>
            <p>No profile picture available.</p>
        <?php endif; ?>

        <!-- Profile Details -->
        <div class="profile-details">
            <p><strong>First Name:</strong> <?php echo htmlspecialchars($member['first_name']); ?></p>
            <p><strong>Last Name:</strong> <?php echo htmlspecialchars($member['last_name']); ?></p>
            <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($member['phone_number']); ?></p>
            <p><strong>CNE:</strong> <?php echo htmlspecialchars($member['CNE']); ?></p>
            <p><strong>Activity Status:</strong> <?php echo htmlspecialchars($member['activity_status']); ?></p>
            <p><strong>Insurance Status:</strong> <?php echo htmlspecialchars($member['insurance_status']); ?></p>
            <p><strong>Membership Status:</strong> <?php echo htmlspecialchars($member['membership_status']); ?></p>
            <p><strong>Created At:</strong> <?php echo htmlspecialchars($member['created_at']); ?></p>
        </div>

        <!-- Back Button -->
        <a href="members_men.php" class="btn btn-secondary mt-3">Back to Members</a>
    </div>


</div>

<script>
    // Toggle sidebar functionality
    document.getElementById("toggleSidebar").addEventListener("click", function () {
        document.querySelector(".wrapper").classList.toggle("toggled");
    });
</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
