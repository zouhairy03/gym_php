<?php
// Start session and include necessary files
session_start();
include 'config.php'; // Include database connection

// Variables for search, date filter, and pagination
$search = isset($_GET['search']) ? $_GET['search'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$limit = 5; // Number of records per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Base query for expired memberships with search functionality and date filters
$where_clause = "WHERE members.gender = 'male' AND (members.first_name LIKE '%$search%' OR members.last_name LIKE '%$search%')";

if (!empty($start_date)) {
    $where_clause .= " AND memberships.expiry_date >= '$start_date'";
}

if (!empty($end_date)) {
    $where_clause .= " AND memberships.expiry_date <= '$end_date'";
}

// SQL query to get expired memberships, ordered by newest first
$expired_sql = "
    SELECT memberships.*, members.first_name, members.last_name, members.picture 
    FROM memberships 
    JOIN members ON memberships.member_id = members.member_id 
    $where_clause AND memberships.expiry_date < CURDATE()
    ORDER BY memberships.expiry_date DESC 
    LIMIT $limit OFFSET $offset";

// Fetch the expired memberships
$expired_result = $conn->query($expired_sql);

// Count total expired memberships for pagination
$count_query = "
    SELECT COUNT(*) as total_rows 
    FROM memberships 
    JOIN members ON memberships.member_id = members.member_id 
    WHERE members.gender = 'male' AND memberships.expiry_date < CURDATE() 
    AND (members.first_name LIKE '%$search%' OR members.last_name LIKE '%$search%')";
$count_result = $conn->query($count_query);
$total_rows = $count_result->fetch_assoc()['total_rows'];
$total_pages = ceil($total_rows / $limit);

// Fetch pending payments for male members with search functionality and date filters
$pending_sql = "
    SELECT payments.*, members.first_name, members.last_name, members.picture 
    FROM payments 
    JOIN members ON payments.member_id = members.member_id 
    WHERE members.gender = 'male' AND payments.pending_amount > 0 
    AND (members.first_name LIKE '%$search%' OR members.last_name LIKE '%$search%')";

if (!empty($start_date)) {
    $pending_sql .= " AND payments.payment_date >= '$start_date'";
}

if (!empty($end_date)) {
    $pending_sql .= " AND payments.payment_date <= '$end_date'";
}

$pending_sql .= " ORDER BY payments.payment_date DESC 
                  LIMIT $limit OFFSET $offset";

$pending_result = $conn->query($pending_sql);

// Count total pending payments for pagination
$pending_count_query = "
    SELECT COUNT(*) as total_rows 
    FROM payments 
    JOIN members ON payments.member_id = members.member_id 
    WHERE members.gender = 'male' AND payments.pending_amount > 0 
    AND (members.first_name LIKE '%$search%' OR members.last_name LIKE '%$search%')";
$pending_count_result = $conn->query($pending_count_query);
$pending_total_rows = $pending_count_result->fetch_assoc()['total_rows'];
$pending_total_pages = ceil($pending_total_rows / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - men's Section</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        .wrapper { display: flex; width: 100%; align-items: stretch; }
        .sidebar { width: 250px; background-color: #343a40; padding: 15px; height: 100vh; position: fixed; color: white; transition: all 0.3s ease; }
        .sidebar.active { width: 0; padding: 0; overflow: hidden; }
        #content { width: 100%; padding: 20px; margin-left: 250px; transition: margin-left 0.3s ease; }
        #content.active { margin-left: 0; }
        .sidebar-header { font-size: 22px; color: white; margin-bottom: 20px; text-align: center; }
        .notification-card { margin-bottom: 15px; }
        .image-member { width: 50px; height: 50px; object-fit: cover; border-radius: 50%; }
        .alert-danger { background-color: #9b111e; border-color: #9b111e; color: #f8d7da; }
        .pagination { justify-content: center; }
        .btn-filter { background-color: #343a40; color: white; }
        .btn-filter:hover { background-color: #212529; }
        .alert-custom { background-color: #290000; color: #ffcccc; border-color: #5a1e1e; }
        .alert-custom .alert-link { color: #ff9999; text-decoration: underline; }
        .alert-custom .alert-link:hover { color: #ffb3b3; }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Page Content -->
    <div id="content">
    <button id="sidebarCollapse" class="btn btn-info"><i class="fas fa-bars"></i></button>
    <h2 class="mt-4 text-center"><i class="fas fa-bell"></i> men's Notifications</h2>

        <div class="container mt-4">
            <!-- Search and Filter Form -->
            <form method="GET" class="form-inline mb-3">
                <input type="text" name="search" class="form-control mr-2" placeholder="Search by name" value="<?php echo htmlspecialchars($search); ?>">
                <input type="date" name="start_date" class="form-control mr-2" value="<?php echo htmlspecialchars($start_date); ?>">
                <input type="date" name="end_date" class="form-control mr-2" value="<?php echo htmlspecialchars($end_date); ?>">
                <button type="submit" class="btn btn-filter"><i class="fas fa-filter"></i> Filter</button>
            </form>
<br>
            <!-- Expired Memberships Section -->
            <h4 style="text-align: center;"><i class="fas fa-exclamation-triangle"></i> Expired Memberships</h4>
            <table class="table table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>Member</th>
                        <th>Membership Type</th>
                        <th>Expiry Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($expired_result->num_rows > 0): ?>
                        <?php while ($row = $expired_result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo $row['picture']; ?>" alt="Member Image" class="image-member">
                                    <?php echo htmlspecialchars($row['first_name'] . " " . $row['last_name']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['membership_type']); ?></td>
                                <td style="color: red;"><?php echo htmlspecialchars($row['expiry_date']); ?></td>
                                <td>
                                    <a href="view_membership_men.php?id=<?php echo $row['membership_id']; ?>" class="btn btn-warning btn-sm">View</a>
                                    <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal" data-id="<?php echo $row['membership_id']; ?>" data-type="membership">Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center">No expired memberships found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pending Payments Section -->
            <h4 class="mt-4" style="text-align: center;"><i class="fas fa-credit-card"></i> Pending Payments</h4>
            <table class="table table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>Member</th>
                        <th>Amount Paid</th>
                        <th>Pending Amount</th>
                        <th>Payment Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($pending_result->num_rows > 0): ?>
                        <?php while ($row = $pending_result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo $row['picture']; ?>" alt="Member Image" class="image-member">
                                    <?php echo htmlspecialchars($row['first_name'] . " " . $row['last_name']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['amount_paid']); ?> MAD</td>
                                <td style="color: orange;"><?php echo htmlspecialchars($row['pending_amount']); ?> MAD</td>
                                <td><?php echo htmlspecialchars($row['payment_date']); ?></td>
                                <td>
                                    <a href="edit__men_payment.php?id=<?php echo $row['payment_id']; ?>" class="btn btn-warning btn-sm">Pay Now</a>
                                    <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal" data-id="<?php echo $row['payment_id']; ?>" data-type="payment">Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No pending payments found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination for Expired Memberships -->
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo htmlspecialchars($search); ?>&start_date=<?php echo htmlspecialchars($start_date); ?>&end_date=<?php echo htmlspecialchars($end_date); ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>&start_date=<?php echo htmlspecialchars($start_date); ?>&end_date=<?php echo htmlspecialchars($end_date); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo htmlspecialchars($search); ?>&start_date=<?php echo htmlspecialchars($start_date); ?>&end_date=<?php echo htmlspecialchars($end_date); ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this record?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script src="https://kit.fontawesome.com/a076d05399.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('sidebarCollapse').addEventListener('click', function () {
        document.getElementById('sidebar').classList.toggle('active');
        document.getElementById('content').classList.toggle('active');
    });

    $('#deleteModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var type = button.data('type');
        var deleteUrl = type === 'membership' ? 'delete_men_notif.php?id=' + id : 'delete_men_pay.php?id=' + id;
        $('#confirmDeleteBtn').attr('href', deleteUrl);
    });
</script>
</body>
</html>

<?php $conn->close(); ?>
