<?php
session_start();
include_once "php/config.php";
if (!isset($_SESSION['unique_id'])) {
    header("location: login.php");
    exit(); 
}
?>
<?php include_once "header.php"; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Page</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
       body {
        background: 
        rgb(17,68,96);
      color: #fff;
      font-family: 'Courier New', monospace;
      text-decoration: none;
    }
    
    .form-container {
      max-width: 400px;
      margin: 0 auto;
      margin-top: 80px;
      padding: 1rem;
    text-align: center;
    background: rgba(127, 127, 127, .25);
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255,.18);
    position: relative;
    }
    
    .form-container h2 {
      margin-bottom: 20px;
    }
    
    .form-container label {
      display: block;
      margin-bottom: 10px;
      text-align: left;
    }
    
    .form-container input,
    .form-container textarea {
      width: 100%;
      padding: 10px;
      border-radius: 5px;
      border: none;
      margin-bottom: 10px;
    }  
    
    .form-container button {
      background-color: #42a3c3;
      border: none;
      color: #fff;
      padding: 10px 20px;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s;
    }
    
    .form-container button:hover {
      background-color: #328da4;
    }

    .success-message {
      color: #4caf50;
      text-align: center;
      margin-top: 80px;
    }
    
    .error-message {
      color: #721c24;
      text-align: center;
      margin-top: 80px;
    }

    .file-input-container {
        position: relative;
        display: inline-block;
        overflow: hidden;
        margin-bottom: 10px;
    }
        
    .file-input-container input {
        font-size: 100px;
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
        cursor: pointer;
    }
        
    .file-input-container button {
        background-color: #42a3c3;
        border: none;
        color: #fff;
        padding: 6px 10px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    button a {
  text-decoration: none;
  color: #fff;
}
    </style>
</head>

<body>
    <div class="form-container">
        <?php
        $check_user_query = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$_SESSION['unique_id']}");
        if (mysqli_num_rows($check_user_query) > 0) {
            $row = mysqli_fetch_assoc($check_user_query);
            $user_id = $row['user_id']; 
        } else {
            echo "User not found!";
            exit(); 
        }
        ?>
        <form method="POST" enctype="multipart/form-data">
            <label for="image">Content (Image):</label>
            <input type="file" name="image" id="image" accept="image/*" required>

            <label for="description">Description:</label>
            <textarea name="description" id="description" rows="4" required></textarea>

            <label for="price">Price:</label>
            <input type="number" name="price" id="price" min="0" step="0.01" required>

            <button type="submit">Add Post</button>
            <button type="button" ><a href="users.php">Back</a></button>
        </form>
    </div>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $price = mysqli_real_escape_string($conn, $_POST['price']);

        if (isset($_FILES['image'])) {
            $img_name = $_FILES['image']['name'];
            $img_type = $_FILES['image']['type'];
            $tmp_name = $_FILES['image']['tmp_name'];

            $img_explode = explode('.', $img_name);
            $img_ext = end($img_explode);

            $extensions = ["jpeg", "png", "jpg"];
            if (in_array($img_ext, $extensions)) {
                $types = ["image/jpeg", "image/jpg", "image/png"];
                if (in_array($img_type, $types)) {
                    $time = time();
                    $new_img_name = $time . $img_name;

                
                    $upload_dir = "php/Pimages/";
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    if (move_uploaded_file($tmp_name, $upload_dir . $new_img_name)) {
                        $created_at = date('Y-m-d H:i:s');

                        $insert_query = mysqli_query($conn, "INSERT INTO posts (user_id, image, description, price, created_at)
                            VALUES ('{$user_id}', '{$new_img_name}', '{$description}', '{$price}', '{$created_at}')");

                        if ($insert_query) {
                            echo "Post added successfully.";
                            header("location: users.php");
                            exit(); 
                        } else {
                            echo "Something went wrong. Please try again!";
                        }
                    } else {
                        echo "Something went wrong while uploading the image. Please try again!";
                    }
                } else {
                    echo "Please upload an image file - JPEG, PNG, JPG.";
                }
            } else {
                echo "Please upload an image file - JPEG, PNG, JPG.";
            }
        }
    }
    ?>
</body>

</html>
