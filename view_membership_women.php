<?php
// Start session and include necessary files
session_start();
include 'config.php'; // Include database connection

// Get membership ID from URL parameter
$membership_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch membership details based on membership_id
$sql = "SELECT memberships.*, members.first_name, members.last_name, members.phone_number, members.picture
        FROM memberships
        JOIN members ON memberships.member_id = members.member_id
        WHERE memberships.membership_id = $membership_id";
$result = $conn->query($sql);

// Check if membership exists
if ($result->num_rows == 0) {
    echo "Membership not found.";
    exit;
}

// Fetch membership details
$membership = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Membership - <?php echo $membership['first_name'] . " " . $membership['last_name']; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        .wrapper { display: flex; width: 100%; align-items: stretch; }
        .sidebar { width: 250px; background-color: #343a40; padding: 15px; height: 100vh; position: fixed; color: white; transition: all 0.3s ease; }
        .sidebar.active { left: -250px; }
        #content { width: 100%; padding: 20px; margin-left: 250px; transition: all 0.3s ease; }
        #content.active { margin-left: 0; }
        .sidebar-header { font-size: 22px; color: white; margin-bottom: 20px; text-align: center; }
        .sidebar a { color: white; padding: 10px; text-decoration: none; display: block; transition: 0.3s; }
        .sidebar a:hover { background-color: #495057; }
        .card { margin-top: 20px; }
        .image-member { width: 150px; height: 150px; object-fit: cover; border-radius: 50%; display: block; margin: 0 auto; } /* Center image */
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Page Content -->
    <div id="content">
        <button id="sidebarCollapse" class="btn btn-info">
            <i class="fas fa-align-left"></i>
        </button>
        
        <div class="container">
        <h2 class="mt-4 text-center">View Membership - <?php echo $membership['first_name'] . " " . $membership['last_name']; ?></h2>

            <div class="card">
                <div class="card-header text-center">
                    <img src="<?php echo $membership['picture']; ?>" alt="Member Image" class="image-member img-thumbnail">
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <p><strong>First Name:</strong> <?php echo $membership['first_name']; ?></p>
                        <p><strong>Last Name:</strong> <?php echo $membership['last_name']; ?></p>
                   
                    <p><strong>Phone Number:</strong> <?php echo $membership['phone_number']; ?></p>
                    <p><strong>Membership Type:</strong> <?php echo ucfirst($membership['membership_type']); ?></p>
                    <p><strong>Start Date:</strong> <?php echo $membership['start_date']; ?></p>
                    <p><strong>Expiry Date:</strong> <?php echo $membership['expiry_date']; ?></p>
                    <p><strong>Remaining Days:</strong> 

                        <?php
                            $remaining_days = (strtotime($membership['expiry_date']) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
                            if ($remaining_days <= 0) {
                                echo "<span style='color: red;'>Expired</span>";
                            } else {
                                $color = $remaining_days > 10 ? 'green' : 'orange';
                                echo "<span style='color: $color;'>" . ceil($remaining_days) . " days left</span>";
                            }
                        ?>
                    </p>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <div class="text-center mb-3">
    <a href="memberships_women.php" class="btn btn-secondary">
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
