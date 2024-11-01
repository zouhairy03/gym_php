<?php
session_start();
include 'config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Fetch admin details
$admin_query = "SELECT * FROM admin WHERE admin_id = ?";
$stmt = $conn->prepare($admin_query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $profile_picture_path = $admin_data['profile_picture'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if new password and confirm password match
    if (!empty($new_password) && $new_password !== $confirm_password) {
        $_SESSION['error'] = "The new password and confirmation password do not match.";
        header("Location: profile.php");
        exit();
    }

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "uploads/members/admin/";
        $file_name = basename($_FILES['profile_picture']['name']);
        $target_file = $target_dir . $admin_id . "_" . $file_name;

        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $profile_picture_path = $target_file;
        } else {
            $_SESSION['error'] = "Error uploading image.";
        }
    }

    $update_query = "UPDATE admin SET username = ?, profile_picture = ? WHERE admin_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssi", $username, $profile_picture_path, $admin_id);
    $stmt->execute();
    $stmt->close();

    if (!empty($new_password)) {
        $update_password_query = "UPDATE admin SET password = ? WHERE admin_id = ?";
        $stmt = $conn->prepare($update_password_query);
        $stmt->bind_param("si", $new_password, $admin_id); // No hashing
        $stmt->execute();
        $stmt->close();
    }

    $_SESSION['success'] = "Profile updated successfully.";
    header("Location: profile.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Profile</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body { font-family: 'Arial', sans-serif; overflow-x: hidden; }
        .sidebar { width: 250px; height: 100vh; position: fixed; background-color: #343a40; transition: 0.5s; left: 0; }
        .sidebar.active { width: 0; }
        .sidebar ul { padding: 0; list-style: none; }
        .sidebar ul li { padding: 15px; }
        .sidebar ul li a { color: #fff; display: block; text-decoration: none; }
        .sidebar ul li a:hover { background-color: #007bff; transition: 0.3s; }
        .content { margin-left: 250px; transition: margin-left 0.5s; }
        .content.active { margin-left: 0; }
        .profile-container { max-width: 700px; margin: 50px auto; animation: fadeIn 0.6s ease; }
        .card { box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); border-radius: 8px; }
        .profile-image { width: 150px; height: 150px; object-fit: cover; border-radius: 50%; }
        .icon-container { font-size: 1.1em; color: #343a40; }
        .eye-icon { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #666; cursor: pointer; }
        .form-control:focus { box-shadow: 0px 0px 8px rgba(0, 123, 255, 0.5); border-color: #80bdff; transition: 0.3s; }
        h3 { font-family: 'Helvetica Neue', sans-serif; color: black; }
        .btn-primary { background-color: #007bff; border: none; }
        .btn-secondary { background-color: #6c757d; border: none; }
        .btn-group { display: flex; gap: 10px; }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<?php include 'sidebar.php'; ?>

<!-- Content -->
<div class="content" id="content">
    <button id="sidebarCollapse" class="btn btn-info mt-3 ml-3"><i class="fas fa-bars"></i></button>

    <div class="profile-container">
        <div class="card">
            <div class="card-header text-center">
                <h3><i class="fas fa-user-circle"></i> Admin Profile</h3>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <div class="text-center mb-4">
                    <img src="<?php echo $admin_data['profile_picture'] ?? 'default_profile.jpg'; ?>" alt="Profile Picture" class="profile-image mb-2">
                    <h5><?php echo htmlspecialchars($admin_data['username']); ?></h5>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <!-- Username -->
                    <div class="form-group">
                        <label><i class="fas fa-user icon-container"></i> Username</label>
                        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($admin_data['username']); ?>" required>
                    </div>

                    <!-- Profile Picture -->
                    <div class="form-group">
                        <label><i class="fas fa-image icon-container"></i> Profile Picture</label>
                        <input type="file" name="profile_picture" class="form-control">
                    </div>

                    <!-- New Password -->
                    <div class="form-group position-relative">
                        <label><i class="fas fa-lock icon-container"></i> New Password</label>
                        <input type="password" name="new_password" class="form-control" id="new_password">
                        <i class="fas fa-eye eye-icon" onclick="togglePasswordVisibility('new_password')"></i>
                    </div>
                    <div class="form-group position-relative">
                        <label><i class="fas fa-lock icon-container"></i> Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" id="confirm_password">
                        <i class="fas fa-eye eye-icon" onclick="togglePasswordVisibility('confirm_password')"></i>
                    </div>

                    <!-- Button Group -->
                    <div class="btn-group mt-4">
                        <button type="submit" class="btn btn-primary w-100">Update Profile</button>
                        <a href="dashboard.php" class="btn btn-secondary w-100">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://kit.fontawesome.com/a076d05399.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
    // Sidebar toggle with animation
    document.getElementById('sidebarCollapse').addEventListener('click', function () {
        document.getElementById('sidebar').classList.toggle('active');
        document.getElementById('content').classList.toggle('active');
    });

    // Toggle password visibility
    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        const eyeIcon = input.nextElementSibling;
        if (input.type === 'password') {
            input.type = 'text';
            eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>
</body>
</html>
