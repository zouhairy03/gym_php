<?php
// dashboard.php
session_start();
require 'config.php'; // Include the database configuration

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch admin details
$admin_id = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT username, profile_picture FROM admin WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Placeholder profile picture if not set
$imagePath = (!empty($admin_data['profile_picture']) && file_exists($admin_data['profile_picture']))
             ? $admin_data['profile_picture']
             : 'https://via.placeholder.com/100';

// Define months array
$months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

// Initialize chart data arrays for men and women
$monthly_revenue_men = $monthly_revenue_women = $monthly_user_growth_men = $monthly_user_growth_women = $monthly_memberships_men = $monthly_memberships_women = array_fill(1, 12, 0);
$active_members_men = $active_members_women = $inactive_members_men = $inactive_members_women = 0;

// Calculate monthly revenue for men and women
for ($i = 1; $i <= 12; $i++) {
    $stmt = $conn->prepare("SELECT gender, SUM(amount_paid) AS total FROM payments JOIN members ON payments.member_id = members.member_id WHERE MONTH(payment_date) = ? GROUP BY gender");
    $stmt->bind_param("i", $i);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if ($row['gender'] == 'male') {
            $monthly_revenue_men[$i] = $row['total'];
        } else {
            $monthly_revenue_women[$i] = $row['total'];
        }
    }
    $stmt->close();
}

// Calculate monthly user growth for men and women
for ($i = 1; $i <= 12; $i++) {
    $stmt = $conn->prepare("SELECT gender, COUNT(*) AS total FROM members WHERE MONTH(created_at) = ? GROUP BY gender");
    $stmt->bind_param("i", $i);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if ($row['gender'] == 'male') {
            $monthly_user_growth_men[$i] = $row['total'];
        } else {
            $monthly_user_growth_women[$i] = $row['total'];
        }
    }
    $stmt->close();
}

// Calculate monthly memberships for men and women
for ($i = 1; $i <= 12; $i++) {
    $stmt = $conn->prepare("SELECT gender, COUNT(*) AS total FROM memberships JOIN members ON memberships.member_id = members.member_id WHERE MONTH(start_date) = ? GROUP BY gender");
    $stmt->bind_param("i", $i);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if ($row['gender'] == 'male') {
            $monthly_memberships_men[$i] = $row['total'];
        } else {
            $monthly_memberships_women[$i] = $row['total'];
        }
    }
    $stmt->close();
}

// Fetch totals for cards and active/inactive members by gender
$total_revenue = array_sum($monthly_revenue_men) + array_sum($monthly_revenue_women);
$total_members = $new_members = $total_memberships = 0;

$stmt = $conn->prepare("SELECT COUNT(*) AS total_members, SUM(activity_status = 'active') AS active_members, SUM(activity_status = 'inactive') AS inactive_members FROM members");
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$total_members = $result['total_members'];
$active_members = $result['active_members'];
$inactive_members = $result['inactive_members'];
$stmt->close();

// Active/Inactive members by gender
$stmt = $conn->prepare("SELECT gender, SUM(activity_status = 'active') AS active_count, SUM(activity_status = 'inactive') AS inactive_count FROM members GROUP BY gender");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    if ($row['gender'] == 'male') {
        $active_members_men = $row['active_count'];
        $inactive_members_men = $row['inactive_count'];
    } else {
        $active_members_women = $row['active_count'];
        $inactive_members_women = $row['inactive_count'];
    }
}
$stmt->close();

// Count of new members this month
$current_month = date('m');
$stmt = $conn->prepare("SELECT COUNT(*) AS new_members FROM members WHERE MONTH(created_at) = ?");
$stmt->bind_param("i", $current_month);
$stmt->execute();
$new_members = $stmt->get_result()->fetch_assoc()['new_members'];
$stmt->close();

// Total memberships
$stmt = $conn->prepare("SELECT COUNT(*) AS total_memberships FROM memberships");
$stmt->execute();
$total_memberships = $stmt->get_result()->fetch_assoc()['total_memberships'];
$stmt->close();





// Fetch notifications from the database

