<?php
// Start session and include necessary files
session_start();
include 'config.php'; // Include database connection

// Check if membership ID is provided in URL
$membership_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch membership details to display in confirmation message
$sql = "SELECT memberships.membership_type, members.first_name, members.last_name 
        FROM memberships 
        JOIN members ON memberships.member_id = members.member_id 
        WHERE memberships.membership_id = $membership_id";
$result = $conn->query($sql);

// Check if membership exists
if ($result->num_rows == 0) {
    echo "<div class='text-center mt-5 text-danger'>Membership not found.</div>";
    exit;
}

$membership = $result->fetch_assoc();

// Delete membership upon confirmation
if (isset($_POST['confirm_delete'])) {
    $delete_sql = "DELETE FROM memberships WHERE membership_id = $membership_id";
    if ($conn->query($delete_sql) === TRUE) {
        echo "<script>
                alert('Membership deleted successfully.');
                window.location.href = 'memberships_men.php';
              </script>";
    } else {
        echo "<div class='text-center mt-5 text-danger'>Error deleting membership: " . $conn->error . "</div>";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Membership</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
</head>
<body>

<div class="container text-center mt-5">
    <h2 class="text-danger"><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h2>
    <p class="mt-4">
        Are you sure you want to delete the membership for 
        <strong><?php echo $membership['first_name'] . " " . $membership['last_name']; ?></strong>
        (<?php echo ucfirst($membership['membership_type']); ?> Membership)?
    </p>
    <form method="POST" class="mt-4">
        <button type="submit" name="confirm_delete" class="btn btn-danger">
            <i class="fas fa-trash-alt"></i> Confirm Delete
        </button>
        <a href="memberships_men.php" class="btn btn-secondary ml-2">
            <i class="fas fa-arrow-left"></i> Cancel
        </a>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
