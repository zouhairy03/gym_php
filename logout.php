<?php
session_start();

// Handle AJAX logout request
if (isset($_POST['logout']) && $_POST['logout'] === 'true') {
    session_unset();
    session_destroy();
    echo 'success';
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout Confirmation</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        .rotate {
            animation: rotate 1s linear infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .confirmation-message {
            font-size: 20px;
            color: #555;
            margin-top: 20px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container text-center mt-5">
        <h3><i class="fas fa-sign-out-alt"></i> Are you sure you want to log out?</h3>
        <p>Your session will be closed, and you will be redirected to the login page.</p>

        <!-- Confirmation Buttons -->
        <button id="logoutConfirm" class="btn btn-danger">
            <i class="fas fa-check"></i> Yes, Logout
        </button>
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="fas fa-times"></i> Cancel
        </a>
        
        <div class="confirmation-message">
            <i class="fas fa-spinner rotate"></i> Hold on a second... Logging you out.
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#logoutConfirm').on('click', function() {
                // Show hold message and animation
                $('.confirmation-message').show();

                // AJAX request to process logout
                $.ajax({
                    type: 'POST',
                    url: 'logout.php',
                    data: { logout: 'true' },
                    success: function(response) {
                        if (response === 'success') {
                            // Wait 5 seconds and then redirect
                            setTimeout(function() {
                                window.location.href = 'index.php';
                            }, 5000);
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
