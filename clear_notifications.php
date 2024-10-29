<?php
include 'config.php'; // Database connection

// Get the gender parameter from the POST request
$gender = $_POST['gender'] ?? '';

if ($gender === 'women') {
    // Mark expired memberships as viewed for women
    $conn->query("
        UPDATE memberships 
        JOIN members ON memberships.member_id = members.member_id 
        SET memberships.shown = 1 
        WHERE members.gender = 'female' AND memberships.expiry_date < CURDATE() AND memberships.shown = 0
    ");
    
    // Mark pending payments as viewed for women
    $conn->query("
        UPDATE payments 
        JOIN members ON payments.member_id = members.member_id 
        SET payments.shown = 1 
        WHERE members.gender = 'female' AND payments.pending_amount > 0 AND payments.shown = 0
    ");
    
    // Mark expired insurance as viewed for women
    $conn->query("
        UPDATE insurance 
        JOIN members ON insurance.member_id = members.member_id 
        SET insurance.shown = 1 
        WHERE members.gender = 'female' AND insurance.insurance_expiry_date < CURDATE() AND insurance.shown = 0
    ");
    
} elseif ($gender === 'men') {
    // Mark expired memberships as viewed for men
    $conn->query("
        UPDATE memberships 
        JOIN members ON memberships.member_id = members.member_id 
        SET memberships.shown = 1 
        WHERE members.gender = 'male' AND memberships.expiry_date < CURDATE() AND memberships.shown = 0
    ");
    
    // Mark pending payments as viewed for men
    $conn->query("
        UPDATE payments 
        JOIN members ON payments.member_id = members.member_id 
        SET payments.shown = 1 
        WHERE members.gender = 'male' AND payments.pending_amount > 0 AND payments.shown = 0
    ");
    
    // Mark expired insurance as viewed for men
    $conn->query("
        UPDATE insurance 
        JOIN members ON insurance.member_id = members.member_id 
        SET insurance.shown = 1 
        WHERE members.gender = 'male' AND insurance.insurance_expiry_date < CURDATE() AND insurance.shown = 0
    ");
}
?>
