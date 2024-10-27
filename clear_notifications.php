<?php
session_start();

if (isset($_POST['gender'])) {
    // Initialize viewed_notifications session array if not already set
    if (!isset($_SESSION['viewed_notifications'])) {
        $_SESSION['viewed_notifications'] = ['women' => 0, 'men' => 0];
    }

    // Update viewed notifications based on gender
    if ($_POST['gender'] === 'women') {
        $_SESSION['viewed_notifications']['women'] = $_SESSION['viewed_notifications']['women'];
    } elseif ($_POST['gender'] === 'men') {
        $_SESSION['viewed_notifications']['men'] = $_SESSION['viewed_notifications']['men'];
    }

    echo json_encode(['status' => 'success', 'message' => 'Notifications cleared']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid gender specified']);
}
?>
