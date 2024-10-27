<?php
// Start session and include necessary files
session_start();
include 'config.php'; // Database connection

// Check if insurance ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No insurance ID provided.";
    header("Location: insurance_men.php");
    exit();
}

$insurance_id = intval($_GET['id']);

// Fetch insurance record to confirm existence
$sql = "SELECT insurance.*, members.first_name, members.last_name
        FROM insurance
        INNER JOIN members ON insurance.member_id = members.member_id 
        WHERE insurance.insurance_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $insurance_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Insurance record not found.";
    header("Location: insurance_men.php");
    exit();
}

$row = $result->fetch_assoc();
$stmt->close();

// Handle deletion upon confirmation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
    $delete_sql = "DELETE FROM insurance WHERE insurance_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $insurance_id);

    if ($delete_stmt->execute()) {
        $_SESSION['success'] = "Insurance record deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting insurance record.";
    }

    $delete_stmt->close();
    header("Location: insurance_men.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Delete Insurance</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        .wrapper { display: flex; width: 100%; align-items: stretch; }
        .sidebar { width: 250px; background-color: #343a40; padding: 15px; height: 100vh; position: fixed; color: white; transition: all 0.3s ease; }
        .content { width: 100%; padding: 20px; margin-left: 250px; transition: all 0.3s ease; }
        .sidebar.active { transform: translateX(-100%); }
        .content.active { margin-left: 0; }
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
        <button id="sidebarCollapse" class="btn btn-info mb-4">
            <i class="fas fa-bars"></i>
        </button>

        <h2 class="text-center"><i class="fas fa-exclamation-triangle"></i> Confirm Delete Insurance</h2>

        <div class="card mt-4">
            <div class="card-body">
                <p class="text-center">Are you sure you want to delete the insurance record for:</p>
                <div class="text-center">
                    <h4><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></h4>
                    <p>Insurance ID: <?php echo htmlspecialchars($row['insurance_id']); ?></p>
                </div>
                <form method="POST" class="text-center">
                    <button type="submit" name="confirm_delete" class="btn btn-danger">Yes, Delete</button>
                    <a href="insurance_men.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
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
