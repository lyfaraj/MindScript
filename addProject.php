<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include_once "php/config.php";

if (!isset($_SESSION['unique_id'])) {
    header("location: login.php");
    exit();
}

$user_id = $_SESSION['unique_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['name']) || empty($_POST['type']) || empty($_POST['category'])) {
        echo "Please fill in all the fields.";
        exit();
    }

    $name = $_POST['name'];
    $type = $_POST['type'];
    $category = $_POST['category'];

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        echo "Error uploading image.";
        exit();
    }

    $img_name = $_FILES['image']['name'];
    $tmp_name = $_FILES['image']['tmp_name'];
    $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
    $allowed_ext = ["jpeg", "png", "jpg"];

    if (!in_array($img_ext, $allowed_ext)) {
        echo "Please upload an image file - JPEG, PNG, JPG.";
        exit();
    }

    $time = time();
    $new_img_name = $time . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $img_name);
    $upload_dir = "Pimages/";

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    if (!move_uploaded_file($tmp_name, $upload_dir . $new_img_name)) {
        echo "Something went wrong while uploading the image. Please try again!";
        exit();
    }

    $insert_project_query = $conn->prepare("INSERT INTO projects (user_id, name, type, category, pic) VALUES (?, ?, ?, ?, ?)");
    if (!$insert_project_query) {
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        exit();
    }

    $insert_project_query->bind_param("issss", $user_id, $name, $type, $category, $new_img_name);

    if ($insert_project_query->execute()) {
        $project_id = $insert_project_query->insert_id;

        $select_user_query = $conn->prepare("SELECT email FROM users WHERE unique_id = ?");
        if (!$select_user_query) {
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
            exit();
        }

        $select_user_query->bind_param("i", $user_id);
        $select_user_query->execute();
        $select_user_result = $select_user_query->get_result();

        if ($select_user_result->num_rows > 0) {
            $user_row = $select_user_result->fetch_assoc();
            $email = $user_row['email'];

            $insert_member_query = $conn->prepare("INSERT INTO project_members (project_id, user_id, email) VALUES (?, ?, ?)");
            if (!$insert_member_query) {
                echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
                exit();
            }

            $insert_member_query->bind_param("iis", $project_id, $user_id, $email);
            if (!$insert_member_query->execute()) {
                echo "Execute failed: (" . $insert_member_query->errno . ") " . $insert_member_query->error;
                exit();
            }
        } else {
            echo "User with the provided ID not found.";
            exit();
        }

        if ($type === 'group') {
            if (empty($_POST['email'])) {
                echo "Please provide an email for group project.";
                exit();
            }

            $added_email = $_POST['email'];

            $select_user_query = $conn->prepare("SELECT unique_id FROM users WHERE email = ?");
            if (!$select_user_query) {
                echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
                exit();
            }

            $select_user_query->bind_param("s", $added_email);
            $select_user_query->execute();
            $select_user_result = $select_user_query->get_result();

            if ($select_user_result->num_rows > 0) {
                $user_row = $select_user_result->fetch_assoc();
                $selected_user_id = $user_row['unique_id'];

                $insert_member_query = $conn->prepare("INSERT INTO project_members (project_id, user_id, email) VALUES (?, ?, ?)");
                if (!$insert_member_query) {
                    echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
                    exit();
                }

                $insert_member_query->bind_param("iis", $project_id, $selected_user_id, $added_email);
                if (!$insert_member_query->execute()) {
                    echo "Execute failed: (" . $insert_member_query->errno . ") " . $insert_member_query->error;
                    exit();
                }

                $notification_query = $conn->prepare("INSERT INTO notifications (user_id, type, from_user_id) VALUES (?, 'Collab', ?)");
                if (!$notification_query) {
                    echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
                    exit();
                }

                $notification_query->bind_param("ii", $selected_user_id, $user_id);
                if (!$notification_query->execute()) {
                    echo "Execute failed: (" . $notification_query->errno . ") " . $notification_query->error;
                    exit();
                }
            } else {
                echo "User with the provided email not found.";
                exit();
            }
        }

        header("Location: projectManager.php");
        exit();
    } else {
        echo "Error adding project: " . $insert_project_query->error;
        exit();
    }
} else {
    echo "Invalid request method.";
}
?>
