<?php
// Start session and include necessary files
session_start();
include 'config.php'; // Include database connection

// Get the payment ID from the URL
$payment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if the payment ID is valid
if ($payment_id > 0) {
    // Fetch payment details to confirm existence and get member information
    $sql = "
        SELECT payments.payment_id, payments.amount_paid, payments.pending_amount, 
               members.first_name, members.last_name 
        FROM payments 
        JOIN members ON payments.member_id = members.member_id 
        WHERE payments.payment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the payment exists
    if ($result->num_rows == 0) {
        echo "<div class='text-center mt-5 text-danger'>Payment not found.</div>";
        exit;
    }

    $payment = $result->fetch_assoc();

    // Delete payment record
    $delete_sql = "DELETE FROM payments WHERE payment_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $payment_id);

    if ($delete_stmt->execute()) {
        // Set success message with payment details and redirect
        $_SESSION['success'] = "Payment of " . htmlspecialchars($payment['amount_paid']) . " MAD for " . 
                               htmlspecialchars($payment['first_name'] . " " . $payment['last_name']) . 
                               " deleted successfully.";
        header("Location: notifications_women.php");
    } else {
        // Display error message if deletion fails
        echo "<div class='text-center mt-5 text-danger'>Error deleting payment: " . $delete_stmt->error . "</div>";
    }

    // Close the statements
    $delete_stmt->close();
    $stmt->close();
} else {
    echo "<div class='text-center mt-5 text-danger'>Invalid payment ID.</div>";
}

// Close the database connection
$conn->close();
?>