// Fetch new (unshown) expiring memberships with names and expiry dates
$expiring_memberships_query = "
    SELECT m.member_id, m.first_name, m.last_name, mb.expiry_date, m.gender 
    FROM memberships mb 
    JOIN members m ON mb.member_id = m.member_id 
    WHERE mb.expiry_date <= DATE_ADD(NOW(), INTERVAL 10 DAY) AND mb.shown = 0
    ORDER BY mb.expiry_date ASC";
$expiring_memberships_result = $conn->query($expiring_memberships_query);

// Fetch only expired (unshown) insurances with names and expiry dates
$expired_insurance_query = "
    SELECT m.member_id, m.first_name, m.last_name, i.insurance_expiry_date, m.gender 
    FROM insurance i 
    JOIN members m ON i.member_id = m.member_id 
    WHERE i.insurance_expiry_date < NOW() AND i.shown = 0
    ORDER BY i.insurance_expiry_date ASC";
$expired_insurance_result = $conn->query($expired_insurance_query);

// Fetch only pending payments (unshown) with a non-zero pending amount
$pending_payments_query = "
    SELECT m.member_id, m.first_name, m.last_name, p.payment_date, m.gender, p.pending_amount 
    FROM payments p 
    JOIN members m ON p.member_id = m.member_id 
    WHERE p.pending_amount > 0 AND p.shown = 0
    ORDER BY p.payment_date DESC";
$pending_payments_result = $conn->query($pending_payments_query);
// Prepare notifications array to store each notification
$notifications = [];

// Add membership expiry notifications with gender-based links
if ($expiring_memberships_result->num_rows > 0) {
    while ($row = $expiring_memberships_result->fetch_assoc()) {
        // Determine gender-specific link for membership
        $link = $row['gender'] === 'female' ? "memberships_women.php" : "memberships_men.php";
        
        $notifications[] = [
            'type' => 'Membership Expiry',
            'message' => "Membership for {$row['first_name']} {$row['last_name']} is expiring on {$row['expiry_date']}.",
            'link' => $link,
            'color' => '#FFCCCB',
            'icon' => 'bi-exclamation-circle' // Bootstrap icon for alert
        ];
    }
    // Mark all fetched memberships as shown
    $conn->query("UPDATE memberships SET shown = 1 WHERE expiry_date <= DATE_ADD(NOW(), INTERVAL 10 DAY) AND shown = 0");
}

// Add expired insurance notifications with gender-based links
if ($expired_insurance_result->num_rows > 0) {
    while ($row = $expired_insurance_result->fetch_assoc()) {
        // Determine gender-specific link for insurance
        $link = $row['gender'] === 'female' ? "insurance_women.php" : "insurance_men.php";
        
        $notifications[] = [
            'type' => 'Insurance Expiry',
            'message' => "Insurance for {$row['first_name']} {$row['last_name']} expired on {$row['insurance_expiry_date']}.",
            'link' => $link,
            'color' => '#CCFFCC',
            'icon' => 'bi-shield-exclamation' // Bootstrap icon for insurance
        ];
    }
    // Mark all fetched insurances as shown
    $conn->query("UPDATE insurance SET shown = 1 WHERE insurance_expiry_date < NOW() AND shown = 0");
}



// Initialize arrays for male and female pending payments by month
$pending_payments_men = array_fill(1, 12, 0);
$pending_payments_women = array_fill(1, 12, 0);

// Loop through each month to calculate pending payments by gender
for ($month = 1; $month <= 12; $month++) {
    $stmt = $conn->prepare("SELECT gender, SUM(pending_amount) AS total_pending FROM payments JOIN members ON payments.member_id = members.member_id WHERE MONTH(payment_date) = ? AND pending_amount > 0 GROUP BY gender");
    $stmt->bind_param("i", $month);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if ($row['gender'] == 'male') {
            $pending_payments_men[$month] = $row['total_pending'] ?? 0;
        } else if ($row['gender'] == 'female') {
            $pending_payments_women[$month] = $row['total_pending'] ?? 0;
        }
    }
    $stmt->close();
}

// Convert data to JSON format for JavaScript
$pending_payments_men_json = json_encode(array_values($pending_payments_men));
$pending_payments_women_json = json_encode(array_values($pending_payments_women));






// Initialize arrays for male and female expiring memberships
$expiring_memberships_men = array_fill(1, 12, 0);
$expiring_memberships_women = array_fill(1, 12, 0);

