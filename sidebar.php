<?php
// Start session and include necessary files

include 'config.php'; // Database connection

// Queries to get notification counts for expired memberships, pending payments, and expired insurance
$expired_women_query = "
    SELECT COUNT(*) AS expired_count 
    FROM memberships 
    JOIN members ON memberships.member_id = members.member_id 
    WHERE members.gender = 'female' AND memberships.expiry_date < CURDATE();
";
$expired_women_result = $conn->query($expired_women_query);
$expired_women_count = $expired_women_result->fetch_assoc()['expired_count'];

$pending_women_query = "
    SELECT COUNT(*) AS pending_count 
    FROM payments 
    JOIN members ON payments.member_id = members.member_id 
    WHERE members.gender = 'female' AND payments.pending_amount > 0;
";
$pending_women_result = $conn->query($pending_women_query);
$pending_women_count = $pending_women_result->fetch_assoc()['pending_count'];

$expired_insurance_women_query = "
    SELECT COUNT(*) AS expired_count 
    FROM insurance 
    JOIN members ON insurance.member_id = members.member_id 
    WHERE members.gender = 'female' AND insurance.insurance_expiry_date < CURDATE();
";
$expired_insurance_women_result = $conn->query($expired_insurance_women_query);
$expired_insurance_women_count = $expired_insurance_women_result->fetch_assoc()['expired_count'];

$expired_men_query = "
    SELECT COUNT(*) AS expired_count 
    FROM memberships 
    JOIN members ON memberships.member_id = members.member_id 
    WHERE members.gender = 'male' AND memberships.expiry_date < CURDATE();
";
$expired_men_result = $conn->query($expired_men_query);
$expired_men_count = $expired_men_result->fetch_assoc()['expired_count'];

$pending_men_query = "
    SELECT COUNT(*) AS pending_count 
    FROM payments 
    JOIN members ON payments.member_id = members.member_id 
    WHERE members.gender = 'male' AND payments.pending_amount > 0;
";
$pending_men_result = $conn->query($pending_men_query);
$pending_men_count = $pending_men_result->fetch_assoc()['pending_count'];

$expired_insurance_men_query = "
    SELECT COUNT(*) AS expired_count 
    FROM insurance 
    JOIN members ON insurance.member_id = members.member_id 
    WHERE members.gender = 'male' AND insurance.insurance_expiry_date < CURDATE();
";
$expired_insurance_men_result = $conn->query($expired_insurance_men_query);
$expired_insurance_men_count = $expired_insurance_men_result->fetch_assoc()['expired_count'];

// Calculate total notifications
$total_women_notifications = $expired_women_count + $pending_women_count + $expired_insurance_women_count;
$total_men_notifications = $expired_men_count + $pending_men_count + $expired_insurance_men_count;

// Track viewed notifications in session, reset if new notifications are added
if (!isset($_SESSION['viewed_women_notifications']) || $total_women_notifications > $_SESSION['viewed_women_notifications']) {
    $_SESSION['viewed_women_notifications'] = 0;
}
if (!isset($_SESSION['viewed_men_notifications']) || $total_men_notifications > $_SESSION['viewed_men_notifications']) {
    $_SESSION['viewed_men_notifications'] = 0;
}

