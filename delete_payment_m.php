<?php
// delete_payment.php

include 'config.php';

$payment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($payment_id) {
    $query = "DELETE FROM payments WHERE payment_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $payment_id);

    if ($stmt->execute()) {
        // Redirect to notifications_men.php if deletion was successful
        header("Location: notifications_men.php");
        exit();
    } else {
        echo "Error deleting payment: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid ID.";
}
?>
