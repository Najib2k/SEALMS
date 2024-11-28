<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}
require 'db.php';

$message = ""; // Variable to store notification messages
$message_type = ""; // To differentiate success and error messages

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    $email = $_POST['email'];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO admins (username, password, name, email) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $hashed_password, $name, $email);

    // Execute the query and check for errors
    if ($stmt->execute()) {
        $message = "New admin created successfully!";
        $message_type = "success";
    } else {
        $message = "Error: " . $stmt->error;
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create New Admin</title>
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9f9f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            text-align: center;
        }

        h2 {
            font-size: 2em;
            color: #4e54c8;
            margin-bottom: 20px;
        }

        form {
            margin-top: 20px;
        }

        label {
            font-size: 1.1em;
            color: #333;
            margin-bottom: 5px;
            display: block;
            text-align: left;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 8px 0 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }

        button {
            background-color: #4e54c8;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #3c3f9f;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4e54c8;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .back-btn:hover {
            background-color: #3c3f9f;
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }

            h2 {
                font-size: 1.5em;
            }

            form {
                text-align: left;
            }

            /* Notification Styling */
            .notification {
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                padding: 15px 20px;
                border-radius: 5px;
                font-size: 1em;
                color: white;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.5s ease, visibility 0.5s ease;
                z-index: 1000;
            }

            .notification.success {
                background-color: #4caf50; /* Green for success */
            }

            .notification.error {
                background-color: #f44336; /* Red for error */
            }

            .notification.show {
                opacity: 1;
                visibility: visible;
            }

        }
    </style>
</head>
<body>

<div class="notification <?php echo htmlspecialchars($message_type); ?>" id="notification">
    <?php echo htmlspecialchars($message); ?>
</div>


    <div class="container">
        <h2>Create New Admin Account</h2>
        <form method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br><br>

            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required><br><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br><br>

            <button type="submit">Create Admin</button>
        </form>

        <a class="back-btn" href="admin_dashboard.php">Back to Admin Dashboard</a>
    </div>
</body>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const notification = document.getElementById("notification");
        if (notification && notification.textContent.trim() !== "") {
            notification.classList.add("show");
            setTimeout(() => {
                notification.classList.remove("show");
            }, 3000); // Hide after 3 seconds
        }
    });
</script>

</html>
