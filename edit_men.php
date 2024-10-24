<?php
// edit_men.php
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

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the updated form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone_number = $_POST['phone_number'];
    $CNE = $_POST['CNE'];
    $activity_status = $_POST['activity_status'];
    $insurance_status = $_POST['insurance_status'];
    $membership_status = $_POST['membership_status'];
    $created_at = $_POST['created_at'];
    $picture = $member['picture']; // Keep the current picture if no new one is uploaded

    // Check if a new image was uploaded
    if (!empty($_FILES['picture']['name'])) {
        // Define the directory for men members' pictures
        $image_dir = 'uploads/members/men/';
        
        // Ensure the directory exists, create it if not
        if (!is_dir($image_dir)) {
            mkdir($image_dir, 0777, true);
        }

        $image_name = $image_dir . basename($_FILES['picture']['name']);
        $imageFileType = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

        // Allow certain file formats
        if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            move_uploaded_file($_FILES['picture']['tmp_name'], $image_name);
            $picture = $image_name; // Update the picture path
        } else {
            $error_message = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        }
    }

    // Update the member in the database
    $updateStmt = $conn->prepare("UPDATE members SET first_name = ?, last_name = ?, phone_number = ?, CNE = ?, activity_status = ?, insurance_status = ?, membership_status = ?, picture = ?, created_at = ? WHERE member_id = ?");
    $updateStmt->bind_param("sssssssssi", $first_name, $last_name, $phone_number, $CNE, $activity_status, $insurance_status, $membership_status, $picture, $created_at, $member_id);
    
    if ($updateStmt->execute()) {
        header("Location: members_men.php");
        exit();
    } else {
        $error_message = "Failed to update the member.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit men Member - <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></title>
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

        .sidebar .sidebar-header {
            font-size: 20px;
            color: white;
            margin-bottom: 20px;
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

        .form-container {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .form-container h2 {
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            font-weight: bold;
        }

        .form-group input[type="submit"] {
            width: auto;
        }

        .form-group img {
            display: block;
            margin-bottom: 10px;
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
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
        <div class="form-container">
        <h2>Edit men Member - <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></h2>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form action="edit_men.php?id=<?php echo $member_id; ?>" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($member['first_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($member['last_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="text" class="form-control" name="phone_number" value="<?php echo htmlspecialchars($member['phone_number']); ?>" >
                </div>
                <div class="form-group">
                    <label for="CNE">CNE</label>
                    <input type="text" class="form-control" name="CNE" value="<?php echo htmlspecialchars($member['CNE']); ?>" >
                </div>
                <div class="form-group">
                    <label for="activity_status">Activity Status</label>
                    <select class="form-control" name="activity_status" required>
                        <option value="active" <?php if ($member['activity_status'] === 'active') echo 'selected'; ?>>Active</option>
                        <option value="inactive" <?php if ($member['activity_status'] === 'inactive') echo 'selected'; ?>>Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="insurance_status">Insurance Status</label>
                    <select class="form-control" name="insurance_status" required>
                        <option value="valid" <?php if ($member['insurance_status'] === 'valid') echo 'selected'; ?>>Valid</option>
                        <option value="expired" <?php if ($member['insurance_status'] === 'expired') echo 'selected'; ?>>Expired</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="membership_status">Membership Status</label>
                    <select class="form-control" name="membership_status" required>
                        <option value="valid" <?php if ($member['membership_status'] === 'valid') echo 'selected'; ?>>Valid</option>
                        <option value="expired" <?php if ($member['membership_status'] === 'expired') echo 'selected'; ?>>Expired</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="picture">Picture</label>
                    <?php if (!empty($member['picture'])): ?>
                        <img src="<?php echo $member['picture']; ?>" alt="Member Picture">
                    <?php endif; ?>
                    <input type="file" class="form-control" name="picture" accept="image/*">
                </div>
                <div class="form-group">
                    <label for="created_at">Creation Date</label>
                    <input type="datetime-local" class="form-control" name="created_at" value="<?php echo date('Y-m-d\TH:i', strtotime($member['created_at'])); ?>" required>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Update Member">
                    <a href="members_men.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
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