// Loop through each month and count expiring memberships by gender
for ($month = 1; $month <= 12; $month++) {
    $stmt = $conn->prepare("SELECT gender, COUNT(*) AS expiring_count FROM memberships JOIN members ON memberships.member_id = members.member_id WHERE MONTH(expiry_date) = ? GROUP BY gender");
    $stmt->bind_param("i", $month);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if ($row['gender'] == 'male') {
            $expiring_memberships_men[$month] = $row['expiring_count'];
        } else if ($row['gender'] == 'female') {
            $expiring_memberships_women[$month] = $row['expiring_count'];
        }
    }
    $stmt->close();
}

// Convert data to JSON for JavaScript
$expiring_memberships_men_json = json_encode(array_values($expiring_memberships_men));
$expiring_memberships_women_json = json_encode(array_values($expiring_memberships_women));


// Initialize arrays for male and female insurance expirations
$insurance_expiry_men = array_fill(1, 12, 0);
$insurance_expiry_women = array_fill(1, 12, 0);

// Loop through each month and count expiring insurances by gender
for ($month = 1; $month <= 12; $month++) {
    $stmt = $conn->prepare("SELECT gender, COUNT(*) AS expiring_insurance FROM insurance JOIN members ON insurance.member_id = members.member_id WHERE MONTH(insurance_expiry_date) = ? GROUP BY gender");
    $stmt->bind_param("i", $month);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if ($row['gender'] == 'male') {
            $insurance_expiry_men[$month] = $row['expiring_insurance'];
        } else if ($row['gender'] == 'female') {
            $insurance_expiry_women[$month] = $row['expiring_insurance'];
        }
    }
    $stmt->close();
}

// Convert data to JSON for JavaScript
$insurance_expiry_men_json = json_encode(array_values($insurance_expiry_men));
$insurance_expiry_women_json = json_encode(array_values($insurance_expiry_women));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            display: flex;
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .wrapper {
    display: flex;
    width: 100%;
}
.sidebar {
    width: 250px;
    background-color: #343a40;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    transition: transform 0.3s ease;
    transform: translateX(0);
    z-index: 100;
}

.sidebar.active {
    transform: translateX(-100%);
}



#content {
    width: calc(100% - 250px);
    margin-left: 250px;
    padding: 20px;
    transition: all 0.3s ease;
}


