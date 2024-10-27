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

// Fetch existing insurance and member details
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
$stmt->close();

// Update insurance details upon form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $insurance_start_date = $_POST['insurance_start_date'] ?? '';
    $insurance_expiry_date = $_POST['insurance_expiry_date'] ?? '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;

    // Prepare the SQL query to update the insurance record
    $update_sql = "UPDATE insurance SET insurance_start_date = ?, insurance_expiry_date = ?, price = ? WHERE insurance_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssdi", $insurance_start_date, $insurance_expiry_date, $price, $insurance_id);

    if ($update_stmt->execute()) {
        $_SESSION['success'] = "Insurance record updated successfully.";
        header("Location: insurance_women.php");
        exit();
    } else {
        $_SESSION['error'] = "Error updating insurance record: " . $update_stmt->error;
    }

    $update_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Insurance Details</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        .wrapper { display: flex; width: 100%; align-items: stretch; }
        .sidebar { width: 250px; background-color: #343a40; padding: 15px; height: 100vh; position: fixed; color: white; transition: all 0.3s ease; }
        .content { width: 100%; padding: 20px; margin-left: 250px; transition: all 0.3s ease; }
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

        <h2 class="text-center"><i class="fas fa-edit"></i> Edit Insurance Details</h2>

        <div class="card mt-4">
            <div class="card-body">
                <div class="text-center">
                    <img src="<?php echo htmlspecialchars($row['picture']); ?>" alt="Member Image" class="image-member mb-3">
                    <h4><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></h4>
                    <span class="<?php echo $row['activity_status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
                        <?php echo ucfirst($row['activity_status']); ?>
                    </span>
                </div>
                <hr>

                <!-- Edit Insurance Form -->
                <form method="POST">
                    <div class="form-group">
                        <label for="insuranceStartDate">Insurance Start Date</label>
                        <input type="date" class="form-control" id="insuranceStartDate" name="insurance_start_date" value="<?php echo htmlspecialchars($row['insurance_start_date']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="insuranceExpiryDate">Insurance Expiry Date</label>
                        <input type="date" class="form-control" id="insuranceExpiryDate" name="insurance_expiry_date" value="<?php echo htmlspecialchars($row['insurance_expiry_date']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="insurancePrice">Insurance Price (MAD)</label>
                        <input type="number" class="form-control" id="insurancePrice" name="price" value="<?php echo htmlspecialchars($row['price']); ?>" required>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="insurance_women.php" class="btn btn-secondary">Cancel</a>
                    </div>
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
