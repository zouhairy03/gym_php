<?php
// Start session and include necessary files
session_start();
include 'config.php'; // Include database connection

// Variables for search, filter, and pagination
$search_first_name = isset($_GET['search_first_name']) ? $_GET['search_first_name'] : '';
$search_last_name = isset($_GET['search_last_name']) ? $_GET['search_last_name'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$payment_status = isset($_GET['payment_status']) ? $_GET['payment_status'] : '';
$limit = 5; // Number of records per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Base query for filtering women
$where_clause = "WHERE members.gender = 'female'";

// Search functionality
if (!empty($search_first_name)) {
    $where_clause .= " AND members.first_name LIKE '%$search_first_name%'";
}
if (!empty($search_last_name)) {
    $where_clause .= " AND members.last_name LIKE '%$search_last_name%'";
}

// Payment date filter
if (!empty($start_date)) {
    $where_clause .= " AND payments.payment_date >= '$start_date'";
}
if (!empty($end_date)) {
    $where_clause .= " AND payments.payment_date <= '$end_date'";
}

// Payment status filter
if (!empty($payment_status)) {
    if ($payment_status === 'completed') {
        $where_clause .= " AND payments.pending_amount = 0";
    } elseif ($payment_status === 'pending') {
        $where_clause .= " AND payments.pending_amount > 0";
    }
}

// Query for fetching women's payments
$sql = "SELECT payments.*, members.first_name, members.last_name, members.picture,
               CASE 
                   WHEN payments.pending_amount = 0 THEN 'Completed' 
                   ELSE 'Pending' 
               END AS payment_status,
               (payments.amount_paid - payments.pending_amount) AS amount_difference
        FROM payments 
        JOIN members ON payments.member_id = members.member_id 
        $where_clause 
        ORDER BY payments.payment_id DESC 
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// Count total rows for pagination
$count_query = "SELECT COUNT(*) as total_rows 
                FROM payments 
                JOIN members ON payments.member_id = members.member_id 
                $where_clause";
$count_result = $conn->query($count_query);
$total_rows = $count_result->fetch_assoc()['total_rows'];
$total_pages = ceil($total_rows / $limit);

// Handle Excel export
if (isset($_GET['export_excel'])) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="women_payments.xlsx"');
    header('Cache-Control: max-age=0');

    echo "Payment ID\tFirst Name\tLast Name\tAmount Paid (MAD)\tPending Amount (MAD)\tPayment Date\tPayment Status\n";
    while ($row = $result->fetch_assoc()) {
        echo $row['payment_id'] . "\t" . $row['first_name'] . "\t" . $row['last_name'] . "\t" . number_format($row['amount_paid'], 2) . "\t" . number_format($row['pending_amount'], 2) . "\t" . $row['payment_date'] . "\t" . $row['payment_status'] . "\n";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Women's Payments</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        .wrapper { display: flex; width: 100%; align-items: stretch; }
        .sidebar { width: 250px; background-color: #343a40; padding: 15px; height: 100vh; position: fixed; color: white; transition: all 0.3s ease; }
        .content { width: 100%; padding: 20px; margin-left: 250px; transition: all 0.3s ease; }
        .sidebar-header { font-size: 22px; color: white; margin-bottom: 20px; text-align: center; }
        .image-member { width: 50px; height: 50px; object-fit: cover; border-radius: 50%; }
        .pagination { justify-content: center; }
        .status-completed { color: green; }
        .status-pending { color: orange; }
        .sidebar {
    width: 250px;
    background-color: #343a40;
    padding: 15px;
    height: 100vh;
    position: fixed;
    left: 0;
    transition: all 0.3s ease;
    z-index: 1000; /* Ensure the sidebar appears above other elements */
}

.sidebar.active {
    transform: translateX(-100%); /* Hide the sidebar by moving it off-screen */
}

.content {
    margin-left: 250px; /* Adjust margin to accommodate sidebar */
    transition: all 0.3s ease;
}

.content.active {
    margin-left: 0; /* Remove margin when sidebar is hidden */
}

    </style>
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Page Content -->
    <div class="content">
   <!-- Sidebar Toggle Button -->
<button id="sidebarCollapse" class="btn btn-info">
    <i class="fas fa-bars"></i>
</button>
        <h2 class="mt-4 text-center"><i class="fas fa-credit-card"></i> Women's Payments</h2>

        <!-- Add Payment Button to Trigger Modal -->
        <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#addPaymentModal">
            <i class="fas fa-plus"></i> Add Payment
        </button>

        <!-- Search, Filter, and Export Section -->
        <div class="d-flex justify-content-between mb-3">
            <form method="GET" class="form-inline">
                <input type="text" name="search_first_name" class="form-control mr-2" placeholder="First Name" value="<?php echo htmlspecialchars($search_first_name); ?>">
                <input type="text" name="search_last_name" class="form-control mr-2" placeholder="Last Name" value="<?php echo htmlspecialchars($search_last_name); ?>">
                <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-search"></i> Search</button>
            </form>
            <form method="GET" class="form-inline">
                <input type="date" name="start_date" class="form-control mr-2" value="<?php echo htmlspecialchars($start_date); ?>">
                <input type="date" name="end_date" class="form-control mr-2" value="<?php echo htmlspecialchars($end_date); ?>">
                <select name="payment_status" class="form-control mr-2">
                    <option value="">All</option>
                    <option value="completed" <?php echo ($payment_status == 'completed') ? 'selected' : ''; ?>>Completed</option>
                    <option value="pending" <?php echo ($payment_status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                </select>
                <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-filter"></i> Filter</button>
                <button type="submit" name="export_excel" style="margin-top: 10px;" class="btn btn-success"><i class="fas fa-file-excel"></i> Export</button>
            </form>
        </div>

        <!-- Payment Table -->
        <div class="table-responsive">
            <table class="table table-hover ">
                <thead class="thead">
                    <tr>
                        <th>Picture</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Amount Paid (MAD)</th>
                        <th>Pending Amount (MAD)</th>
                        <th>Payment Date</th>
                        <th>Payment Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><img src="<?php echo $row['picture']; ?>" alt="Member Image" class="image-member"></td>
                                <td><?php echo $row['first_name']; ?></td>
                                <td><?php echo $row['last_name']; ?></td>
                                <td><?php echo number_format($row['amount_paid'], 2); ?></td>
                                <td><?php echo number_format($row['pending_amount'], 2); ?></td>
                                <td><?php echo $row['payment_date']; ?></td>
                                <td class="<?php echo ($row['pending_amount'] == 0) ? 'status-completed' : 'status-pending'; ?>">
                                    <?php echo ($row['pending_amount'] == 0) ? 'Completed' : 'Pending'; ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="view_women_payment.php?id=<?php echo $row['payment_id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                                        <a href="edit__women_payment.php?id=<?php echo $row['payment_id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                        <a href="delete_women_payment.php?id=<?php echo $row['payment_id']; ?>" class="btn btn-danger btn-sm" ><i class="fas fa-trash-alt"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No payments found for women.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>&search_first_name=<?php echo htmlspecialchars($search_first_name); ?>&search_last_name=<?php echo htmlspecialchars($search_last_name); ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&payment_status=<?php echo $payment_status; ?>">&laquo;</a></li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($page == $i) echo 'active'; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&search_first_name=<?php echo htmlspecialchars($search_first_name); ?>&search_last_name=<?php echo htmlspecialchars($search_last_name); ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&payment_status=<?php echo $payment_status; ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>&search_first_name=<?php echo htmlspecialchars($search_first_name); ?>&search_last_name=<?php echo htmlspecialchars($search_last_name); ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&payment_status=<?php echo $payment_status; ?>">&raquo;</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addPaymentForm" action="add_payment_women.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPaymentModalLabel">Add New Payment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Member Select -->
                    <div class="form-group">
                        <label for="memberSelect">Select Member</label>
                        <select class="form-control" id="memberSelect" name="member_id" required>
                            <option value="">-- Select Member --</option>
                            <?php
                            // Fetch active female members
                            $member_sql = "SELECT member_id, first_name, last_name 
                                           FROM members 
                                           WHERE gender = 'female' AND activity_status = 'active'";
                            $member_result = $conn->query($member_sql);
                            if ($member_result->num_rows > 0) {
                                while ($member = $member_result->fetch_assoc()) {
                                    echo "<option value='" . $member['member_id'] . "'>" . $member['first_name'] . " " . $member['last_name'] . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Payment Fields -->
                    <div class="form-group">
                        <label for="amountPaid">Amount Paid (MAD)</label>
                        <input type="number" class="form-control" id="amountPaid" name="amount_paid" required>
                    </div>
                    <div class="form-group">
                        <label for="pendingAmount">Pending Amount (MAD)</label>
                        <input type="number" class="form-control" id="pendingAmount" name="pending_amount" required>
                    </div>
                    <div class="form-group">
                        <label for="paymentDate">Payment Date</label>
                        <input type="date" class="form-control" id="paymentDate" name="payment_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    document.getElementById('sidebarCollapse').addEventListener('click', function () {
        const sidebar = document.getElementById('sidebar');
        const content = document.querySelector('.content');
        sidebar.classList.toggle('active');
        content.classList.toggle('active');
    });
</script>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
