<?php
// Start session and include necessary files
session_start();
include 'config.php'; // Include database connection

// Check if form data is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $member_id = $_POST['member_id'];
    $amount_paid = $_POST['amount_paid'];
    $pending_amount = $_POST['pending_amount'];

    // Prepare SQL query to insert new payment
    $sql = "INSERT INTO payments (member_id, amount_paid, pending_amount, payment_date) VALUES (?, ?, ?, NOW())";
    
    // Prepare statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters
        $stmt->bind_param("idd", $member_id, $amount_paid, $pending_amount);

        // Execute statement
        if ($stmt->execute()) {
            // Redirect back to payments page with success message
            $_SESSION['success'] = "Payment added successfully.";
            header("Location: payments_women.php");
            exit;
        } else {
            // Handle error
            $_SESSION['error'] = "Error adding payment: " . $stmt->error;
            header("Location: payments_women.php");
            exit;
        }

        // Close statement
        $stmt->close();
    } else {
        $_SESSION['error'] = "Error preparing the statement.";
        header("Location: payments_women.php");
        exit;
    }
}

// Close database connection
$conn->close();
?>
