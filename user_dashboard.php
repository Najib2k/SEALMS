<?php
session_start();
if ($_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}
require 'db.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username']; // Retrieve the username from the session

// Query to fetch the user's leave history and status
$query = "SELECT leave_id, leave_type, start_date, end_date, status FROM leave_requests WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$leave_result = $stmt->get_result();

// Clock-in/out notifications
$message = "";

// Clock in
if (isset($_POST['clock_in'])) {
    $stmt = $conn->prepare("INSERT INTO attendance (user_id, username, clock_in) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $user_id, $username);
    if ($stmt->execute()) {
        $message = "Clocked in successfully!";
    }
}

// Clock out
if (isset($_POST['clock_out'])) {
    $stmt = $conn->prepare("UPDATE attendance SET clock_out = NOW() WHERE user_id = ? AND clock_out IS NULL");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $message = "Clocked out successfully!";
    }
}

// Check user's current status
$query = "SELECT clock_in, clock_out FROM attendance WHERE user_id = ? ORDER BY clock_in DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$last_attendance = $result->fetch_assoc();

$can_clock_in = !$last_attendance || $last_attendance['clock_out'] !== null;
$can_clock_out = $last_attendance && $last_attendance['clock_out'] === null;

// Fetch clock-in and clock-out history for the current month
$query = "SELECT clock_in, clock_out FROM attendance WHERE user_id = ? AND MONTH(clock_in) = MONTH(CURDATE()) AND YEAR(clock_in) = YEAR(CURDATE())";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$attendance_result = $stmt->get_result();

// Calculate total hours
$total_hours = 0;
$clock_history = [];
while ($row = $attendance_result->fetch_assoc()) {
    $clock_in = new DateTime($row['clock_in']);
    $clock_out = isset($row['clock_out']) ? new DateTime($row['clock_out']) : null;

    if ($clock_out) {
        $interval = $clock_in->diff($clock_out);
        $hours = $interval->h + ($interval->days * 24) + ($interval->i / 60); // Total hours for this entry
        $total_hours += $hours;

        $clock_history[] = [
            'clock_in' => $clock_in->format('Y-m-d H:i:s'),
            'clock_out' => $clock_out->format('Y-m-d H:i:s'),
            'hours' => round($hours, 2)
        ];
    } else {
        $clock_history[] = [
            'clock_in' => $clock_in->format('Y-m-d H:i:s'),
            'clock_out' => 'Currently Clocked In',
            'hours' => 'N/A'
        ];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <style>

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #4caf50;
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            opacity: 1;
            transition: opacity 3s ease-out, transform 3s ease-out;
            transform: translateY(0);
        }

        .notification.hide {
            opacity: 0;
            transform: translateY(-20px);
        }

        button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        button.fade {
            opacity: 0.6;
        }

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
            justify-content: center;  /* Centers horizontally */
            align-items: center;      /* Centers vertically */
            flex-direction: column;
            min-height: 100vh;         /* Ensures the body takes full viewport height */
            padding: 20px;
}


        .container {
            background-color: #fff;
            width: 100%;
            max-width: 900px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #4e54c8;
            text-align: center;
            margin-bottom: 30px;
        }

        form {
            margin-bottom: 30px;
            display: flex;
            justify-content: center;
            gap: 15px;
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
        }

        button:hover {
            background-color: #3c3f9f;
        }

        table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #f5f5f5;
            color: #4e54c8;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        small {
            font-size: 0.9em;
            color: #888;
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

        .summary {
            margin-top: 20px;
            font-weight: bold;
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
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const notification = document.querySelector(".notification");
            if (notification) {
                setTimeout(() => {
                    notification.classList.add("hide");
                }, 3000);
            }

            // Disable buttons based on user status
            const clockInButton = document.querySelector("#clock_in");
            const clockOutButton = document.querySelector("#clock_out");
            <?php if (!$can_clock_in): ?>
                clockInButton.disabled = true;
                clockInButton.classList.add("fade");
            <?php endif; ?>
            <?php if (!$can_clock_out): ?>
                clockOutButton.disabled = true;
                clockOutButton.classList.add("fade");
            <?php endif; ?>
        });
    </script>
</head>
<body>
    <div class="container">

    <?php if ($message): ?>
            <div class="notification"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
        <form method="POST">
            <button id="clock_in" name="clock_in" type="submit">Clock In</button>
            <button id="clock_out" name="clock_out" type="submit">Clock Out</button>
        </form>

        <!-- Apply Leave Button -->
        <form action="apply_leave.php" method="GET">
            <button type="submit">Apply for Leave</button>
        </form>

        <!-- Leave History Section -->
        <h3>Your Leave History</h3>
        <?php if ($leave_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $leave_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['leave_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You have no leave history.</p>
        <?php endif; ?>

        <!-- Clock-In and Clock-Out History Section -->
        <h3>Your Clock-In and Clock-Out History (This Month)</h3>
        <?php if (!empty($clock_history)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Hours Worked</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clock_history as $entry): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($entry['clock_in']); ?></td>
                            <td><?php echo htmlspecialchars($entry['clock_out']); ?></td>
                            <td><?php echo htmlspecialchars($entry['hours']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="summary"><strong>Total Hours Worked This Month:</strong> <?php echo round($total_hours, 2); ?> hours</p>
        <?php else: ?>
            <p>No clock-in history found for this month.</p>
        <?php endif; ?>

        <!-- Edit Profile Button -->
        <form action="user_edit_profile.php" method="GET">
            <button type="submit">Edit Profile</button>
        </form>

        <a class="back-btn" href="logout.php">Logout</a>
    </div>
</body>
</html>
