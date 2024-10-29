<?php
// delete_membership.php

include 'config.php';

$membership_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($membership_id) {
    $query = "DELETE FROM memberships WHERE membership_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $membership_id);

    if ($stmt->execute()) {
        // Redirect to notifications_men.php if deletion was successful
        header("Location: notifications_men.php");
        exit();
    } else {
        echo "Error deleting membership: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid ID.";
}
?>
