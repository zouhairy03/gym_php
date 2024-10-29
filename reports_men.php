<?php
session_start();
include 'config.php';

// Global Date Filtering for all queries
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$date_filter = '';
if (!empty($start_date) && !empty($end_date)) {
    $date_filter = "AND created_at BETWEEN '$start_date' AND '$end_date'";
}

// KPIs
$total_payments_query = "SELECT COUNT(*) as total FROM payments JOIN members ON payments.member_id = members.member_id WHERE members.gender = 'male' $date_filter";
$total_amount_query = "SELECT COALESCE(SUM(amount_paid), 0) as total FROM payments JOIN members ON payments.member_id = members.member_id WHERE members.gender = 'male' $date_filter";
$average_payment_query = "SELECT COALESCE(AVG(amount_paid), 0) as average FROM payments JOIN members ON payments.member_id = members.member_id WHERE members.gender = 'male' $date_filter";
$paid_payments_query = "SELECT COUNT(*) as total FROM payments JOIN members ON payments.member_id = members.member_id WHERE members.gender = 'male' AND pending_amount = 0 $date_filter";
$unpaid_payments_query = "SELECT COUNT(*) as total FROM payments JOIN members ON payments.member_id = members.member_id WHERE members.gender = 'male' AND pending_amount > 0 $date_filter";

// Total Users by Gender and Filtered Date
$total_users_query = "SELECT COUNT(*) as total FROM members WHERE gender = 'male' $date_filter";
$total_memberships_query = "SELECT COUNT(*) as total FROM memberships JOIN members ON memberships.member_id = members.member_id WHERE members.gender = 'male' $date_filter";

// Top Paying Members with Date Filter
$top_members_query = "SELECT members.first_name, members.last_name, SUM(payments.amount_paid) as total_paid 
                      FROM payments 
                      JOIN members ON payments.member_id = members.member_id 
                      WHERE members.gender = 'male' $date_filter
                      GROUP BY payments.member_id 
                      ORDER BY total_paid DESC 
                      LIMIT 5";

// Monthly Payments Trend Data with Date Filter
$monthly_payments_query = "
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount_paid) as total 
    FROM payments 
    JOIN members ON payments.member_id = members.member_id 
    WHERE members.gender = 'male' $date_filter
    GROUP BY month 
    ORDER BY month DESC 
    LIMIT 12";

// Insurance Status with Date Filter
$insurance_status_query = "
    SELECT 
        SUM(CASE WHEN insurance.insurance_expiry_date > CURDATE() THEN 1 ELSE 0 END) AS active_insurance,
        SUM(CASE WHEN insurance.insurance_expiry_date <= CURDATE() THEN 1 ELSE 0 END) AS expired_insurance
    FROM insurance
    JOIN members ON insurance.member_id = members.member_id
    WHERE members.gender = 'male' $date_filter";

// Total Users by Month (filtered by date range)
$total_users_by_month_query = "
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as user_count
    FROM members
    WHERE gender = 'male' $date_filter
    GROUP BY month
    ORDER BY month DESC
    LIMIT 12";

// Fetch Results for KPIs with a fallback to "No data found" if empty
$total_payments = $conn->query($total_payments_query)->fetch_assoc()['total'] ?? 'No data found';
$total_amount = $conn->query($total_amount_query)->fetch_assoc()['total'] ?? 'No data found';
$average_payment = $conn->query($average_payment_query)->fetch_assoc()['average'] ?? 'No data found';
$total_users = $conn->query($total_users_query)->fetch_assoc()['total'] ?? 'No data found';
$total_memberships = $conn->query($total_memberships_query)->fetch_assoc()['total'] ?? 'No data found';
$paid_payments = $conn->query($paid_payments_query)->fetch_assoc()['total'] ?? 'No data found';
$unpaid_payments = $conn->query($unpaid_payments_query)->fetch_assoc()['total'] ?? 'No data found';

// Fetch Top Paying Members or "No data found"
$top_members_result = $conn->query($top_members_query);
$top_members = $top_members_result->num_rows > 0 ? $top_members_result : null;

// Monthly payments data for chart or fallback to empty array
$monthly_payments_data = [];
$monthly_payments_result = $conn->query($monthly_payments_query);
while ($row = $monthly_payments_result->fetch_assoc()) {
    $monthly_payments_data[] = $row;
}

// Insurance data for pie chart
$insurance_data = $conn->query($insurance_status_query)->fetch_assoc();
if (!$insurance_data['active_insurance'] && !$insurance_data['expired_insurance']) {
    $insurance_data = ['active_insurance' => 0, 'expired_insurance' => 0];
}

