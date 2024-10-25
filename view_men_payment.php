<?php
// Start session and include necessary files
session_start();
include 'config.php'; // Include database connection

// Get payment ID from URL parameter
$payment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch payment details based on payment_id
$sql = "SELECT payments.*, members.first_name, members.last_name, members.picture 
        FROM payments 
        JOIN members ON payments.member_id = members.member_id 
        WHERE payments.payment_id = $payment_id AND members.gender = 'male'";
$result = $conn->query($sql);

// Check if payment exists
if ($result->num_rows == 0) {
    echo "<div class='text-center mt-5 text-danger'>Payment not found.</div>";
    exit;
}

// Fetch payment details
$payment = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Payment - <?php echo $payment['first_name'] . " " . $payment['last_name']; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        .wrapper { display: flex; width: 100%; align-items: stretch; }
        .sidebar { width: 250px; background-color: #343a40; padding: 15px; height: 100vh; position: fixed; color: white; transition: all 0.3s ease; }
        .sidebar.active { left: -250px; }
        #content { width: 100%; padding: 20px; margin-left: 250px; transition: all 0.3s ease; }
        #content.active { margin-left: 0; }
        .sidebar-header { font-size: 22px; color: white; margin-bottom: 20px; text-align: center; }
        .image-member { width: 150px; height: 150px; object-fit: cover; border-radius: 50%; display: block; margin: 0 auto; } /* Center image */
        .card { margin-top: 20px; }
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
            <h2 class="mt-4 text-center"><i class="fas fa-credit-card"></i> View Payment - <?php echo $payment['first_name'] . " " . $payment['last_name']; ?></h2>

            <div class="card">
                <div class="card-header text-center">
                    <img src="<?php echo $payment['picture']; ?>" alt="Member Image" class="image-member img-thumbnail">
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <p><strong>First Name:</strong> <?php echo $payment['first_name']; ?></p>
                        <p><strong>Last Name:</strong> <?php echo $payment['last_name']; ?></p>
                        <p><strong>Payment ID:</strong> <?php echo $payment['payment_id']; ?></p>
                        <p><strong>Amount Paid:</strong> MAD <?php echo number_format($payment['amount_paid'], 2); ?></p>
                        <p><strong>Pending Amount:</strong> MAD <?php echo number_format($payment['pending_amount'], 2); ?></p>
                        <p><strong>Payment Date:</strong> <?php echo $payment['payment_date']; ?></p>
                        <p><strong>Payment Status:</strong> 
                            <?php 
                            echo ($payment['pending_amount'] == 0) ? "<span style='color: green;'>Completed</span>" : "<span style='color: orange;'>Pending</span>"; 
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mb-3">
            <a href="payments_men.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
    $('#sidebarCollapse').on('click', function () { 
        $('.sidebar').toggleClass('active'); 
        $('#content').toggleClass('active'); 
    });
</script>
</body>
</html>

<?php $conn->close(); ?>
