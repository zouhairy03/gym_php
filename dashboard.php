<?php
// dashboard.php
session_start();
require 'config.php'; // Include the database configuration

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch admin details
$admin_id = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT username FROM admin WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
            margin: 0;
        }

        .wrapper {
            display: flex;
            width: 100%;
        }

        .sidebar {
            width: 250px;
            background: #343a40;
            padding: 15px;
            height: 100vh;
            position: fixed;
            transition: all 0.3s ease;
            left: 0;
        }

        .sidebar a {
            color: white;
            padding: 10px;
            text-decoration: none;
            display: block;
        }

        .sidebar a:hover {
            background-color: #495057;
        }

        .sidebar-header {
            font-size: 20px;
            color: white;
            margin-bottom: 20px;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
            width: 100%;
            transition: margin-left 0.3s ease;
        }

        .toggled .sidebar {
            left: -250px;
        }

        .toggled .content {
            margin-left: 0;
        }

        .rounded-img {
            border-radius: 50%;
            width: 150px;
            height: 150px;
            object-fit: cover;
        }

        .center-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Include the sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Content -->
    <div class="content">
        <button class="btn btn-secondary mb-3" id="toggleSidebar">Toggle Sidebar</button>
        <div class="center-content">
            <h2>Hello, Admin</h2>
            <h4><?php echo $admin['username']; ?></h4>
            <img src="https://via.placeholder.com/150" alt="Admin Image" class="rounded-img">
        </div>
    </div>
</div>

<script>
    document.getElementById("toggleSidebar").addEventListener("click", function () {
        document.querySelector(".wrapper").classList.toggle("toggled");
    });
</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
