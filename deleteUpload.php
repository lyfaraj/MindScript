<?php
session_start();
include_once "php/config.php";

if (!isset($_SESSION['unique_id'])) {
    header("location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $project_id = isset($_POST['project_id']) ? mysqli_real_escape_string($conn, $_POST['project_id']) : null;

    if ($project_id === null) {
        die('Project ID is not set.');
    }

    $delete_query = mysqli_query($conn, "DELETE FROM Upload WHERE id = '$project_id'");

    if ($delete_query) {
        echo 'Project deleted successfully.';
    } else {
        die('Error deleting project: ' . mysqli_error($conn));
    }
} else {
    die('Invalid request method.');
}
?>
