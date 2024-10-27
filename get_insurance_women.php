<?php
// get_insurance_women.php
session_start();
require 'config.php'; // Include the database configuration

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Check if member ID is provided
if (!isset($_GET['member_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Member ID is required']);
    exit();
}

// Get the member ID
$member_id = (int)$_GET['member_id'];

// Prepare the SQL query to fetch insurance details for the specific member
$query = "SELECT i.insurance_id, i.insurance_start_date, i.insurance_expiry_date, i.price, 
                 m.first_name, m.last_name, m.activity_status, m.picture
          FROM insurance i
          INNER JOIN members m ON i.member_id = m.member_id
          WHERE m.member_id = ? AND m.gender = 'female'";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the member has insurance records
if ($result->num_rows > 0) {
    $insuranceData = $result->fetch_assoc();

    // Prepare image path
    $imagePath = "uploads/members/women/" . $insuranceData['member_id'] . ".jpg";
    $insuranceData['picture'] = file_exists($imagePath) ? $imagePath : "uploads/members/women/default.jpg";

    // Send response as JSON
    echo json_encode($insuranceData);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'No insurance records found for this member']);
}

$stmt->close();
$conn->close();
?>
