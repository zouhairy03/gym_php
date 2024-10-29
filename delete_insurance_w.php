<?php
// delete_insurance.php

include 'config.php';

$insurance_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($insurance_id) {
    $query = "DELETE FROM insurance WHERE insurance_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $insurance_id);

    if ($stmt->execute()) {
        // Redirect to notifications_men.php if deletion was successful
        header("Location: notifications_women.php");
        exit();
    } else {
        echo "Error deleting insurance: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid ID.";
}
?>
