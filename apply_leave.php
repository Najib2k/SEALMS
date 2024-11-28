<?php
session_start();
if ($_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}
require 'db.php';

$user_id = $_SESSION['user_id'];
$successMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $leave_type = $_POST['leave_type'];

    $query = "INSERT INTO leave_requests (user_id, start_date, end_date, leave_type, status) VALUES (?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isss", $user_id, $start_date, $end_date, $leave_type);

    if ($stmt->execute()) {
        $successMessage = "Leave request submitted successfully!";
    } else {
        $successMessage = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Apply for Leave</title>
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
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            height: 100vh;
            padding: 20px;
            margin: 0;
        }

        .container {
            background-color: #fff;
            width: 100%;
            max-width: 600px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 1em;
            animation: fadeOut 5s forwards;
        }

        @keyframes fadeOut {
            0% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                display: none;
            }
        }

        h2 {
            color: #4e54c8;
            margin-bottom: 30px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: center;
        }

        label {
            font-size: 1.1em;
            font-weight: bold;
            text-align: left;
            width: 100%;
        }

        input[type="date"], select {
            padding: 10px;
            font-size: 1em;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
            width: 100%;
        }

        input[type="date"]:focus, select:focus {
            border-color: #4e54c8;
        }

        button {
            padding: 10px 20px;
            background-color: #4e54c8;
            color: white;
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
        <?php if ($successMessage): ?>
            <div class="success-message" id="successMessage"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <h2>Apply for Leave</h2>
        <form method="POST">
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" required>
            
            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" required>
            
            <label for="leave_type">Leave Type:</label>
            <select id="leave_type" name="leave_type" required>
                <option value="sick">Sick Leave</option>
                <option value="vacation">Vacation Leave</option>
                <option value="other">Other</option>
            </select>
            
            <button type="submit">Submit Leave Request</button>
        </form>
        
        <a class="back-btn" href="user_dashboard.php">Back to Dashboard</a>
    </div>

    <script>
        // Auto fade-out the success message
        const successMessage = document.getElementById('successMessage');
        if (successMessage) {
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 5000);
        }
    </script>
</body>
</html>
