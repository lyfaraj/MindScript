<?php
session_start();

if (isset($_SESSION['unique_id'])) {
    include_once "php/config.php";
    $delete_id = mysqli_real_escape_string($conn, $_GET['project_id']);

    mysqli_query($conn, "DELETE FROM tasks WHERE project_id = $delete_id");

    mysqli_query($conn, "DELETE FROM project_members WHERE project_id = $delete_id");

    $sql = mysqli_query($conn, "DELETE FROM projects WHERE project_id = $delete_id");

    if ($sql) {
        header("location: projectManager.php");
        exit();
    } else {
        echo "Something went wrong. Please try again!";
    }
} else {
    header("location: login.php");
    exit();
}
?>

