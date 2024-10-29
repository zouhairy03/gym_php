<?php
// Start session and include necessary files
session_start();
include 'config.php'; // Include database connection

// Get payment ID from URL parameter
$payment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch payment details based on payment_id
$sql = "SELECT payments.*, members.first_name, members.last_name 
        FROM payments 
        JOIN members ON payments.member_id = members.member_id 
        WHERE payments.payment_id = $payment_id AND members.gender = 'female'";
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
    <title>Edit Payment - <?php echo $payment['first_name'] . " " . $payment['last_name']; ?></title>
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

        }        .sidebar.active { left: -250px; }
        #content { width: 100%; padding: 20px; margin-left: 250px; transition: all 0.3s ease; }
        #content.active { margin-left: 0; }
        .sidebar-header { font-size: 22px; color: white; margin-bottom: 20px; text-align: center; }
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
        
        <h2 class="mt-4 text-center">Edit Payment - <?php echo $payment['first_name'] . " " . $payment['last_name']; ?></h2>

        <div class="container">
            <form action="update_payment_women.php" method="POST">
                <input type="hidden" name="payment_id" value="<?php echo $payment['payment_id']; ?>">
                <div class="form-group">
                    <label for="amountPaid">Amount Paid (MAD)</label>
                    <input type="number" class="form-control" id="amountPaid" name="amount_paid" value="<?php echo $payment['amount_paid']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="pendingAmount">Pending Amount (MAD)</label>
                    <input type="number" class="form-control" id="pendingAmount" name="pending_amount" value="<?php echo $payment['pending_amount']; ?>" >
                </div>
                <div class="form-group">
                    <label for="paymentDate">Payment Date</label>
                    <input type="date" class="form-control" id="paymentDate" name="payment_date" value="<?php echo $payment['payment_date']; ?>" required>
                </div>

                <div class="text-center mb-3">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Payment</button>
                    <a href="payments_women.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                </div>
            </form>
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
