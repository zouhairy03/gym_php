<?php
// Start session and include necessary files
session_start();
include 'config.php'; // Database connection

// Variables for search, filter, and pagination
$search_first_name = isset($_GET['search_first_name']) ? $_GET['search_first_name'] : '';
$search_last_name = isset($_GET['search_last_name']) ? $_GET['search_last_name'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$limit = 5; // Number of records per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Base query for filtering women with insurance
$where_clause = "WHERE members.gender = 'female'";

// Search functionality
if (!empty($search_first_name)) {
    $where_clause .= " AND members.first_name LIKE '%" . $conn->real_escape_string($search_first_name) . "%'";
}
if (!empty($search_last_name)) {
    $where_clause .= " AND members.last_name LIKE '%" . $conn->real_escape_string($search_last_name) . "%'";
}

// Insurance date filter
if (!empty($start_date)) {
    $where_clause .= " AND insurance.insurance_start_date >= '" . $conn->real_escape_string($start_date) . "'";
}
if (!empty($end_date)) {
    $where_clause .= " AND insurance.insurance_expiry_date <= '" . $conn->real_escape_string($end_date) . "'";
}

// Query for fetching women's insurance records
$sql = "SELECT insurance.*, members.first_name, members.last_name, members.picture, members.activity_status
        FROM insurance
        INNER JOIN members ON insurance.member_id = members.member_id 
        $where_clause 
        ORDER BY insurance.insurance_id DESC 
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// Count total rows for pagination
$count_query = "SELECT COUNT(*) as total_rows 
                FROM insurance 
                JOIN members ON insurance.member_id = members.member_id 
                $where_clause";
$count_result = $conn->query($count_query);
$total_rows = $count_result->fetch_assoc()['total_rows'];
$total_pages = ceil($total_rows / $limit);

// Handle Excel export
if (isset($_GET['export_excel'])) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="women_insurance.xlsx"');
    header('Cache-Control: max-age=0');

    echo "Insurance ID\tFirst Name\tLast Name\tActivity Status\tStart Date\tExpiry Date\tPrice (MAD)\n";
    while ($row = $result->fetch_assoc()) {
        $activity = $row['activity_status'] == 'active' ? 'Active' : 'Inactive';
        $price_display = (strtotime($row['insurance_expiry_date']) < time()) ? 'Expired' : number_format($row['price'], 2);
        echo $row['insurance_id'] . "\t" . $row['first_name'] . "\t" . $row['last_name'] . "\t" . $activity . "\t" . $row['insurance_start_date'] . "\t" . $row['insurance_expiry_date'] . "\t" . $price_display . "\n";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Women's Insurance</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        .wrapper { display: flex; width: 100%; align-items: stretch; }
        .sidebar {
            width: 250px;
            height: 100vh;
            background-color:white;
            position: fixed;
            left: 0;
            top: 0;
            overflow-x: hidden;
            transition: all 0.3s ease;
            z-index: 100;

        }        .content { width: 100%; padding: 20px; margin-left: 250px; transition: all 0.3s ease; }
        .image-member { width: 50px; height: 50px; object-fit: cover; border-radius: 50%; }
        .pagination { justify-content: center; }
        .sidebar.active { transform: translateX(-100%); } /* Hides sidebar */
        .content.active { margin-left: 0; } /* Adjusts content margin */
        .status-active { color: white; background-color: green; padding: 5px 10px; border-radius: 12px; }
        .status-inactive { color: white; background-color: red; padding: 5px 10px; border-radius: 12px; }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <?php include 'sidebar.php'; ?>
    </div>

    <!-- Page Content -->
    <div class="content" id="content">
        <!-- Sidebar Toggle Button -->
        <button id="sidebarCollapse" class="btn btn-info">
            <i class="fas fa-bars"></i>
        </button>
        <h2 class="mt-4 text-center"><i class="fas fa-shield-alt"></i> Women's Insurance</h2>

        <!-- Add Insurance Button to Trigger Modal -->
        <button type="button" class="btn btn-info mb-3" data-toggle="modal" data-target="#addInsuranceModal">
            <i class="fas fa-plus"></i> Add Insurance
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
                <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-filter"></i> Filter</button>
                <button type="submit" name="export_excel" style="margin-top: 10px;" class="btn btn-success"><i class="fas fa-file-excel"></i> Export</button>
            </form>
        </div>

        <!-- Insurance Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="thead">
                    <tr>
                        <th>Picture</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Activity Status</th>
                        <th>Start Date</th>
                        <th>Expiry Date</th>
                        <th>Price (MAD)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php
                                // Check if the insurance is expired
                                $isExpired = strtotime($row['insurance_expiry_date']) < time();
                                $price_display = $isExpired ? '<span class="text-danger">Expired</span>' : number_format($row['price'], 2) . ' MAD';

                                // Activity status styling
                                $activity_class = $row['activity_status'] == 'active' ? 'status-active' : 'status-inactive';
                                $activity_text = ucfirst($row['activity_status']);
                            ?>
                            <tr>
                                <td><img src="<?php echo $row['picture']; ?>" alt="Member Image" class="image-member"></td>
                                <td><?php echo $row['first_name']; ?></td>
                                <td><?php echo $row['last_name']; ?></td>
                                <td><span class="<?php echo $activity_class; ?>"><?php echo $activity_text; ?></span></td>
                                <td><?php echo $row['insurance_start_date']; ?></td>
                                <td><?php echo $row['insurance_expiry_date']; ?></td>
                                <td><?php echo $price_display; ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="view_women_insurance.php?id=<?php echo $row['insurance_id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                                        <a href="edit_women_insurance.php?id=<?php echo $row['insurance_id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                        <a href="delete_women_insurance.php?id=<?php echo $row['insurance_id']; ?>" class="btn btn-danger btn-sm" ><i class="fas fa-trash-alt"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No insurance records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>&search_first_name=<?php echo htmlspecialchars($search_first_name); ?>&search_last_name=<?php echo htmlspecialchars($search_last_name); ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>">&laquo;</a></li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($page == $i) echo 'active'; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&search_first_name=<?php echo htmlspecialchars($search_first_name); ?>&search_last_name=<?php echo htmlspecialchars($search_last_name); ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>&search_first_name=<?php echo htmlspecialchars($search_first_name); ?>&search_last_name=<?php echo htmlspecialchars($search_last_name); ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>">&raquo;</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</div>

<!-- Add Insurance Modal -->
<div class="modal fade" id="addInsuranceModal" tabindex="-1" aria-labelledby="addInsuranceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="add_insurance_women.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="addInsuranceModalLabel">Add New Insurance</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="memberSelect">Select Member</label>
                        <select class="form-control" id="memberSelect" name="member_id" required>
                            <option value="">-- Select Member --</option>
                            <?php
                            $member_sql = "SELECT member_id, first_name, last_name FROM members WHERE gender = 'female' AND activity_status = 'active'";
                            $member_result = $conn->query($member_sql);
                            if ($member_result->num_rows > 0) {
                                while ($member = $member_result->fetch_assoc()) {
                                    echo "<option value='" . $member['member_id'] . "'>" . $member['first_name'] . " " . $member['last_name'] . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="insuranceStartDate">Insurance Start Date</label>
                        <input type="date" class="form-control" id="insuranceStartDate" name="insurance_start_date" required>
                    </div>
                    <div class="form-group">
                        <label for="insuranceExpiryDate">Insurance Expiry Date</label>
                        <input type="date" class="form-control" id="insuranceExpiryDate" name="insurance_expiry_date" required>
                    </div>
                    <div class="form-group">
                        <label for="insurancePrice">Insurance Price (MAD)</label>
                        <input type="number" class="form-control" id="insurancePrice" name="price" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Insurance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('sidebarCollapse').addEventListener('click', function () {
        document.getElementById('sidebar').classList.toggle('active');
        document.getElementById('content').classList.toggle('active');
    });
</script>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
