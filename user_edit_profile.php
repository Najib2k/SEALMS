<?php
session_start();
require 'db.php';

// Check if the user is logged in
if ($_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data from the database
$query = "SELECT username, name, email FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Update user credentials when the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = $_POST['username'];
    $new_password = $_POST['password'];
    $new_name = $_POST['name'];
    $new_email = $_POST['email'];

    // Hash the password if it's being changed
    if (!empty($new_password)) {
        $new_password = password_hash($new_password, PASSWORD_DEFAULT);
    } else {
        // Keep the old password if it's not changed
        $new_password = $user['password'];
    }

    // Update the user in the database
    $update_query = "UPDATE users SET username = ?, password = ?, name = ?, email = ? WHERE user_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssssi", $new_username, $new_password, $new_name, $new_email, $user_id);
    $stmt->execute();

    // Redirect to the user dashboard after updating
    header("Location: user_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <style>
        /* General reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fb;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .container {
            background-color: #fff;
            width: 100%;
            max-width: 600px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            color: #4e54c8;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }

        label {
            font-size: 1.1em;
            font-weight: bold;
            width: 100%;
            text-align: left;
            margin-bottom: 5px;
        }

        input[type="text"], input[type="password"], input[type="email"] {
            width: 100%;
            padding: 10px;
            font-size: 1em;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus, input[type="password"]:focus, input[type="email"]:focus {
            border-color: #4e54c8;
        }

        button {
            padding: 10px 20px;
            background-color: #4e54c8;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background-color 0.3s;
            width: 100%;
        }

        button:hover {
            background-color: #3c3f9f;
        }

        .back-btn {
            margin-top: 20px;
            display: inline-block;
            padding: 10px 20px;
            background-color: #ddd;
            color: #333;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1.1em;
            text-align: center;
            transition: background-color 0.3s;
        }

        .back-btn:hover {
            background-color: #ccc;
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .container {
                width: 90%;
                padding: 20px;
            }

            h2 {
                font-size: 1.6em;
            }

            button {
                font-size: 1em;
            }

            .back-btn {
                font-size: 1em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Profile</h2>

        <form method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo $user['username']; ?>" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Leave blank to keep current password">

            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo $user['name']; ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>

            <button type="submit">Update Profile</button>
        </form>

        <a class="back-btn" href="user_dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>

