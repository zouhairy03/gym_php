<?php
// add_member_women.php
session_start();
require 'config.php'; // Include the database configuration

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone_number = $_POST['phone_number'] ?? null; // Optional
    $CNE = $_POST['CNE'] ?? null; // Optional
    $activity_status = $_POST['activity_status'];
    $membership_status = $_POST['membership_status'];
    $insurance_status = $_POST['insurance_status'];
    $created_at = $_POST['created_at'];
    $gender = "female"; // Gender is always female
    $picture = null; // Initialize picture variable

    // Check if an image was uploaded
    if (!empty($_FILES['picture']['name'])) {
        // Define the directory for women members' pictures
        $image_dir = 'uploads/members/women/';
        
        // Ensure the directory exists, create it if not
        if (!is_dir($image_dir)) {
            mkdir($image_dir, 0777, true);
        }

        // Save the uploaded file
        $image_name = $image_dir . basename($_FILES['picture']['name']);
        $imageFileType = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

        // Allow certain file formats
        if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            move_uploaded_file($_FILES['picture']['tmp_name'], $image_name);
            $picture = $image_name; // Update picture path
        } else {
            $error_message = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        }
    }

    // Insert the member into the database
    if (!isset($error_message)) {
        $stmt = $conn->prepare("INSERT INTO members (first_name, last_name, phone_number, CNE, activity_status, membership_status, insurance_status, created_at, gender, picture) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssss", $first_name, $last_name, $phone_number, $CNE, $activity_status, $membership_status, $insurance_status, $created_at, $gender, $picture);

        if ($stmt->execute()) {
            header("Location: members_women.php?message=MemberAdded");
            exit();
        } else {
            $error_message = "Failed to add the member.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Woman Member</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h2>Add New Woman Member</h2>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form action="add_member_women.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="first_name">First Name</label>
            <input type="text" class="form-control" name="first_name" required>
        </div>
        <div class="form-group">
            <label for="last_name">Last Name</label>
            <input type="text" class="form-control" name="last_name" required>
        </div>
        <div class="form-group">
            <label for="phone_number">Phone Number (Optional)</label>
            <input type="text" class="form-control" name="phone_number">
        </div>
        <div class="form-group">
            <label for="CNE">CNE (Optional)</label>
            <input type="text" class="form-control" name="CNE">
        </div>
        <div class="form-group">
            <label for="picture">Picture</label>
            <input type="file" class="form-control" name="picture" accept="image/*">
        </div>
        <div class="form-group">
            <label for="activity_status">Activity Status</label>
            <select class="form-control" name="activity_status" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="form-group">
            <label for="membership_status">Membership Status</label>
            <select class="form-control" name="membership_status" required>
                <option value="valid">Valid</option>
                <option value="expired">Expired</option>
            </select>
        </div>
        <div class="form-group">
            <label for="insurance_status">Insurance Status</label>
            <select class="form-control" name="insurance_status" required>
                <option value="valid">Valid</option>
                <option value="expired">Expired</option>
            </select>
        </div>
        <div class="form-group">
            <label for="created_at">Creation Date</label>
            <input type="datetime-local" class="form-control" name="created_at" required>
        </div>
        <!-- Hidden field for gender, automatically set to female -->
        <input type="hidden" name="gender" value="female">
        <button type="submit" class="btn btn-primary">Add Member</button>
        <a href="members_women.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
