<?php
// Start session and include necessary files
session_start();
include 'config.php'; // Database connection

// Check if form data is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $member_id = isset($_POST['member_id']) ? intval($_POST['member_id']) : 0;
    $insurance_start_date = $_POST['insurance_start_date'] ?? '';
    $insurance_expiry_date = $_POST['insurance_expiry_date'] ?? '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;

    // Validate that all fields are provided
    if ($member_id > 0 && !empty($insurance_start_date) && !empty($insurance_expiry_date) && $price > 0) {
        // Prepare SQL query to insert new insurance record
        $sql = "INSERT INTO insurance (member_id, insurance_start_date, insurance_expiry_date, price) VALUES (?, ?, ?, ?)";
        
        // Prepare statement
        if ($stmt = $conn->prepare($sql)) {
            // Bind parameters
            $stmt->bind_param("issd", $member_id, $insurance_start_date, $insurance_expiry_date, $price);

            // Execute statement
            if ($stmt->execute()) {
                // Redirect back to insurance page with success message
                $_SESSION['success'] = "Insurance record added successfully.";
                header("Location: insurance_women.php");
                exit;
            } else {
                // Handle execution error
                $_SESSION['error'] = "Error adding insurance record: " . $stmt->error;
                header("Location: insurance_women.php");
                exit;
            }

            // Close statement
            $stmt->close();
        } else {
            $_SESSION['error'] = "Error preparing the SQL statement.";
            header("Location: insurance_women.php");
            exit;
        }
    } else {
        // Handle validation error if fields are missing or invalid
        $_SESSION['error'] = "All fields are required and must be valid.";
        header("Location: insurance_women.php");
        exit;
    }
}

// Close database connection
$conn->close();
?>
