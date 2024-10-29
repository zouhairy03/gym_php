<?php
// Start session and include necessary files
session_start();
include 'config.php'; // Include database connection

// Variables for search, filter, and pagination
$search_first_name = isset($_GET['search_first_name']) ? trim($_GET['search_first_name']) : '';
$search_last_name = isset($_GET['search_last_name']) ? trim($_GET['search_last_name']) : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$limit = 5; // Number of records per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Base query for filtering female members
$where_clause = "WHERE members.gender = 'female'";

// Search functionality for first name and last name
if (!empty($search_first_name)) {
    $where_clause .= " AND members.first_name LIKE '%$search_first_name%'";
}
if (!empty($search_last_name)) {
    $where_clause .= " AND members.last_name LIKE '%$search_last_name%'";
}

// Filter functionality
if (!empty($filter)) {
    $where_clause .= " AND memberships.membership_type = '$filter'";
}

if (!empty($start_date)) {
    $where_clause .= " AND memberships.start_date >= '$start_date'";
}

if (!empty($end_date)) {
    $where_clause .= " AND memberships.expiry_date <= '$end_date'";
}

// Count total rows for pagination (only for filtered/searched results)
$count_query = "SELECT COUNT(*) as total_rows 
                FROM memberships 
                JOIN members ON memberships.member_id = members.member_id 
                $where_clause";
$count_result = $conn->query($count_query);
$total_rows = $count_result->fetch_assoc()['total_rows'];
$total_pages = ceil($total_rows / $limit);

// Prepare SQL query based on filter, search, and pagination criteria
$sql = "SELECT memberships.*, members.first_name, members.last_name, members.phone_number, members.picture
        FROM memberships 
        JOIN members ON memberships.member_id = members.member_id 
        $where_clause 
        ORDER BY memberships.membership_id DESC 
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// Check if there are results
$no_records_message = ($result->num_rows == 0) ? "No memberships found for the selected criteria." : "";

// Handle Excel export
if (isset($_GET['export_excel'])) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="female_memberships.xlsx"');
    header('Cache-Control: max-age=0');

    echo "First Name\tLast Name\tPhone Number\tMembership Type\tStart Date\tExpiry Date\tRemaining Days\n";
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo $row['first_name'] . "\t" . $row['last_name'] . "\t" . $row['phone_number'] . "\t" . $row['membership_type'] . "\t" . $row['start_date'] . "\t" . $row['expiry_date'] . "\t" . ($row['remaining_days'] > 0 ? $row['remaining_days'] . " days" : "Expired") . "\n";
        }
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memberships - Women</title>
    <!-- Include Bootstrap CSS and FontAwesome for icons -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        /* Sidebar and main content styling */
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

        }        .sidebar.active { left: -250px; }
        #content { width: 100%; padding: 20px; margin-left: 250px; transition: all 0.3s ease; }
        #content.active { margin-left: 0; }
        .actions, .filter-section, .pagination { justify-content: center; }
        .image-member { width: 50px; height: 50px; object-fit: cover; border-radius: 50%; }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Page Content -->
    <div id="content">
    <button id="sidebarCollapse" class="btn btn-info">
    <i class="fas fa-bars"></i>
</button>
        <div class="container">
        <h2 class="mt-4 text-center" style="background: pink;color: white;" >
    <i class="fas fa-female"></i> Women Memberships
</h2>

            <button type="button" class="btn btn-info mb-3" data-toggle="modal" data-target="#addMembershipModal">
    <i class="fas fa-plus"></i> Add New Membership
</button>
<div class="actions d-flex justify-content-between mb-3">
    <!-- Search Form -->
    <form method="GET" class="form-inline d-flex align-items-center">
        <input type="text" name="search_first_name" class="form-control mr-2" placeholder="Search by first name" value="<?php echo htmlspecialchars($search_first_name); ?>">
        <input type="text" name="search_last_name" class="form-control mr-2" placeholder="Search by last name" value="<?php echo htmlspecialchars($search_last_name); ?>">
        <button type="submit" class="btn btn-primary d-flex align-items-center">
            <i class="fas fa-search mr-1"></i> Search
        </button>
    </form>

    <!-- Filter and Export Form -->
    <form method="GET" class="form-inline d-flex align-items-center">
        <select name="filter" id="filter" class="form-control mr-2">
            <option value="">All</option>
            <option value="monthly" <?php echo ($filter == 'monthly') ? 'selected' : ''; ?>>Monthly</option>
            <option value="yearly" <?php echo ($filter == 'yearly') ? 'selected' : ''; ?>>Yearly</option>
        </select>
        <input type="date" name="start_date" class="form-control mr-2" value="<?php echo htmlspecialchars($start_date); ?>">
        <input type="date" name="end_date" class="form-control mr-2" value="<?php echo htmlspecialchars($end_date); ?>">
        <button type="submit" class="btn btn-primary d-flex align-items-center mr-2">
            <i class="fas fa-filter mr-1"></i> Filter
        </button>
        <button type="submit" name="export_excel" style="margin-top: 10px;" class="btn btn-success d-flex align-items-center">
            <i class="fas fa-file-excel mr-1"></i> Export
        </button>
    </form>
