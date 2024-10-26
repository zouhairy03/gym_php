<?php
// Start session and include necessary files
session_start();
include 'config.php'; // Include database connection

// Get the membership ID from the URL
$membership_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if the membership ID is valid
if ($membership_id > 0) {
    // Fetch membership details to confirm existence and get member information
    $sql = "
        SELECT memberships.membership_id, members.first_name, members.last_name 
        FROM memberships 
        JOIN members ON memberships.member_id = members.member_id 
        WHERE memberships.membership_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $membership_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the membership exists
    if ($result->num_rows == 0) {
        echo "<div class='text-center mt-5 text-danger'>Membership not found.</div>";
        exit;
    }

    $membership = $result->fetch_assoc();

    // Delete membership record
    $delete_sql = "DELETE FROM memberships WHERE membership_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $membership_id);

    if ($delete_stmt->execute()) {
        // Set success message and redirect
        $_SESSION['success'] = "Membership for " . htmlspecialchars($membership['first_name'] . " " . $membership['last_name']) . " deleted successfully.";
        header("Location: notifications_women.php");
    } else {
        // Display error message if deletion fails
        echo "<div class='text-center mt-5 text-danger'>Error deleting membership: " . $delete_stmt->error . "</div>";
    }

    // Close the statements
    $delete_stmt->close();
    $stmt->close();
} else {
    echo "<div class='text-center mt-5 text-danger'>Invalid membership ID.</div>";
}

// Close the database connection
$conn->close();
?>
