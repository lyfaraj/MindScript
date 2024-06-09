<?php
session_start();
include_once "php/config.php";

if (!isset($_SESSION['unique_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['unique_id'];
$project_id = isset($_POST['project_id']) ? $_POST['project_id'] : null;
$creator_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;

if ($project_id === null) {
    echo json_encode(['status' => 'error', 'message' => 'Project ID is not set']);
    exit();
}

if ($creator_id === null) {
    echo json_encode(['status' => 'error', 'message' => 'Creator ID is not set']);
    exit();
}

$check_like_query = mysqli_query($conn, "SELECT * FROM Likes WHERE user_id = $user_id AND project_id = $project_id");

if (mysqli_num_rows($check_like_query) > 0) {
    $delete_like_query = mysqli_query($conn, "DELETE FROM Likes WHERE user_id = $user_id AND project_id = $project_id");
    if ($delete_like_query) {
        echo json_encode(['status' => 'success', 'action' => 'unliked']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Something went wrong while unliking the project']);
    }
} else {
    $insert_like_query = mysqli_query($conn, "INSERT INTO Likes (user_id, project_id) VALUES ($user_id, $project_id)");
    if ($insert_like_query) {
        $notification_type = "liked";
        $notification_from_user_id = $user_id;
        $notification_created_at = date('Y-m-d H:i:s');
        $insert_notification_query = mysqli_query($conn, "INSERT INTO notifications (user_id, type, from_user_id, created_at) VALUES ($creator_id, '$notification_type', $notification_from_user_id, '$notification_created_at')");
        if ($insert_notification_query) {
            echo json_encode(['status' => 'success', 'action' => 'liked']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Something went wrong while liking the project']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Something went wrong while liking the project']);
    }
}
?>


