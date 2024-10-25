<?php
// Start session and include the database configuration
session_start();
require 'config.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $member_id = isset($_POST['member_id']) ? intval($_POST['member_id']) : 0;
    $membership_type = isset($_POST['membership_type']) ? $_POST['membership_type'] : '';
    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
    $expiry_date = isset($_POST['expiry_date']) ? $_POST['expiry_date'] : '';

    // Validate form data
    if ($member_id && $membership_type && $start_date && $expiry_date) {
        // Calculate the remaining days
        $startDate = new DateTime($start_date);
        $expiryDate = new DateTime($expiry_date);
        $remaining_days = $expiryDate->diff($startDate)->days;

        // Prepare SQL query to insert the new membership
        $sql = "INSERT INTO memberships (member_id, membership_type, start_date, expiry_date, remaining_days) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("isssi", $member_id, $membership_type, $start_date, $expiry_date, $remaining_days);
            
            // Execute the query
            if ($stmt->execute()) {
                // Success message
                $_SESSION['message'] = "New membership added successfully!";
            } else {
                // Error message if query execution fails
                $_SESSION['error'] = "Error adding membership. Please try again.";
            }
            
            // Close the statement
            $stmt->close();
        } else {
            // Error preparing the statement
            $_SESSION['error'] = "Failed to prepare SQL statement.";
        }
    } else {
        // Validation error
        $_SESSION['error'] = "Please fill all the required fields.";
    }

    // Redirect back to the memberships page
    header("Location: memberships_men.php");
    exit();
}
?>
