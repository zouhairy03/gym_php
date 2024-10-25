<?php
include 'config.php';

if (isset($_POST['member_id'])) {
    $member_id = $_POST['member_id'];
    
    $sql = "SELECT memberships.amount_paid, memberships.pending_amount, members.picture 
            FROM members 
            JOIN memberships ON members.member_id = memberships.member_id 
            WHERE members.member_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode([]);
    }
}
?>