#content.active {
    margin-left: 0;
    width: 100%;
}
        .rounded-img {
            border-radius: 50%;
            width: 100px;
            height: 100px;
            object-fit: cover;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 10px 0;
        }
        .chart-container {
            width: 30%;
            margin: 1%;
            display: inline-block;
            height: 300px;
        }
        .card-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .card {
            flex: 1 1 calc(25% - 1rem);
            min-width: 150px;
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            position: relative;
        }
        .card h4 {
            font-size: 1.2rem;
        }
        .card p {
            font-size: 1.4rem;
            font-weight: bold;
            margin-top: 5px;
        }
        .bg-lightblue { background-color: #007bff; }
        .bg-green { background-color: #28a745; }
        .bg-yellow { background-color: #ffc107; }
        .bg-red { background-color: #dc3545; }
        .bg-purple { background-color: #6f42c1; }
        .bg-orange { background-color: #fd7e14; }
        .icon {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 1.5rem;
            opacity: 0.8;
        }

        /* Notification container positioned at the top-right of the page */
        .notification-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            width: 100%;
            max-width: 350px;
            gap: 10px; /* Space between notifications */
        }

        /* Styling for each notification item */
        .notification-item {
            padding: 10px 15px;
            border-radius: 5px;
            color: #333;
            font-size: 0.9rem; /* Smaller font size for the message */
            opacity: 1;
            transition: opacity 1s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Fade out effect */
        .notification-item.fade-out {
            opacity: 0;
        }
        /* Animation for welcome message */
@keyframes fadeInUp {
    0% {
        opacity: 0;
        transform: translateY(20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.welcome-message {
    animation: fadeInUp 1s ease-in-out forwards;
    opacity: 0; /* Start hidden for animation */
}

    </style>
</head>
<body>
<div class="wrapper">
<div class="sidebar" id="sidebar">
        <?php include 'sidebar.php'; ?>
    </div>


    <div id="content">

    <button id="sidebarCollapse" class="btn btn-info">
        <i class="fas fa-align-justify"></i>
    </button>

    <div id="notification-container" class="notification-container">
        <?php foreach ($notifications as $notification): ?>
            <div class="notification-item alert alert-dismissible fade show" style="background-color: <?php echo $notification['color']; ?>;">
                <!-- Icon -->
                <i class="bi <?php echo $notification['icon']; ?>" style="font-size: 1.2rem;"></i>
                <!-- Message -->
                <div>
                    <strong><?php echo $notification['type']; ?>:</strong> <?php echo $notification['message']; ?>
                    <br>
                    <a href="<?php echo $notification['link']; ?>" class="alert-link">Check the list</a>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    </div>




    <div class="text-center">
    <h2 class="welcome-message">Welcome back, <?php echo htmlspecialchars($admin_data['username']); ?>!</h2>
    <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Admin Image" class="rounded-img">
        </div>

        <!-- Cards Section -->
        <div class="card-container">
            <div class="card bg-lightblue">
                <i class="fas fa-dollar-sign icon"></i>
                <h4>Total Revenue</h4>
                <p><?php echo number_format($total_revenue, 2); ?> MAD</p>
            </div>
            <div class="card bg-green">
                <i class="fas fa-users icon"></i>
                <h4>Total Members</h4>
                <p><?php echo $total_members; ?></p>
            </div>
            <div class="card bg-yellow">
                <i class="fas fa-user-check icon"></i>
                <h4>Active Members</h4>
                <p><?php echo $active_members; ?></p>
            </div>
            <div class="card bg-red">
                <i class="fas fa-user-times icon"></i>
                <h4>Inactive Members</h4>
                <p><?php echo $inactive_members; ?></p>
            </div>
            <div class="card bg-purple">
                <i class="fas fa-user-plus icon"></i>
                <h4>New Members (This Month)</h4>
                <p><?php echo $new_members; ?></p>
            </div>
            <div class="card bg-orange">
                <i class="fas fa-id-card icon"></i>
                <h4>Total Memberships</h4>
                <p><?php echo $total_memberships; ?></p>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="chart-container">
        <h3 style="text-align: center; margin-top: 50px;">
    <i class="fas fa-chart-line" style="margin-right: 8px;"></i> Monthly Revenue
</h3>
            <canvas id="revenueChart"></canvas>
        </div>
        
        <div class="chart-container">
        <h3 style="text-align: center;">
    <i class="fas fa-users" style="margin-right: 8px;"></i> Monthly User Growth
</h3>
            <canvas id="userGrowthChart"></canvas>
        </div>
        
        <div class="chart-container">
        <h3 style="text-align: center;">
    <i class="fas fa-id-card" style="margin-right: 8px;"></i> Monthly Memberships
</h3>
            <canvas id="membershipsChart"></canvas>
        </div>

       <div class="chart-container" style="margin-top:80px;">
    <h3 style="text-align: center;">
        <i class="fas fa-shield-alt" style="margin-right: 8px;"></i> Insurance Expiry by Month
    </h3>
    <canvas id="insuranceExpiryChart"></canvas>
</div>

        <div class="chart-container">
    <h3 style="text-align: center;">
        <i class="fas fa-calendar-times" style="margin-right: 8px;"></i> Expiring Memberships by Month
    </h3>
    <canvas id="expiringMembershipsChart"></canvas>
</div>

 

        <div class="chart-container">
    <h3 style="text-align: center;">
        <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i> Pending Payments by Month
    </h3>
    <canvas id="pendingPaymentsChart"></canvas>
</div>




</div>






<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelector(".welcome-message").style.animationDelay = "0.5s";
});

// Get the context of the canvas element


document.getElementById('sidebarCollapse').addEventListener('click', function () {
    document.querySelector('.sidebar').classList.toggle('active');
    document.getElementById('content').classList.toggle('active');
});


    const revenueDataMen = <?php echo json_encode(array_values($monthly_revenue_men)); ?>;
    const revenueDataWomen = <?php echo json_encode(array_values($monthly_revenue_women)); ?>;
    const userGrowthDataMen = <?php echo json_encode(array_values($monthly_user_growth_men)); ?>;
    const userGrowthDataWomen = <?php echo json_encode(array_values($monthly_user_growth_women)); ?>;
    const membershipsDataMen = <?php echo json_encode(array_values($monthly_memberships_men)); ?>;
    const membershipsDataWomen = <?php echo json_encode(array_values($monthly_memberships_women)); ?>;
    const months = <?php echo json_encode($months); ?>;
    const activeMembersData = [<?php echo $active_members_men; ?>, <?php echo $active_members_women; ?>];
    const inactiveMembersData = [<?php echo $inactive_members_men; ?>, <?php echo $inactive_members_women; ?>];



    // Monthly Revenue Chart
    new Chart(document.getElementById("revenueChart").getContext("2d"), {
        type: "line",
        data: {
            labels: months,
            datasets: [
                { label: "Men", data: revenueDataMen, borderColor: "lightblue", fill: false },
                { label: "Women", data: revenueDataWomen, borderColor: "pink", fill: false }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // Monthly User Growth Chart
    new Chart(document.getElementById("userGrowthChart").getContext("2d"), {
        type: "bar",
        data: {
            labels: months,
            datasets: [
                { label: "Men", data: userGrowthDataMen, backgroundColor: "lightblue" },
                { label: "Women", data: userGrowthDataWomen, backgroundColor: "pink" }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // Monthly Memberships Chart
    new Chart(document.getElementById("membershipsChart").getContext("2d"), {
        type: "bar",
        data: {
            labels: months,
            datasets: [
                { label: "Men", data: membershipsDataMen, backgroundColor: "lightblue" },
                { label: "Women", data: membershipsDataWomen, backgroundColor: "pink" }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

 

   



  // Select all notification items
  const notificationItems = document.querySelectorAll('.notification-item');

// Loop through each notification to apply a fade-out delay
notificationItems.forEach((item, index) => {
    const delay = (index + 1) * 4000; // 4 seconds delay per notification

    setTimeout(() => {
        item.classList.add('fade-out'); // Apply fade-out class to trigger CSS opacity transition
    }, delay);

    // Remove the notification from the DOM after it fades out
    setTimeout(() => {
        item.remove();
    }, delay + 1000); // Allow 1 second for fade-out transition before removing
});
// Define months for the chart labels


// Retrieve the pending payments data from PHP


// Render the Pending Payments by Month Chart

const pendingPaymentsMenData = <?php echo $pending_payments_men_json; ?>;
const pendingPaymentsWomenData = <?php echo $pending_payments_women_json; ?>;

new Chart(document.getElementById("pendingPaymentsChart").getContext("2d"), {
    type: "bar",
    data: {
        labels: months,
        datasets: [
            {
                label: "Men",
                data: pendingPaymentsMenData,
                backgroundColor: "lightblue",


            },
            {
                label: "Women",
                data: pendingPaymentsWomenData,
                backgroundColor: "pink",


            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Pending Payments (MAD)'
                }
            }
        }
    }
});



const expiringMembershipsMenData = <?php echo $expiring_memberships_men_json; ?>;
const expiringMembershipsWomenData = <?php echo $expiring_memberships_women_json; ?>;

new Chart(document.getElementById("expiringMembershipsChart").getContext("2d"), {
    type: "bar",
    data: {
        labels: months,
        datasets: [
            {
                label: "Men",
                data: expiringMembershipsMenData,
                backgroundColor: "lightblue",
                // borderColor: "blue",
                // borderWidth: 1
            },
            {
                label: "Women",
                data: expiringMembershipsWomenData,
                backgroundColor: "pink",
                // borderColor: "darkred",
                // borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Expiring Memberships Count'
                }
            }
        }
    }
});


const insuranceExpiryMenData = <?php echo $insurance_expiry_men_json; ?>;
const insuranceExpiryWomenData = <?php echo $insurance_expiry_women_json; ?>;

new Chart(document.getElementById("insuranceExpiryChart").getContext("2d"), {
    type: "bar",
    data: {
        labels: months,
        datasets: [
            {
                label: "Men",
                data: insuranceExpiryMenData,
                backgroundColor: "lightblue",

                // borderWidth: 1
            },
            {
                label: "Women",
                data: insuranceExpiryWomenData,
                backgroundColor: "pink",

                // borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Expiring Insurances Count'
                }
            }
        }
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
