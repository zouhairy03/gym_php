<?php
// Start session and include necessary files
session_start();
include 'config.php'; // Include database connection

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Check if membership_id is passed
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No membership selected to edit!";
    header("Location: memberships_men.php");
    exit();
}

$membership_id = intval($_GET['id']);

// Fetch membership details
$sql = "SELECT memberships.*, members.first_name, members.last_name, members.phone_number, members.picture 
        FROM memberships 
        JOIN members ON memberships.member_id = members.member_id 
        WHERE memberships.membership_id = ? AND members.gender = 'male'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $membership_id);
$stmt->execute();
$result = $stmt->get_result();
$membership = $result->fetch_assoc();

// Check if membership exists
if (!$membership) {
    $_SESSION['error'] = "Membership not found!";
    header("Location: memberships_men.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $membership_type = $_POST['membership_type'];
    $start_date = $_POST['start_date'];
    $expiry_date = $_POST['expiry_date'];
    
    // Calculate remaining days
    $current_date = date('Y-m-d');
    $remaining_days = (strtotime($expiry_date) - strtotime($current_date)) / (60 * 60 * 24);
    
    // Update query
    $update_sql = "UPDATE memberships SET membership_type = ?, start_date = ?, expiry_date = ?, remaining_days = ? WHERE membership_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssii", $membership_type, $start_date, $expiry_date, $remaining_days, $membership_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['message'] = "Membership updated successfully!";
        header("Location: memberships_men.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to update membership!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Membership - men</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        /* Sidebar and content styling */
        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
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

        .sidebar.active {
            left: -250px;
        }

        #content {
            width: 100%;
            padding: 20px;
            margin-left: 250px;
            transition: all 0.3s ease;
        }

        #content.active {
            margin-left: 0;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .toggle-btn {
            margin-left: 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Include Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Page Content -->
    <div id="content">
        <button id="sidebarCollapse" class="btn btn-info mb-4 toggle-btn"><i class="fas fa-bars"></i> </button>

        <div class="container">
            <h2 class="content-header">Edit Membership for <?php echo $membership['first_name'] . " " . $membership['last_name']; ?></h2>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="membershipType">Membership Type</label>
                    <select class="form-control" id="membershipType" name="membership_type" required>
                        <option value="monthly" <?php echo ($membership['membership_type'] == 'monthly') ? 'selected' : ''; ?>>Monthly</option>
                        <option value="yearly" <?php echo ($membership['membership_type'] == 'yearly') ? 'selected' : ''; ?>>Yearly</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="startDate">Start Date</label>
                    <input type="date" class="form-control" id="startDate" name="start_date" value="<?php echo $membership['start_date']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="expiryDate">Expiry Date</label>
                    <input type="date" class="form-control" id="expiryDate" name="expiry_date" value="<?php echo $membership['expiry_date']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="remainingDays">Remaining Days</label>
                    <input type="text" class="form-control" id="remainingDays" value="<?php
                        $remaining_days = (strtotime($membership['expiry_date']) - strtotime(date("Y-m-d"))) / (60 * 60 * 24);
                        if ($remaining_days > 0) {
                            echo $remaining_days . ' days';
                        } else {
                            echo 'Expired';
                        }
                    ?>" readonly>
                </div>

                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Update Membership</button>
                <a href="memberships_men.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Cancel</a>
            </form>
        </div>
    </div>
</div>

<!-- Include jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<script>
    // Toggle the sidebar
    $('#sidebarCollapse').on('click', function () {
        $('.sidebar').toggleClass('active');
        $('#content').toggleClass('active');
    });
</script>

</body>
</html>
