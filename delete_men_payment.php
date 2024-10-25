<?php
// Start session and include necessary files
session_start();
include 'config.php'; // Include database connection

// Get the payment ID from the URL
$payment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$confirm = isset($_GET['confirm']) ? $_GET['confirm'] : false; // Check for confirmation

// Check if the payment ID is valid
if ($payment_id > 0) {
    // Fetch payment details to confirm existence and get member info
    $sql = "SELECT payments.*, members.first_name, members.last_name 
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

    // Fetch payment details
    $payment = $result->fetch_assoc();

    // If confirmation is received, delete the payment record
    if ($confirm === 'true') {
        $delete_sql = "DELETE FROM payments WHERE payment_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $payment_id);

        if ($delete_stmt->execute()) {
            // Set success message and redirect
            $_SESSION['success'] = "Payment deleted successfully.";
            header("Location: payments_men.php");
            exit;
        } else {
            // Display error message if deletion fails
            echo "<div class='text-center mt-5 text-danger'>Error deleting payment: " . $delete_stmt->error . "</div>";
            exit;
        }

        // Close the statements
        $delete_stmt->close();
        $stmt->close();
    } else {
        // If confirmation is not received, show a confirmation message with Bootstrap buttons
        echo "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Confirm Deletion</title>
            <link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>
        </head>
        <body>
            <div class='container mt-5 text-center'>
                <h5>Are you sure you want to delete the payment  for <strong>" . $payment['first_name'] . " " . $payment['last_name'] . "</strong>?</h5>
                <div class='mt-3'>
                    <a href='delete_men_payment.php?id=$payment_id&confirm=true' class='btn btn-danger'>
                        <i class='fas fa-trash-alt'></i> Yes, Delete
                    </a>
                    <a href='payments_men.php' class='btn btn-secondary'>
                        <i class='fas fa-arrow-left'></i> Cancel
                    </a>
                </div>
            </div>
            <script src='https://code.jquery.com/jquery-3.5.1.min.js'></script>
            <script src='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js'></script>
        </body>
        </html>";
        exit;
    }
} else {
    echo "<div class='text-center mt-5 text-danger'>Invalid payment ID.</div>";
}

// Close the database connection
$conn->close();
?>
