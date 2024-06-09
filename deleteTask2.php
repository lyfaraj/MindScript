<?php
session_start();
include_once "php/config.php";

if (!isset($_SESSION['unique_id'])) {
    header("location: login.php");
    exit();
}

$user_id = $_SESSION['unique_id'];
$task_id = isset($_GET['task_id']) ? intval($_GET['task_id']) : null;

if ($task_id === null) {
    die('Task ID is not set.');
}

$delete_query = "DELETE FROM tasks WHERE id = $task_id AND project_id IN (SELECT project_id FROM projects WHERE user_id = $user_id)";

if (mysqli_query($conn, $delete_query)) {
    header("location: projectManager.php");
    exit();
} else {
    echo "Error deleting task: " . mysqli_error($conn);
}
?>

