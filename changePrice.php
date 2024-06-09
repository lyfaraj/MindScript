<?php
session_start();
include_once "php/config.php";

if (!isset($_SESSION['unique_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

$user_id = $_SESSION['unique_id'];

if ($user_id === null) {
    header("HTTP/1.1 400 Bad Request");
    exit('User ID is not set.');
}

$id = isset($_POST['project_id']) ? $_POST['project_id'] : null;
$new_price = isset($_POST['new_price']) ? $_POST['new_price'] : null;

if ($id === null || $new_price === null) {
    header("HTTP/1.1 400 Bad Request");
    exit('Project ID or new price is missing.');
}

if (!is_numeric($new_price)) {
    header("HTTP/1.1 400 Bad Request");
    exit('Invalid price format.');
}

$id = mysqli_real_escape_string($conn, $id);
$new_price = mysqli_real_escape_string($conn, $new_price);
$update_query = "UPDATE Upload SET price = '$new_price' WHERE id = '$id'";
$result = mysqli_query($conn, $update_query);

if (!$result) {
    error_log("Error updating price: " . mysqli_error($conn));
    header("HTTP/1.1 500 Internal Server Error");
    exit('Error updating price. Please try again later.');
}

echo "success";
?>
