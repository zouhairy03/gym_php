<?php
include 'config.php';

// Query counts for expired memberships and pending payments
$expired_women_count = 3; // Replace with database query
$pending_women_count = 2; // Replace with database query
$expired_men_count = 1;   // Replace with database query
$pending_men_count = 1;   // Replace with database query

$total_notifications_women = $expired_women_count + $pending_women_count;
$total_notifications_men = $expired_men_count + $pending_men_count;

$has_new_notifications = (!$$_SESSION['viewed_notifications']['women'] && $total_notifications_women > 0) || 
                         (!$$_SESSION['viewed_notifications']['men'] && $total_notifications_men > 0);

echo json_encode([
    'women_count' => $total_notifications_women,
    'men_count' => $total_notifications_men,
    'has_new_notifications' => $has_new_notifications
]);
?>
