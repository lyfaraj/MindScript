<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include_once "php/config.php";

if (!isset($_SESSION['unique_id'])) {
    header("location: login.php");
    exit();
}

$current_user_id = $_SESSION['unique_id'];
$query = "SELECT * FROM notifications WHERE user_id = $current_user_id ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
$notifications = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $row['lname'] = getUsernameById($row['from_user_id']);
        if ($row['type'] === 'like') {
            $project_id = $row['project_id'];
            $project_query = "SELECT name FROM projects WHERE project_id = $project_id";
            $project_result = mysqli_query($conn, $project_query);
            if ($project_result) {
                if (mysqli_num_rows($project_result) > 0) {
                    $project_row = mysqli_fetch_assoc($project_result);
                    $row['name'] = $project_row['name'];
                } else {
                    $row['name'] = "Unknown Project";
                }
            } else {
                $row['name'] = "Error fetching project name";
            }
        }
        $notifications[] = $row;
    }
}

function getUsernameById($userId) {
    global $conn;
    $query = "SELECT * FROM users WHERE unique_id = $userId";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['fname'] . ' ' . $row['lname'];
    } else {
        return "Unknown User";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <style>
        :root {
            --dark: #615C83;
            --pink: #F0AEB6;
            --lavender: #D0CADB;
            --box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
        }

        body {
            font-family: Arial, sans-serif;
            background-color: var(--lavender);
            color: var(--dark);
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .notification {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--box-shadow);
        }

        .type {
            font-weight: bold;
            font-size: 18px;
            color: var(--dark);
        }

        .from {
            font-size: 16px;
            color: var(--pink);
            margin-left: 10px;
        }

        .timestamp {
            text-align: right;
            font-size: 14px;
            color: #666;
        }

        h1 {
            text-align: center;
            color: var(--dark);
            margin-bottom: 30px;
        }

        .no-notifications {
            text-align: center;
            font-size: 18px;
            color: var(--dark);
            margin-top: 50px;
        }

        .back-arrow {
            position: absolute;
            left: 20px;
            top: 30px; 
            transform: translateY(-50%);
            color: white;
            font-size: 1.5em;
            z-index: 1; 
        }
    </style>
</head>
<body>
    <div class="container">
    <a href="users.php" class="back-arrow"><i class="fas fa-arrow-left"></i></a>
        <h1>Notifications</h1>
        <?php if (empty($notifications)) : ?>
            <div class="no-notifications">No new notifications.</div>
        <?php else : ?>
            <?php foreach ($notifications as $notification) : ?>
                <div class="notification">
                    <div>
                        <span class="type"><?php echo htmlspecialchars($notification['type']); ?></span>
                        <span class="from">from <?php echo htmlspecialchars($notification['lname']); ?></span>
                        <?php if ($notification['type'] === 'like') : ?>
                            <span class="project-name">(Project: <?php echo htmlspecialchars($notification['name']); ?>)</span>
                        <?php endif; ?>
                    </div>
                    <div class="timestamp"><?php echo date('Y-m-d H:i:s', strtotime($notification['created_at'])); ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>

