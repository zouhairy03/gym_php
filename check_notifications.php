<?php
session_start();
include 'config.php';

// Initialize notification counts
$expired_women_count = $pending_women_count = $expired_men_count = $pending_men_count = 0;

// Query for expired women's memberships
$expired_women_query = "
    SELECT COUNT(*) AS expired_count 
    FROM memberships 
    JOIN members ON memberships.member_id = members.member_id 
    WHERE members.gender = 'female' AND memberships.expiry_date < CURDATE();
";
if ($result = $conn->query($expired_women_query)) {
    $expired_women_count = $result->fetch_assoc()['expired_count'];
}

// Query for pending payments for women
$pending_women_query = "
    SELECT COUNT(*) AS pending_count 
    FROM payments 
    JOIN members ON payments.member_id = members.member_id 
    WHERE members.gender = 'female' AND payments.pending_amount > 0;
";
if ($result = $conn->query($pending_women_query)) {
    $pending_women_count = $result->fetch_assoc()['pending_count'];
}

// Query for expired men's memberships
$expired_men_query = "
    SELECT COUNT(*) AS expired_count 
    FROM memberships 
    JOIN members ON memberships.member_id = members.member_id 
    WHERE members.gender = 'male' AND memberships.expiry_date < CURDATE();
";
if ($result = $conn->query($expired_men_query)) {
    $expired_men_count = $result->fetch_assoc()['expired_count'];
}

// Query for pending payments for men
$pending_men_query = "
    SELECT COUNT(*) AS pending_count 
    FROM payments 
    JOIN members ON payments.member_id = members.member_id 
    WHERE members.gender = 'male' AND payments.pending_amount > 0;
";
if ($result = $conn->query($pending_men_query)) {
    $pending_men_count = $result->fetch_assoc()['pending_count'];
}

// Calculate total notifications for each gender
$total_notifications_women = $expired_women_count + $pending_women_count;
$total_notifications_men = $expired_men_count + $pending_men_count;

// Initialize session variables if they don't exist
if (!isset($_SESSION['viewed_notifications'])) {
    $_SESSION['viewed_notifications'] = ['women' => 0, 'men' => 0];
}

// Calculate new notifications by comparing totals with the last viewed count
$new_women_notifications = max(0, $total_notifications_women - $_SESSION['viewed_notifications']['women']);
$new_men_notifications = max(0, $total_notifications_men - $_SESSION['viewed_notifications']['men']);

// Indicate if there are any new notifications
$has_new_notifications = $new_women_notifications > 0 || $new_men_notifications > 0;

// Return notification data as JSON
echo json_encode([
    'women_count' => $total_notifications_women,
    'men_count' => $total_notifications_men,
    'new_women_notifications' => $new_women_notifications,
    'new_men_notifications' => $new_men_notifications,
    'has_new_notifications' => $has_new_notifications
]);
?>
