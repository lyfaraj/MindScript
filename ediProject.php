<?php 
session_start();
include_once "php/config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = mysqli_real_escape_string($conn, $_POST['project_id']);
    $project_name = mysqli_real_escape_string($conn, $_POST['name']);
    $project_category = mysqli_real_escape_string($conn, $_POST['category']);

    if (!empty($project_id) && !empty($project_name) && !empty($project_category)) {
        if (isset($_FILES['image'])) {
            $img_name = $_FILES['image']['name'];
            $img_type = $_FILES['image']['type'];
            $tmp_name = $_FILES['image']['tmp_name'];

            $img_explode = explode('.', $img_name);
            $img_ext = end($img_explode);

            $extensions = ["jpeg", "png", "jpg"];
            if (in_array($img_ext, $extensions) === true) {
                $types = ["image/jpeg", "image/jpg", "image/png"];
                if (in_array($img_type, $types) === true) {
                    $time = time();
                    $new_img_name = $time . $img_name;
                    if (move_uploaded_file($tmp_name, "Pimages/" . $new_img_name)) {
                        $update_query = "UPDATE projects SET name = '$project_name', category = '$project_category', pic = '$new_img_name' WHERE project_id = '$project_id'";
                        $update_result = mysqli_query($conn, $update_query);

                        if ($update_result) {
                            echo "Project updated successfully!";
                            header("location: groupProject.php?project_id=$project_id");
                        } else {
                            echo "Error updating project: " . mysqli_error($conn);
                        }
                    } else {
                        echo "Error uploading image.";
                    }
                } else {
                    echo "Please upload an image file - jpeg, png, jpg";
                }
            } else {
                echo "Please upload an image file - jpeg, png, jpg";
            }
        }
    } else {
        echo "All input fields are required!";
    }

$select_query = "SELECT name, category, pic FROM projects WHERE project_id = '$project_id'";
$result = mysqli_query($conn, $select_query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
} else {
    echo "Error fetching project details: " . mysqli_error($conn);
}
}
?>


