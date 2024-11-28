<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}
require 'db.php';

// Query to get all users' activities (clock-in and clock-out) for today only
$query = "SELECT users.username, attendance.clock_in, attendance.clock_out 
          FROM attendance
          JOIN users ON attendance.user_id = users.user_id
          WHERE DATE(attendance.clock_in) = CURDATE()";
$result = $conn->query($query);

// Query to calculate total hours worked by each user for the current month
$total_hours_query = "
    SELECT users.username, 
           SUM(TIMESTAMPDIFF(SECOND, attendance.clock_in, attendance.clock_out) / 3600) AS total_hours
    FROM attendance
    JOIN users ON attendance.user_id = users.user_id
    WHERE MONTH(attendance.clock_in) = MONTH(CURDATE()) AND YEAR(attendance.clock_in) = YEAR(CURDATE())
    AND attendance.clock_out IS NOT NULL
    GROUP BY users.username";
$total_hours_result = $conn->query($total_hours_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Users' Activities</title>
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fb;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            width: 100%;
            max-width: 900px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #4e54c8;
            margin-bottom: 20px;
            font-size: 1.8em;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 1em;
        }

        th {
            background-color: #4e54c8;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #ddd;
        }

        p {
            text-align: center;
            color: #888;
            font-size: 1.1em;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4e54c8;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
            transition: background-color 0.3s;
        }

        .back-btn:hover {
            background-color: #3c3f9f;
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            h2 {
                font-size: 1.5em;
            }

            table, th, td {
                font-size: 0.9em;
            }

            .container {
                padding: 15px;
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>All Users' Clock In/Out History (Today)</h2>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['username']; ?></td>
                            <td><?php echo $row['clock_in']; ?></td>
                            <td><?php echo $row['clock_out']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No user activities found for today.</p>
        <?php endif; ?>

        <h2>Total Hours Worked by Each User (This Month)</h2>

        <?php if ($total_hours_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Total Hours Worked</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $total_hours_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['username']; ?></td>
                            <td><?php echo round($row['total_hours'], 2); ?> hours</td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No work hours found for this month.</p>
        <?php endif; ?>

        <a class="back-btn" href="admin_dashboard.php">Back to Admin Dashboard</a>
    </div>
</body>
</html>
