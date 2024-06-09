<?php
session_start();
include_once "php/config.php";

if (!isset($_SESSION['unique_id'])) {
    header("location: login.php");
    exit(); 
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['creator_id'])) {
    $creator_id = $_GET['creator_id'];

    $cv_query = $conn->prepare("SELECT `file_name`, `file_content` FROM `cv_files` WHERE `user_id` = ?");
    $cv_query->bind_param("s", $creator_id);
    $cv_query->execute();
    $cv_result = $cv_query->get_result();

    if ($cv_result && $cv_result->num_rows > 0) {
        $cv_row = $cv_result->fetch_assoc();
        $file_name = $cv_row['file_name'];
        $file_content = $cv_row['file_content'];

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($file_content));
        
        echo $file_content;
        exit();
    } else {
        die('CV not found.');
    }
}
?>

