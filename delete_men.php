<?php
// delete_men.php
session_start();
require 'config.php'; // Include the database configuration

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Get the member ID from the URL
$member_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($member_id > 0) {
    // Fetch men member details to ensure it's a male member
    $stmt = $conn->prepare("SELECT * FROM members WHERE member_id = ? AND gender = 'male'");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $member = $result->fetch_assoc();

    if ($member) {
        // Check if deletion is confirmed
        if (isset($_POST['confirm_delete'])) {
            // Delete the member from the database
            $deleteStmt = $conn->prepare("DELETE FROM members WHERE member_id = ?");
            $deleteStmt->bind_param("i", $member_id);
            if ($deleteStmt->execute()) {
                // Redirect to the members list after successful deletion
                header("Location: members_men.php?message=MemberDeleted");
                exit();
            } else {
                $error_message = "Failed to delete the member.";
            }
        }
    } else {
        $error_message = "No men member found with this ID.";
    }
} else {
    $error_message = "Invalid member ID.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete men Member</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h2>Delete Member</h2>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php elseif (isset($member)): ?>
        <div class="alert alert-warning">
            Are you sure you want to delete <strong><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></strong>?
        </div>
        
        <form action="delete_men.php?id=<?php echo $member_id; ?>" method="POST">
            <input type="hidden" name="confirm_delete" value="1">
            <button type="submit" class="btn btn-danger">Yes, Delete</button>
            <a href="members_men.php" class="btn btn-secondary">Cancel</a>
        </form>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
