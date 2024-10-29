<?php
// Start session and include necessary files
session_start();
include 'config.php'; // Database connection

// Check if insurance ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No insurance ID provided.";
    header("Location: insurance_women.php");
    exit();
}

$insurance_id = intval($_GET['id']);

// Fetch insurance and member details
$sql = "SELECT insurance.*, members.first_name, members.last_name, members.picture, members.activity_status
        FROM insurance
        INNER JOIN members ON insurance.member_id = members.member_id 
        WHERE insurance.insurance_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $insurance_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Insurance record not found.";
    header("Location: insurance_women.php");
    exit();
}

$row = $result->fetch_assoc();
$isExpired = strtotime($row['insurance_expiry_date']) < time();
$price_display = $isExpired ? '<span class="text-danger">Expired</span>' : number_format($row['price'], 2) . ' MAD';
$activity_class = $row['activity_status'] === 'active' ? 'status-active' : 'status-inactive';
$activity_text = ucfirst($row['activity_status']);

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Insurance Details</title>
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
        .sidebar.active { transform: translateX(-100%); } /* Hides sidebar */
        .content.active { margin-left: 0; } /* Adjusts content margin */
        .status-active { color: white; background-color: green; padding: 5px 10px; border-radius: 12px; }
        .status-inactive { color: white; background-color: red; padding: 5px 10px; border-radius: 12px; }
        .image-member { width: 100px; height: 100px; object-fit: cover; border-radius: 50%; }
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

        <h2 class="text-center"><i class="fas fa-shield-alt"></i> Insurance Details</h2>

        <div class="card mt-4">
            <div class="card-body">
                <div class="text-center">
                    <img src="<?php echo htmlspecialchars($row['picture']); ?>" alt="Member Image" class="image-member mb-3">
                    <h4><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></h4>
                    <span class="<?php echo $activity_class; ?>"><?php echo $activity_text; ?></span>
                </div>
                <hr>
                <ul class="list-group list-group-flush" style="text-align: center;">
                    <li class="list-group-item"><strong>Insurance Start Date:</strong> <?php echo htmlspecialchars($row['insurance_start_date']); ?></li>
                    <li class="list-group-item"><strong>Insurance Expiry Date:</strong> <?php echo htmlspecialchars($row['insurance_expiry_date']); ?></li>
                    <li class="list-group-item"><strong>Insurance Price:</strong> <?php echo $price_display; ?></li>

                </ul>
                <div class="mt-3 text-center">
                    <a href="insurance_women.php" class="btn btn-secondary">Back to List</a>
                </div>
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
