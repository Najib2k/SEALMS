<?php
session_start();
require 'db.php';
require 'vendor/autoload.php';

use OTPHP\TOTP;

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userOtp = $_POST['otp'];
    $userId = $_SESSION['user_id'];
    $role = $_SESSION['role'];

    // Determine the table and ID column based on role
    $table = $role === 'admin' ? 'admins' : 'users';
    $idColumn = $role === 'admin' ? 'admin_id' : 'user_id';

    // Retrieve the user's `mfa_secret` from the appropriate table
    $stmt = $conn->prepare("SELECT mfa_secret FROM $table WHERE $idColumn = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && $user['mfa_secret']) {
        $totp = TOTP::create($user['mfa_secret']);

        // Verify the OTP
        if ($totp->verify($userOtp)) {
            // Redirect to the appropriate dashboard
            $redirectPage = $role === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php';
            header("Location: $redirectPage");
            exit();
        } else {
            $error = "Invalid OTP. Please try again.";
        }
    } else {
        $error = "MFA setup incomplete. Please scan the QR code first.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Approval</title>
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #4e54c8, #8f94fb);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            color: #333;
        }

        .container {
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        h2 {
            font-size: 1.8em;
            color: #333;
            margin-bottom: 20px;
        }

        p {
            font-size: 1em;
            color: #555;
            margin-bottom: 15px;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 1em;
        }

        button[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #4e54c8;
            border: none;
            color: #fff;
            font-size: 1em;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button[type="submit"]:hover {
            background-color: #3c3f9f;
        }

        .error {
            color: red;
            font-size: 1em;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Enter OTP from Google Authenticator</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="otp">One-Time Password (OTP):</label>
            <input type="text" id="otp" name="otp" required>
            <button type="submit">Verify OTP</button>
        </form>
    </div>
</body>
</html>
