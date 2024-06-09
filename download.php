<?php
session_start();
include_once "php/config.php";

if (!isset($_SESSION['unique_id'])) {
    header("location: login.php");
    exit(); 
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['project_id'])) {
    $project_id = $_GET['project_id'];

    $files_query = $conn->prepare("SELECT `zip` FROM `Upload` WHERE `id` = ?");
    $files_query->bind_param("s", $project_id);
    $files_query->execute();
    $files_result = $files_query->get_result();


    if ($files_result && $files_result->num_rows > 0) {
        $files_row = $files_result->fetch_assoc();
        $file = $cv_row['zip'];

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($file));
        
        echo $file;
        exit();
    } else {
        die('file not found.');
    }
}
?> 