// Total users by month data for chart or fallback to empty array
$total_users_by_month_data = [];
$total_users_by_month_result = $conn->query($total_users_by_month_query);
while ($row = $total_users_by_month_result->fetch_assoc()) {
    $total_users_by_month_data[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>men's Reports</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body { font-family: 'Roboto', sans-serif; }
        .content { margin-left: 250px; padding: 20px; transition: margin-left 0.3s ease; }
        .card { border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); text-align: center; color: #fff; }
        .card h5 { font-size: 18px; font-weight: bold; }
        .card p { font-size: 24px; font-weight: bold; }
        #sidebarCollapse { background-color: #007bff; color: white; border: none; }
        #sidebarCollapse:hover { background-color: #0056b3; }
        .chart-container { width: 100%; max-width: 600px; margin: auto; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<!-- Sidebar -->
<?php include 'sidebar.php'; ?>

<div class="content">
    <button id="sidebarCollapse" class="btn mb-3"><i class="fas fa-bars"></i></button>
    <h2 class="dashboard-header" style="text-align: center;background: lightblue;color: white;">
        <i class="fas fa-male"></i> men's Reports

        </h2>



        


    <!-- Date Filter Form -->
    <div class="filter-section mb-4">
        <form method="GET" class="form-inline">
            <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>" placeholder="Start Date">
            <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>" placeholder="End Date">
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card p-4 bg-primary">
                <h5>Total Payments</h5>
                <p><?php echo is_numeric($total_payments) ? "$total_payments payments" : $total_payments; ?></p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-4 bg-success">
                <h5>Total Amount</h5>
                <p><?php echo is_numeric($total_amount) ? number_format($total_amount, 2) . " MAD" : $total_amount; ?></p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-4 bg-info">
                <h5>Average Payment</h5>
                <p><?php echo is_numeric($average_payment) ? number_format($average_payment, 2) . " MAD" : $average_payment; ?></p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-4 bg-warning">
                <h5>Total Users</h5>
                <p><?php echo is_numeric($total_users) ? "$total_users users" : $total_users; ?></p>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <h1 style="text-align: center;font-size: 2.3rem;"><i class="fas fa-chart-line"></i> Monthly Payments Overview</h1>
    <div class="chart-container mb-4">
        <?php if (empty($monthly_payments_data)): ?>
            <p>No data found</p>
        <?php else: ?>
            <canvas id="monthlyPaymentsChart"></canvas>
        <?php endif; ?>
    </div>

    <h1 style="text-align: center;font-size: 2.3rem;"><i class="fas fa-user-plus"></i> New Users by Month</h1>
    <div class="chart-container mb-4">
        <?php if (empty($total_users_by_month_data)): ?>
            <p>No data found</p>
        <?php else: ?>
            <canvas id="usersByMonthChart"></canvas>
        <?php endif; ?>
    </div>

    <h1 style="text-align: center;font-size: 2.3rem;"><i class="fas fa-shield-alt"></i> Insurance Status</h1>
    <div class="chart-container mb-4">
        <?php if ($insurance_data['active_insurance'] == 0 && $insurance_data['expired_insurance'] == 0): ?>
            <p>No data found</p>
        <?php else: ?>
            <canvas id="insuranceStatusChart"></canvas>
        <?php endif; ?>
    </div>

    <!-- Top Paying Members Table -->
    <div class="mb-4">
        <h5 style="text-align: center;font-size: 2.3rem;">Top Paying Members</h5>
        <?php if (is_null($top_members)): ?>
            <p>No data found</p>
        <?php else: ?>
            <table class="table table-hover" style="text-align: center;">
                <thead>
                    <tr><th>Name</th><th>Total Paid (MAD)</th></tr>
                </thead>
                <tbody>
                    <?php while ($member = $top_members->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></td>
                            <td><?php echo number_format($member['total_paid'], 2); ?> MAD</td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
// Sidebar toggle
document.getElementById('sidebarCollapse').addEventListener('click', function () {
    document.getElementById('sidebar').classList.toggle('active');
    document.querySelector('.content').classList.toggle('active');
});

// Monthly Payments Chart
if (document.getElementById('monthlyPaymentsChart')) {
    const monthlyPaymentsData = {
        labels: <?php echo json_encode(array_column($monthly_payments_data, 'month')); ?>,
        datasets: [{
            label: 'Payments Amount (MAD)',
            data: <?php echo json_encode(array_column($monthly_payments_data, 'total')); ?>,
            backgroundColor: '#007bff',
            borderColor: '#007bff',
            fill: false,
        }]
    };
    new Chart(document.getElementById('monthlyPaymentsChart'), { type: 'line', data: monthlyPaymentsData });
}

// Users by Month Chart
if (document.getElementById('usersByMonthChart')) {
    const usersByMonthData = {
        labels: <?php echo json_encode(array_column($total_users_by_month_data, 'month')); ?>,
        datasets: [{
            label: 'New Users',
            data: <?php echo json_encode(array_column($total_users_by_month_data, 'user_count')); ?>,
            backgroundColor: '#28a745',
            borderColor: '#28a745',
            fill: false,
        }]
    };
    new Chart(document.getElementById('usersByMonthChart'), { type: 'line', data: usersByMonthData });
}

// Insurance Status Chart
if (document.getElementById('insuranceStatusChart')) {
    const insuranceData = {
        labels: ['Active Insurance', 'Expired Insurance'],
        datasets: [{
            data: [<?php echo $insurance_data['active_insurance']; ?>, <?php echo $insurance_data['expired_insurance']; ?>],
            backgroundColor: ['#28a745', '#dc3545']
        }]
    };
    new Chart(document.getElementById('insuranceStatusChart'), { type: 'pie', data: insuranceData });
}
</script>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
