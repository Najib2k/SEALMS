<?php
session_start();
require 'db.php';

$error_message = ''; // Variable to store error messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Select the appropriate table based on the role
    $query = $role == 'user' ? "SELECT * FROM users WHERE username = ?" : "SELECT * FROM admins WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Set session variables for the logged-in user
            $_SESSION['user_id'] = $role == 'admin' ? $user['admin_id'] : $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $role;
            
            // Admin-specific redirection based on mfa_secret
            if ($role == 'admin') {
                if (is_null($user['mfa_secret']) || empty($user['mfa_secret'])) {
                    // Redirect to QR scan page if mfa_secret is not set
                    header("Location: qr_scan.php");
                } else {
                    // Redirect to OTP approval page if mfa_secret is set
                    header("Location: otp_approval.php");
                }
            } else {
                // For regular users, redirect to OTP approval page
                header("Location: otp_approval.php");
            }
            exit();
        } else {
            $error_message = "Incorrect password.";
        }
    } else {
        $error_message = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        /* Styling from your existing code */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: linear-gradient(135deg, #4e54c8, #8f94fb); color: #333; }
        .container { background-color: #fff; padding: 30px; border-radius: 10px; box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1); max-width: 400px; width: 100%; text-align: center; }
        h2 { font-size: 1.8em; margin-bottom: 20px; color: #333; }
        .form-group { margin-bottom: 20px; text-align: left; }
        label { display: block; font-weight: bold; color: #333; margin-bottom: 5px; }
        input[type="text"], input[type="password"], select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 1em; }
        button[type="submit"] { width: 100%; padding: 10px; background-color: #4e54c8; border: none; color: #fff; font-size: 1em; border-radius: 5px; cursor: pointer; transition: background-color 0.3s; }
        button[type="submit"]:hover { background-color: #3c3f9f; }
        p { margin-top: 15px; font-size: 0.9em; }
        a { color: #4e54c8; text-decoration: none; transition: color 0.3s; }
        a:hover { color: #3c3f9f; }
        .error-message { background-color: #f44336; color: white; padding: 10px; margin-bottom: 20px; border-radius: 5px; font-weight: bold; display: none; text-align: center; }
    </style>
    <script>
        window.onload = function() {
            var errorMessage = '<?php echo $error_message; ?>';
            if (errorMessage) {
                var errorDiv = document.getElementById('error-message');
                errorDiv.textContent = errorMessage;
                errorDiv.style.display = 'block';
                setTimeout(function() { errorDiv.style.display = 'none'; }, 3000);
            }
        };
    </script>
</head>
<body>
    <div class="container">
        <h2>ZO Sorting Global Enterprise Login</h2>
        <div id="error-message" class="error-message"></div>
        <form method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="role">Login as:</label>
                <select id="role" name="role">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>