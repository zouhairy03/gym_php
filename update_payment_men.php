<?php
// Start session and include necessary files
session_start();
include 'config.php'; // Include database connection

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the payment ID and other payment details from the form
    $payment_id = isset($_POST['payment_id']) ? intval($_POST['payment_id']) : 0;
    $amount_paid = isset($_POST['amount_paid']) ? floatval($_POST['amount_paid']) : 0;
    $pending_amount = isset($_POST['pending_amount']) ? floatval($_POST['pending_amount']) : 0;
    $payment_date = isset($_POST['payment_date']) ? $_POST['payment_date'] : '';

    // Update the payment record in the database
    $sql = "UPDATE payments 
            SET amount_paid = ?, pending_amount = ?, payment_date = ? 
            WHERE payment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ddsi", $amount_paid, $pending_amount, $payment_date, $payment_id);

    if ($stmt->execute()) {
        // Redirect to the payments page with a success message
        $_SESSION['success'] = "Payment updated successfully.";
        header("Location: payments_men.php");
    } else {
        // Display error message if update fails
        echo "<div class='text-center mt-5 text-danger'>Error updating payment: " . $stmt->error . "</div>";
    }
    
    // Close the statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>
