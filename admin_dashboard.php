<?php 
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}
require 'db.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: #f5f5f5;
            color: #333;
            height: 100vh;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            text-align: center;
        }

        h2 {
            font-size: 2em;
            color: #4e54c8;
            margin-bottom: 30px;
        }

        form {
            margin-bottom: 20px;
        }

        button {
            width: 100%;
            padding: 15px;
            background-color: #4e54c8;
            color: white;
            border: none;
            font-size: 1.1em;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-bottom: 15px;
        }

        button:hover {
            background-color: #3c3f9f;
        }

        a {
            color: #4e54c8;
            font-size: 1em;
            text-decoration: none;
            transition: color 0.3s;
        }

        a:hover {
            color: #3c3f9f;
        }

        /* Adding some margin for logout link */
        .logout-link {
            margin-top: 20px;
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .container {
                width: 90%;
                padding: 20px;
            }

            h2 {
                font-size: 1.5em;
            }

            button {
                font-size: 1em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome, Admin!</h2>
        <form action="admin_approve_leave.php" method="GET">
            <button type="submit">Manage Leave Requests</button>
        </form>

        <form action="create_admin.php" method="GET">
            <button type="submit">Create New Admin</button>
        </form>

        <form action="view_user_activities.php" method="GET">
            <button type="submit">View All Users' Activities</button>
        </form>

        <form action="edit_profile.php" method="GET">
            <button type="submit">Edit Profile</button>
        </form>

        <div class="logout-link">
            <a href="logout.php">Logout</a>
        </div>
    </div>
</body>
</html>
