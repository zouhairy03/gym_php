<?php
// members_men.php
session_start();
require 'config.php'; // Include the database configuration

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Pagination setup
$limit = 5; // Number of entries per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Get search and filter parameters
$searchQuery = isset($_GET['searchQuery']) ? $_GET['searchQuery'] : '';
$filterStatus = isset($_GET['filterSelect']) ? $_GET['filterSelect'] : '';

// Base query to fetch members ordered by member_id in descending order
$query = "SELECT member_id, first_name, last_name, gender, picture, phone_number, CNE, activity_status, insurance_status, membership_status, created_at 
          FROM members WHERE gender = 'male'";

// Add search condition if search query is provided
if (!empty($searchQuery)) {
    $query .= " AND (first_name LIKE ? OR last_name LIKE ?)";
    $searchParam = '%' . $searchQuery . '%';
}

// Add filter condition for activity status if selected
if (!empty($filterStatus)) {
    $query .= " AND activity_status = ?";
}

// Add order by member_id descending and pagination
$query .= " ORDER BY member_id DESC LIMIT ?, ?";

// Prepare statement
$stmt = $conn->prepare($query);

// Bind parameters based on provided search and filter
if (!empty($searchQuery) && !empty($filterStatus)) {
    $stmt->bind_param("sssii", $searchParam, $searchParam, $filterStatus, $offset, $limit);
} elseif (!empty($searchQuery)) {
    $stmt->bind_param("ssii", $searchParam, $searchParam, $offset, $limit);
} elseif (!empty($filterStatus)) {
    $stmt->bind_param("sii", $filterStatus, $offset, $limit);
} else {
    $stmt->bind_param("ii", $offset, $limit);
}

// Execute query
$stmt->execute();
$result = $stmt->get_result();

// Count total records for pagination
$totalQuery = "SELECT COUNT(*) as total FROM members WHERE gender = 'male'";

// Add search condition to count query
if (!empty($searchQuery)) {
    $totalQuery .= " AND (first_name LIKE ? OR last_name LIKE ?)";
}

// Add filter condition to count query
if (!empty($filterStatus)) {
    $totalQuery .= " AND activity_status = ?";
}

// Prepare count statement
$countStmt = $conn->prepare($totalQuery); // This should be initialized here

// Bind parameters for the count query
if (!empty($searchQuery) && !empty($filterStatus)) {
    $countStmt->bind_param("sss", $searchParam, $searchParam, $filterStatus);
} elseif (!empty($searchQuery)) {
    $countStmt->bind_param("ss", $searchParam, $searchParam);
} elseif (!empty($filterStatus)) {
    $countStmt->bind_param("s", $filterStatus);
}

// Execute count query and calculate total pages
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRecords = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $limit);

// Close statements
$stmt->close();
$countStmt->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members - men</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            margin: 0;
            display: flex;
            /* background-color: #f8f9fa; */
        }

        .wrapper {
            display: flex;
            width: 100%;
        }

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

        }

        .sidebar a {
            color: white;
            padding: 10px;
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
        }

        .sidebar a:hover {
            background-color: #495057;
        }

        .sidebar .sidebar-header {
            font-size: 20px;
            color: white;
            margin-bottom: 20px;
        }

        .sidebar .dropdown-btn {
            cursor: pointer;
            color: white;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            padding: 10px;
        }

        .sidebar .dropdown-btn:hover {
            background-color: #495057;
        }

        .dropdown-container {
            display: none;
            padding-left: 15px;
        }

        .dropdown-container a {
            padding: 10px;
            display: block;
            color: white;
        }

        .dropdown-container a:hover {
            background-color: #495057;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
            width: 100%;
            transition: all 0.3s;
        }

        .toggled .sidebar {
            left: -250px;
        }

        .toggled .content {
            margin-left: 0;
        }

        .rounded-img {
            border-radius: 50%;
            width: 50px;
            height: 50px;
            object-fit: cover;
        }

        .search-bar {
            margin-bottom: 20px;
        }

        .table-responsive {
            margin-top: 20px;
        }

        .actions .btn-group {
            margin-top: 10px;
        }

        .pagination {
            justify-content: center;
        }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    <!-- Content -->
    <div class="content">
    <button id="toggleSidebar" class="btn btn-info">
    <i class="fas fa-bars"></i>
</button>

<h2 class="my-4" style="text-align: center;background: lightblue;color: white;">
    <i class="fas fa-male" style="margin-right: 8px;"></i> Men Members
