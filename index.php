<?php
// index.php
session_start();
require 'config.php'; // Include the database configuration

// If the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if the username and password are correct
    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        // Verify the password (if hashed, use password_verify)
        if ($password == $admin['password']) {
            // Password is correct, set session
            $_SESSION['admin_id'] = $admin['admin_id'];
            echo 'success'; // Send success response for AJAX
            exit();
        } else {
            echo 'error'; // Incorrect password
        }
    } else {
        echo 'error'; // Admin not found
    }

    $stmt->close();
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym  Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .login-container { max-width: 400px; margin-top: 10%; }
        .input-icon { position: relative; }
        .input-icon i { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #777; }
        .message { font-size: 1.1em; color: #555; margin-top: 20px; display: none; }
        .spinner { display: none; margin-left: 10px; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="container login-container text-center">
        <h2>Gym Owner Login</h2>
        <form id="loginForm" class="mt-4">
            <div class="form-group input-icon">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
                <i class="fas fa-user"></i>
            </div>
            <div class="form-group input-icon">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <i class="fas fa-eye" id="togglePassword"></i>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
            <div class="message mt-3">
                <i class="fas fa-spinner spinner"></i> <span id="messageText"></span>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle password visibility
            $('#togglePassword').click(function() {
                const passwordField = $('#password');
                const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
                passwordField.attr('type', type);
                $(this).toggleClass('fa-eye fa-eye-slash');
            });

            // Handle form submission with AJAX
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                $('.message').show();
                $('#messageText').text('Thank you for your patience. We’re verifying your information...');
                $('.spinner').show();

                $.ajax({
    type: 'POST',
    url: 'index.php',
    data: $(this).serialize(),
    success: function(response) {
        if (response.trim() === 'success') {
            $('#messageText').text('Login successful! Thank you for waiting, redirecting you now...');
            setTimeout(() => window.location.href = 'dashboard.php', 5000); // 5-second delay
        } else {
            $('#messageText').text('Oops! The credentials don’t seem to match. Please double-check and try again.');
            $('.spinner').hide();
        }
    },
    error: function() {
        $('#messageText').text('An error occurred. Please try again.');
        $('.spinner').hide();
    }
});

            });
        });
    </script>
</body>
</html>
