<?php
session_start();
require 'db.php';
require 'vendor/autoload.php';

use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];
$mfaSecret = (new GoogleAuthenticator())->generateSecret();

// Determine the table based on role
$table = $role === 'admin' ? 'admins' : 'users';

// Store `mfa_secret` in the appropriate table
$stmt = $conn->prepare("UPDATE $table SET mfa_secret = ? WHERE " . ($role === 'admin' ? 'admin_id' : 'user_id') . " = ?");
$stmt->bind_param("si", $mfaSecret, $userId);
$stmt->execute();

// Generate QR code URL
$qrCodeUrl = GoogleQrUrl::generate($_SESSION['username'], $mfaSecret, 'ZO Sorting Global Enterprise');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QR Code</title>
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
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
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

        img {
            margin: 20px 0;
            max-width: 80%;
            border: 2px solid #ddd;
            border-radius: 8px;
        }

        a {
            display: inline-block;
            background-color: #4e54c8;
            color: #fff;
            padding: 10px 20px;
            font-size: 1em;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
            margin-top: 20px;
        }

        a:hover {
            background-color: #3c3f9f;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Scan this QR Code with Google Authenticator</h2>
        <p>Use Google Authenticator app to scan the following QR code:</p>
        <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code">
        <p>After scanning, please enter the code from your app to complete setup.</p>
        <a href="otp_approval.php">Proceed to OTP Approval</a>
    </div>
</body>
</html>