// Determine the count of new notifications
$new_women_notifications = max(0, $total_women_notifications - $_SESSION['viewed_women_notifications']);
$new_men_notifications = max(0, $total_men_notifications - $_SESSION['viewed_men_notifications']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sidebar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #f8f9fa;
            position: fixed;
            left: 0;
            top: 0;
            overflow-x: hidden;
            transition: all 0.3s ease;
            z-index: 100;
        }

        .sidebar-header {
            font-size: 20px;
            font-weight: bold;
            color: #343a40;
            text-align: center;
            padding: 15px 0;
        }

        .sidebar .admin-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 2px solid #343a40;
            margin: 0 auto;
            display: block;
        }

        .sidebar .welcome-message {
            font-size: 18px;
            color: #343a40;
            text-align: center;
            margin: 10px 0;
        }

        .sidebar a {
            color: #343a40;
            font-size: 18px;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 10px 15px;
            transition: all 0.2s ease;
            border-radius: 5px;
            margin: 5px 0;
        }

        .sidebar a:hover {
            background-color: #e0e0e0;
        }

        .notification-icon {
            position: relative;
        }

        .badge-danger {
            background-color: #ff4136;
            color: white;
            margin-left: 10px;
            padding: 2px 6px;
            border-radius: 50%;
        }

        .shake-animation {
            animation: shake 0.5s ease-in-out infinite;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">

    <img src="path/to/admin_image.jpg" alt="Admin Image" class="admin-image"> <!-- Replace path with actual image path -->
    <div class="welcome-message">Welcome, Admin</div>
    <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard </a>

    <!-- Members Dropdown -->
    <a href="#membersSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fas fa-users"></i> Members</a>
    <ul class="collapse list-unstyled" id="membersSubmenu">
        <li><a href="members_women.php"><i class="fas fa-female"></i> Women</a></li>
        <li><a href="members_men.php"><i class="fas fa-male"></i> Men</a></li>
    </ul>

    <!-- Memberships Dropdown -->
    <a href="#membershipsSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fas fa-id-badge"></i> Memberships</a>
    <ul class="collapse list-unstyled" id="membershipsSubmenu">
        <li><a href="memberships_women.php"><i class="fas fa-female"></i> Women</a></li>
        <li><a href="memberships_men.php"><i class="fas fa-male"></i> Men</a></li>
    </ul>

    <!-- Payments Dropdown -->
    <a href="#paymentsSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fas fa-credit-card"></i> Payments</a>
    <ul class="collapse list-unstyled" id="paymentsSubmenu">
        <li><a href="payments_women.php"><i class="fas fa-female"></i> Women</a></li>
        <li><a href="payments_men.php"><i class="fas fa-male"></i> Men</a></li>
    </ul>

    <!-- Notifications Dropdown -->
    <a href="#notificationsSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle" onclick="stopShakeAnimation()">
        <i class="fas fa-bell notification-icon <?php if ($new_women_notifications > 0 || $new_men_notifications > 0) echo 'shake-animation'; ?>"></i> Notifications
    </a>
    <ul class="collapse list-unstyled" id="notificationsSubmenu">
        <li>
            <a href="notifications_women.php" onclick="clearNotification('women')">
                <i class="fas fa-female"></i> Women 
                <?php if ($new_women_notifications > 0): ?>
                    <span class="badge badge-danger" id="womenNotifCount"><?php echo $new_women_notifications; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li>
            <a href="notifications_men.php" onclick="clearNotification('men')">
                <i class="fas fa-male"></i> Men 
                <?php if ($new_men_notifications > 0): ?>
                    <span class="badge badge-danger" id="menNotifCount"><?php echo $new_men_notifications; ?></span>
                <?php endif; ?>
            </a>
        </li>
    </ul>

    <!-- Insurance Dropdown -->
    <a href="#insuranceSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fas fa-shield-alt"></i> Insurance</a>
    <ul class="collapse list-unstyled" id="insuranceSubmenu">
        <li><a href="insurance_women.php"><i class="fas fa-female"></i> Women</a></li>
        <li><a href="insurance_men.php"><i class="fas fa-male"></i> Men</a></li>
    </ul>

    <!-- Reports Dropdown -->
    <a href="#reportsSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fas fa-chart-line"></i> Reports</a>
    <ul class="collapse list-unstyled" id="reportsSubmenu">
        <li><a href="reports_women.php"><i class="fas fa-female"></i> Women</a></li>
        <li><a href="reports_men.php"><i class="fas fa-male"></i> Men</a></li>
    </ul>

    <!-- Profile and Logout -->
    <a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<script>
    function stopShakeAnimation() {
        const notificationIcon = document.querySelector('.notification-icon');
        if (notificationIcon.classList.contains('shake-animation')) {
            notificationIcon.classList.remove('shake-animation');
        }
    }

    function clearNotification(gender) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'clear_notifications.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send(`gender=${gender}`);

        if (gender === 'women') {
            document.getElementById('womenNotifCount').style.display = 'none';
            <?php $_SESSION['viewed_women_notifications'] = $total_women_notifications; ?>
        } else if (gender === 'men') {
            document.getElementById('menNotifCount').style.display = 'none';
            <?php $_SESSION['viewed_men_notifications'] = $total_men_notifications; ?>
        }
    }
</script>

</body>
</html>