</div>


            <table class="table mt-3">
                <thead>
                    <tr>
                        <th>Image</th><th>First Name</th><th>Last Name</th><th>Phone Number</th>
                        <th>Membership Type</th><th>Start Date</th><th>Expiry Date</th><th>Remaining Days</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($no_records_message)): ?>
                        <tr><td colspan="9" class="text-center"><?php echo $no_records_message; ?></td></tr>
                    <?php else: ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><img src="<?php echo $row['picture']; ?>" class="image-member"></td>
                                <td><?php echo $row['first_name']; ?></td>
                                <td><?php echo $row['last_name']; ?></td>
                                <td><?php echo $row['phone_number']; ?></td>
                                <td><?php echo ucfirst($row['membership_type']); ?></td>
                                <td><?php echo $row['start_date']; ?></td>
                                <td><?php echo $row['expiry_date']; ?></td>
                                <td>
                                    <?php
                                        $remaining_days = (strtotime($row['expiry_date']) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
                                        if ($remaining_days <= 0) {
                                            echo "<span style='color: red;'>Expired</span>";
                                        } else {
                                            $color = $remaining_days > 10 ? 'green' : 'orange';
                                            echo "<span style='color: $color;'>" . ceil($remaining_days) . " days left</span>";
                                        }
                                    ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="edit_membership_women.php?id=<?php echo $row['membership_id']; ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                        <a href="view_membership_women.php?id=<?php echo $row['membership_id']; ?>" class="btn btn-info"><i class="fas fa-eye"></i> View</a>
                                        <a href="delete_membership_women.php?id=<?php echo $row['membership_id']; ?>" class="btn btn-danger" ><i class="fas fa-trash"></i> Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <!-- Pagination showing only filtered or searched results -->
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search_first_name=<?php echo htmlspecialchars($search_first_name); ?>&search_last_name=<?php echo htmlspecialchars($search_last_name); ?>&filter=<?php echo $filter; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search_first_name=<?php echo htmlspecialchars($search_first_name); ?>&search_last_name=<?php echo htmlspecialchars($search_last_name); ?>&filter=<?php echo $filter; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search_first_name=<?php echo htmlspecialchars($search_first_name); ?>&search_last_name=<?php echo htmlspecialchars($search_last_name); ?>&filter=<?php echo $filter; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>
<!-- Add Membership Modal -->
<div class="modal fade" id="addMembershipModal" tabindex="-1" aria-labelledby="addMembershipModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addMembershipForm" action="add_membership_women.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMembershipModalLabel">Add New Membership</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Select Member -->
                    <div class="form-group">
                        <label for="memberSelect">Select Active Member</label>
                        <select class="form-control" id="memberSelect" name="member_id" required>
                            <option value="">-- Select Member --</option>
                            <!-- Populate with active female members from database -->
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

                    <!-- Membership Type -->
                    <div class="form-group">
                        <label for="membershipType">Membership Type</label>
                        <select class="form-control" id="membershipType" name="membership_type" required>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>

                    <!-- Start Date -->
                    <div class="form-group">
                        <label for="startDate">Start Date</label>
                        <input type="date" class="form-control" id="startDate" name="start_date" required>
                    </div>

                    <!-- Expiry Date -->
                    <div class="form-group">
                        <label for="expiryDate">End Date</label>
                        <input type="date" class="form-control" id="expiryDate" name="expiry_date" required>
                    </div>

                    <!-- Remaining Days -->
                    <div class="form-group">
                        <label for="remainingDays">Remaining Days</label>
                        <input type="text" class="form-control" id="remainingDays" name="remaining_days" readonly>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Membership</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
    $('#sidebarCollapse').on('click', function () { $('.sidebar').toggleClass('active'); $('#content').toggleClass('active'); });
    $(document).ready(function() {
        $('#memberSelect').change(function() {
            var member_id = $(this).val();
            if (member_id != '') {
                $.ajax({
                    url: 'get_member_details_women.php', method: 'POST',
                    data: { member_id: member_id }, dataType: 'json',
                    success: function(response) {
                        $('#firstName').val(response.first_name);
                        $('#lastName').val(response.last_name);
                        $('#phoneNumber').val(response.phone_number);
                        $('#memberImage').attr('src', response.picture);
                    }
                });
            } else {
                $('#firstName, #lastName, #phoneNumber').val('');
                $('#memberImage').attr('src', 'uploads/default.png');
            }
        });
       // Calculate remaining days based on start date and expiry date
$('#startDate, #expiryDate').change(function() {
    var startDateVal = $('#startDate').val();
    var expiryDateVal = $('#expiryDate').val();

    if (startDateVal && expiryDateVal) {
        var startDate = new Date(startDateVal);
        var expiryDate = new Date(expiryDateVal);

        // Ensure expiry date is after start date
        if (expiryDate >= startDate) {
            var timeDiff = expiryDate.getTime() - startDate.getTime();
            var daysDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24)); // Convert milliseconds to days
            $('#remainingDays').val(daysDiff);
        } else {
            $('#remainingDays').val(0); // Set to 0 if expiry date is before start date
        }
    } else {
        $('#remainingDays').val('');
    }
});

    });
</script>
</body>
</html>
<?php $conn->close(); ?>