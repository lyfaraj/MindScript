<?php
session_start();

if (isset($_SESSION['unique_id'])) {
    include_once "config.php";
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);

    // Delete related records in projects table
    mysqli_query($conn, "DELETE FROM projects WHERE user_id = {$delete_id}");

    // Now, delete the user
    $sql = mysqli_query($conn, "DELETE FROM users WHERE unique_id = {$delete_id}");

    if ($sql) {
        session_unset();
        session_destroy();
        header("location: ../login.php");
    } else {
        echo "Something went wrong. Please try again!";
    }
} else {
    header("location: ../users.php");
}
?>
