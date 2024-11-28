<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = 'user';

    // Check if the username or email already exists
    $query = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Username or email already exists.";
    } else {
        // Insert new user data
        $query = "INSERT INTO users (username, password, name, email, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssss", $username, $password, $name, $email, $role);

        if ($stmt->execute()) {
            // Set session variables and redirect to QR scan page
            $_SESSION['user_id'] = $conn->insert_id; // Assuming 'user_id' is auto-incremented
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            
            header("Location: qr_scan.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <style>
        /* Styling from your existing code */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: linear-gradient(to right, #6a11cb, #2575fc); color: #333; }
        h2 { color: #333; font-size: 2em; margin-bottom: 20px; text-align: center; }
        .container { background-color: #fff; padding: 40px; border-radius: 10px; box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1); max-width: 400px; width: 100%; text-align: center; }
        .form-group { margin-bottom: 20px; text-align: left; }
        label { display: block; font-weight: bold; color: #333; }
        input[type="text"], input[type="password"], input[type="email"] { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 5px; font-size: 1em; }
        button[type="submit"] { width: 100%; padding: 10px; background-color: #2575fc; border: none; color: #fff; font-size: 1em; border-radius: 5px; cursor: pointer; transition: background-color 0.3s; }
        button[type="submit"]:hover { background-color: #1a60d1; }
        p { margin-top: 10px; }
        a { color: #2575fc; text-decoration: none; transition: color 0.3s; }
        a:hover { color: #1a60d1; }
    </style>
</head>
<body>
    <div class="container">
        <h2>User Registration</h2>
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
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>