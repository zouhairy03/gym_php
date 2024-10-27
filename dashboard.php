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
$stmt = $conn->prepare("SELECT username, profile_picture FROM admin WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Determine profile picture path with a placeholder fallback if not found
$imagePath = (!empty($admin_data['profile_picture']) && file_exists($admin_data['profile_picture']))
             ? $admin_data['profile_picture']
             : 'https://via.placeholder.com/150';
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
            font-family: Arial, sans-serif;
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
            border-radius: 5px;
            margin: 5px 0;
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
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .center-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
        .btn-toggle {
            background-color: #007bff;
            border: none;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="content">
        <button class="btn-toggle mb-3" id="toggleSidebar">Toggle Sidebar</button>
        <div class="center-content">
            <h2>Hello, Admin</h2>
            <h4><?php echo htmlspecialchars($admin_data['username']); ?></h4>
            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Admin Image" class="rounded-img">
        </div>
    </div>
</div>

<script>
    // Sidebar toggle functionality
    document.getElementById("toggleSidebar").addEventListener("click", function () {
        document.querySelector(".wrapper").classList.toggle("toggled");
    });
</script>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
