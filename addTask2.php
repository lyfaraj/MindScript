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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $time = mysqli_real_escape_string($conn, $_POST['time']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $insert_query = mysqli_query($conn, "INSERT INTO tasks (project_id, user_id, title, time, description)
        VALUES ('$project_id', '$user_id', '$title', '$time', '$description')");

    if ($insert_query) {
        header("Location: personalProject.php?project_id=$project_id");
    } else {
        echo "Something went wrong. Please try again!";
    }
}
?>