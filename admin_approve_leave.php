<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

require 'db.php';

$message = ""; // Variable to store notification messages
$message_type = ""; // To differentiate success and error messages

// Approve leave request
if (isset($_GET['approve'])) {
    $leave_id = $_GET['approve'];
    $admin_id = $_SESSION['user_id']; // Get the admin ID from session
    $stmt = $conn->prepare("UPDATE leave_requests SET status = 'Approved', approval_admin_id = ? WHERE leave_id = ?");
    $stmt->bind_param("ii", $admin_id, $leave_id);
    if ($stmt->execute()) {
        $message = "Leave approved successfully!";
        $message_type = "success";
    }
}

// Reject leave request
if (isset($_GET['reject'])) {
    $leave_id = $_GET['reject'];
    $stmt = $conn->prepare("UPDATE leave_requests SET status = 'Rejected' WHERE leave_id = ?");
    $stmt->bind_param("i", $leave_id);
    if ($stmt->execute()) {
        $message = "Leave rejected successfully!";
        $message_type = "error";
    }
}

// Get all pending leave requests
$query = "SELECT users.username, leave_requests.leave_type, 
                 leave_requests.start_date, leave_requests.end_date, leave_requests.status, leave_requests.leave_id
          FROM leave_requests
          JOIN users ON leave_requests.user_id = users.user_id
          WHERE leave_requests.status = 'Pending'";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Approve Leave Requests</title>
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
            max-width: 900px;
            text-align: center;
        }

        h2 {
            font-size: 2em;
            color: #4e54c8;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #4e54c8;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        td a {
            color: #4e54c8;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        td a:hover {
            background-color: #ddd;
        }

        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4e54c8;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 20px;
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

            table {
                font-size: 0.9em;
            }

            td a {
                font-size: 0.9em;
            }
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


    </style>
</head>
<body>
<div class="notification <?php echo htmlspecialchars($message_type); ?>" id="notification">
    <?php echo htmlspecialchars($message); ?>
</div>
    <div class="container">
        <h2>Approve or Reject Leave Requests</h2>
        <table>
            <tr>
                <th>Username</th>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>

            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['username'] . "</td>";
                    echo "<td>" . $row['leave_type'] . "</td>";
                    echo "<td>" . $row['start_date'] . "</td>";
                    echo "<td>" . $row['end_date'] . "</td>";
                    echo "<td>" . $row['status'] . "</td>";
                    echo "<td>
                            <a href='admin_approve_leave.php?approve=" . $row['leave_id'] . "'>Approve</a> | 
                            <a href='admin_approve_leave.php?reject=" . $row['leave_id'] . "'>Reject</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No pending leave requests.</td></tr>";
            }
            ?>
        </table>

        <a class="back-btn" href="admin_dashboard.php">Back to Dashboard</a>
    </div>
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

</body>
</html>
