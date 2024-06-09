<?php
session_start();
include_once "php/config.php";

if (!isset($_SESSION['unique_id'])) {
    header("location: login.php");
    exit();
}

$user_id = $_SESSION['unique_id'];
$project_id = isset($_GET['project_id']) ? $_GET['project_id'] : null;

if ($project_id === null) {
    die('Project ID is not set.');
}

$delete_query = "DELETE FROM project_members WHERE project_id = $project_id AND user_id = $user_id";

if (mysqli_query($conn, $delete_query)) {
    header("location: projectManager.php");
    exit();
} else {
    echo "Error leaving project: " . mysqli_error($conn);
}
?>
