<?php
include 'config.php'; // Include your database configuration

if (isset($_POST['member_id'])) {
    $member_id = intval($_POST['member_id']); // Sanitize input
    $sql = "SELECT first_name, last_name, phone_number, picture FROM members WHERE member_id = ? AND gender = 'female' AND activity_status = 'active'";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        echo json_encode(['error' => 'Failed to prepare the SQL statement']);
        exit();
    }

    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $member = $result->fetch_assoc();

        // Check if picture path is valid
        if (!file_exists($member['picture']) || empty($member['picture'])) {
            $member['picture'] = 'uploads/default.png'; // Fallback to default image if not found
        }

        echo json_encode($member); // Return the member data in JSON format
    } else {
        echo json_encode(['error' => 'No active female member found']);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'No member ID provided']);
}

$conn->close(); // Close the database connection
?>
