<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

require 'db.php';

// Debug: Check if the session variable is set
//echo "Admin ID in session: " . $_SESSION['user_id'] . "<br>"; // Check session value

// Check if admin_id is set in the session
$admin_id = $_SESSION['user_id'];

// Fetch the admin's current details
$query = "SELECT * FROM admins WHERE admin_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

// Debug: Check if the result is empty
if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc(); // Fetch the admin's data
} else {
    echo "Admin not found!"; // If no admin found, show this message
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    $email = $_POST['email'];

    // Hash password if it's being changed
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    } else {
        // If password is empty, keep the current password
        $hashed_password = $admin['password'];
    }

    // Update admin details in the database
    $update_query = "UPDATE admins SET username = ?, password = ?, name = ?, email = ? WHERE admin_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssssi", $username, $hashed_password, $name, $email, $admin_id);

    if ($update_stmt->execute()) {
        echo "Profile updated successfully!";
    } else {
        echo "Error updating profile.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <style>
        /* Reset */
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
            min-height: 100vh;
            color: #333;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            width: 100%;
            max-width: 600px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #4e54c8;
            text-align: center;
            margin-bottom: 20px;
            font-size: 2em;
        }

        label {
            font-size: 1.1em;
            margin-bottom: 5px;
            display: block;
            color: #555;
        }

        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
            color: #333;
        }

        input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus {
            border-color: #4e54c8;
            outline: none;
        }

        small {
            font-size: 0.9em;
            color: #888;
        }

        button {
            padding: 12px 20px;
            background-color: #4e54c8;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #3c3f9f;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #ddd;
            color: #333;
            border-radius: 5px;
            text-decoration: none;
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
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Profile</h2>
        <form method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password">
            <small>Leave blank if you do not want to change the password.</small>
            
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
            
            <button type="submit">Update Profile</button>
        </form>
        
        <a class="back-btn" href="admin_dashboard.php">Back to Admin Dashboard</a>
    </div>
</body>
</html>