</h2>
        
        <!-- Add New Member Button -->
        <button type="button" class="btn btn-success mb-3" data-toggle="modal" data-target="#addMemberModal">
            <i class="fas fa-plus"></i> Add New Member
        </button>

        <!-- Search and Filter -->
        <form method="GET" action="members_men.php">
            <div class="row search-bar">
                <div class="col-md-6">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" id="searchInput" name="searchQuery" class="form-control" placeholder="Search by Name" value="<?php echo htmlspecialchars($searchQuery); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-filter"></i></span>
                        </div>
                        <select id="filterSelect" name="filterSelect" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="active" <?php if ($filterStatus === 'active') echo 'selected'; ?>>Active</option>
                            <option value="inactive" <?php if ($filterStatus === 'inactive') echo 'selected'; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success"><i class="fas fa-filter"></i> Apply</button>
                </div>
            </div>
        </form>
        <div class="col-md-2">
            <a href="export_men_excel.php" class="btn btn-success"><i class="fas fa-file-excel"></i> Export to Excel</a>
            </div>
        <!-- Table to display members -->
        <div class="table-responsive">
            <table class="table table-hover ">
                <thead>
                    <tr>
                        <th>Picture</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Phone</th>
                        <th>CNE</th>
                        <th>Activity Status</th>
                        <th>Insurance Status</th>
                        <th>Membership Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="membersTable">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><img src="<?php echo $row['picture']; ?>" alt="Member Picture" class="rounded-img"></td>
                            <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['CNE']); ?></td>
                            <td>
                                <?php if ($row['activity_status'] === 'active'): ?>
                                    <span style="color: green;"><?php echo htmlspecialchars($row['activity_status']); ?></span>
                                <?php else: ?>
                                    <span style="color: red;"><?php echo htmlspecialchars($row['activity_status']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['insurance_status']); ?></td>
                            <td><?php echo htmlspecialchars($row['membership_status']); ?></td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                            <td>
                                <div class="btn-group actions">
                                    <a href="edit_men.php?id=<?php echo $row['member_id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="view_men.php?id=<?php echo $row['member_id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> View</a>
                                    <a href="delete_men.php?id=<?php echo $row['member_id']; ?>" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center">No men members found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav>
            <ul class="pagination">
                <li class="page-item <?php if($page <= 1){ echo 'disabled'; } ?>">
                    <a class="page-link" href="<?php if($page > 1){ echo "?page=" . ($page - 1); } ?>">Previous</a>
                </li>
                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php if($page == $i){ echo 'active'; } ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&searchQuery=<?php echo $searchQuery; ?>&filterSelect=<?php echo $filterStatus; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?php if($page >= $totalPages){ echo 'disabled'; } ?>">
                    <a class="page-link" href="<?php if($page < $totalPages) { echo "?page=" . ($page + 1); } ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
</div>


<!-- Modal for Adding a New Member -->
<div class="modal fade" id="addMemberModal" tabindex="-1" role="dialog" aria-labelledby="addMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMemberModalLabel">Add New Member</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="add_member_men.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" class="form-control" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" class="form-control" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label for="phone_number">Phone Number (Optional)</label>
                        <input type="text" class="form-control" name="phone_number">
                    </div>
                    <div class="form-group">
                        <label for="CNE">CNE (Optional)</label>
                        <input type="text" class="form-control" name="CNE">
                    </div>
                    <div class="form-group">
                        <label for="picture">Picture</label>
                        <input type="file" class="form-control" name="picture" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label for="activity_status">Activity Status</label>
                        <select class="form-control" name="activity_status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="membership_status">Membership Status</label>
                        <select class="form-control" name="membership_status" required>
                            <option value="valid">Valid</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="insurance_status">Insurance Status</label>
                        <select class="form-control" name="insurance_status" required>
                            <option value="valid">Valid</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="created_at">Creation Date</label>
                        <input type="datetime-local" class="form-control" name="created_at" required>
                    </div>
                    <!-- Hidden field for gender, automatically set to male -->
                    <input type="hidden" name="gender" value="male">
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Add Member</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    // Sidebar toggle functionality
    document.getElementById("toggleSidebar").addEventListener("click", function () {
        document.querySelector(".wrapper").classList.toggle("toggled");
    });

    // Sidebar dropdown functionality
    const dropdownBtn = document.querySelector('.dropdown-btn');
    const dropdownContainer = document.querySelector('.dropdown-container');

    dropdownBtn.addEventListener('click', function () {
        dropdownContainer.style.display = dropdownContainer.style.display === 'block' ? 'none' : 'block';
    });

    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function () {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('#membersTable tbody tr');

        let noResults = true; // Track if no rows match the search query

        rows.forEach(row => {
            const firstName = row.cells[1].textContent.toLowerCase();
            const lastName = row.cells[2].textContent.toLowerCase();

            if (firstName.includes(searchValue) || lastName.includes(searchValue)) {
                row.style.display = ''; // Show the row if it matches
                noResults = false; // A matching row is found
            } else {
                row.style.display = 'none'; // Hide the row if it doesn't match
            }
        });

        // Display "No members found" message if no matching rows are found
        document.getElementById('noResultsMessage').style.display = noResults ? 'block' : 'none';
    });

    // Filter functionality based on activity status
    document.getElementById('filterSelect').addEventListener('change', function () {
        const filterValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('#membersTable tbody tr');

        let noResults = true; // Track if no rows match the filter

        rows.forEach(row => {
            const activityStatus = row.cells[5].textContent.toLowerCase(); // Activity status is in the 6th column (adjust based on actual position)

            if (filterValue === '' || activityStatus === filterValue) {
                row.style.display = ''; // Show the row if it matches the filter
                noResults = false; // A matching row is found
            } else {
                row.style.display = 'none'; // Hide the row if it doesn't match
            }
        });

        // Display "No members found" message if no matching rows are found
        document.getElementById('noResultsMessage').style.display = noResults ? 'block' : 'none';
    });

    // Export to Excel functionality
    document.getElementById('exportExcel').addEventListener('click', function () {
        window.location.href = 'export_men_excel.php'; // Trigger export by navigating to the export script
    });
</script>

<!-- jQuery and Bootstrap Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

