<?php
session_start();
include_once "php/config.php";

if (!isset($_SESSION['unique_id'])) {
    header("location: login.php");
    exit(); 
}

$user_id = isset($_SESSION['unique_id']) ? $_SESSION['unique_id'] : null;

if ($user_id === null) {
    die('User ID is not set.');
}

$sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$user_id}");

if ($sql && mysqli_num_rows($sql) > 0) {
    $row = mysqli_fetch_assoc($sql);
} else {
    die('Error fetching user data: ' . mysqli_error($conn));
}

$projects_query = mysqli_query($conn, "SELECT COUNT(*) AS project_count FROM Upload WHERE user_id = $user_id");

if ($projects_query) {
    $projects_row = mysqli_fetch_assoc($projects_query);
    $project_count = $projects_row['project_count'];
} else {
    $project_count = 0;
    echo 'Error fetching project count: ' . mysqli_error($conn);
}

if (isset($_POST['delete_project'])) {
    $project_id_to_delete = mysqli_real_escape_string($conn, $_POST['delete_project']);
    $delete_query = mysqli_query($conn, "DELETE FROM projects WHERE project_id = {$project_id_to_delete}");

    if ($delete_query) {
        header("Refresh:0");
        exit();
    } else {
        echo "Error deleting project: " . mysqli_error($conn);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_account'])) {
    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $lname = mysqli_real_escape_string($conn, $_POST['lname']);
    if (!empty($fname) && !empty($lname)) {
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
                    if (move_uploaded_file($tmp_name, "php/images/" . $new_img_name)) {
                        $update_query = "UPDATE users SET fname = '$fname', lname = '$lname', img = '$new_img_name' WHERE unique_id = '{$_SESSION['unique_id']}'";
                        $update_result = mysqli_query($conn, $update_query);
                        if ($update_result) {
                            echo "Profile updated successfully!";
                            header("location: account.php");
                        } else {
                            echo "Error updating profile: " . mysqli_error($conn);
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
}

$select_query = "SELECT fname, lname, img FROM users WHERE unique_id = '{$_SESSION['unique_id']}'";
$result = mysqli_query($conn, $select_query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
} else {
    echo "Error fetching user details: " . mysqli_error($conn);
}

$followers_query = $conn->prepare("SELECT COUNT(*) AS follower_count FROM follows WHERE following_id = ?");
$followers_query->bind_param("s", $creator_id);
$followers_query->execute();
$followers_result = $followers_query->get_result();

if ($followers_result) {
    $followers_row = $followers_result->fetch_assoc();
    $follower_count = $followers_row['follower_count'];
} else {
    $follower_count = 0;
    echo 'Error fetching follower count: ' . $conn->error;
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Account Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <style>
        :root {
            --dark: #615C83;
            --pink: #F0AEB6;
            --lavender: #D0CADB;
            --box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
        }

        body {
            font-family: 'Courier New', monospace;
            text-decoration: none;
        }

        .profile {
            background-color: var(--dark);
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            box-shadow: var(--box-shadow);
            border-radius: 10px;
            position: relative;
        }

        .back-arrow {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            font-size: 1.5em;
        }

        .profile-content {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }

        .details {
            text-align: center;
            margin-right: 20px;
        }

        .details img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: var(--box-shadow);
        }

        .details span {
            font-size: 1.8em;
            font-weight: bold;
            color: #fff;
            display: block;
            margin-top: 10px;
        }

        .edit-button {
            background-color: #fff;
            color: var(--dark);
            padding: 12px 24px;
            border-radius: 6px;
            border: none;
            font-size: 1.2em;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .edit-button:hover {
            background-color: #ccc;
            color: var(--dark);
        }

        .content {
            color: #fff;
            font-size: 1.2em;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .project-and-follower-counters {
            display: flex;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .project-counter,
        .follower-counter {
            font-size: 1rem;
            color: #fff;
            text-align: center;
            margin-bottom: 10px;
            margin-right: 1rem;
        }

        button {
            background: none;
            border: none;
            padding: 1em 2em;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
            margin-left: 10px;
            background-color: #fff;
            box-shadow: var(--box-shadow);
        }

        button a {
            text-decoration: none;
        }

        .projects {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: flex-start;
            gap: 60px;
        }

        .project {
            color: black;
            background: var(--lavender); 
            box-shadow: var(--box-shadow);
            border-radius: 10px;
            width: 500px;
            margin-bottom: 20px;
            overflow: hidden; 
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
            position: relative; 
        }

        .project img {
            width: 100%;
            height: 180px;
            object-fit: cover; 
        }

        .date {
            position: absolute;
            top: 160px;
            left: 20px;
            background: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            box-shadow: var(--box-shadow);
        }

        .date span {
            font-size: 0.9em;
            font-weight: bold;
            color: #333;
        }

        .project-info {
            padding: 15px; 
            display: flex;
            flex-direction: column;
            align-items: flex-start; 
        }

        .project p {
            text-align: left;
            margin: 10px 0; 
            color: #333; 
            font-size: 1em;
        }

        .project-details {
            width: 100%;
            margin-bottom: 10px;
        }

        .project-details span {
            display: block;
            margin-bottom: 5px;
            color: #666;
        }

        .circular-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: var(--dark);
            color: #fff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: var(--box-shadow);
            transition: background-color 0.3s;
        }

        .circular-btn i {
            font-size: 1.2em;
        }

        .project:hover {
            transform: scale(1.02);
            box-shadow: var(--box-shadow);
        }

        .edit-account-form {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: var(--box-shadow);
            z-index: 999;
            width: 90%;
            max-width: 400px;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translate(-50%, -55%);
            }
            100% {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
        }

        .edit-account-form h2 {
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            font-weight: 600;
            text-align: center;
            color: var(--dark);
        }

        .edit-account-form img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1.2rem;
            margin-left: auto;
            margin-right: auto;
            display: block;
            box-shadow: var(--box-shadow);
        }

        .edit-account-form label {
            display: block;
            margin-bottom: 0.8rem;
            font-weight: 500;
            color: var(--dark);
        }

        .edit-account-form input {
            width: 95%;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-bottom: 1.2rem;
            font-size: 0.95rem;
            transition: border-color 0.3s ease;
        }

        .edit-account-form input:focus {
            outline: none;
            border-color: var(--dark);
        }

        .edit-account-form .button-container {
            display: flex;
            justify-content: center;
            margin-top: 1.5rem;
        }

        .edit-account-form .close-button {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            font-size: 1.5rem;
            color: #999;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .edit-account-form button {
            background-color: var(--dark);
            border: none;
            color: #fff;
            padding: 12px 34px;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin: 0 0.5rem;
        }

        .edit-account-form button:hover {
            background-color: var(--lavender);
        }

        .edit-account-form button a {
            color: var(--dark);
            text-decoration: none;
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
            display: block; 
            margin-bottom: 1.2rem; 
            width: 100%; 
        }

        .file-input-container input[type="file"] {
            font-size: 100px;
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
            height: 100%;
            width: 100%;
            z-index: 999;
        }

        .file-input-container button {
            background-color: var(--dark);
            border: 1px solid #ccc; 
            color: #fff;
            width: 100%; 
            padding: 12px;
            border-radius: 6px;
            font-size: 0.95rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            box-shadow: none;
        }
    </style>
</head>
<body>
    <div class="account-page">
        <div class="profile">
            <a href="users.php" class="back-arrow"><i class="fas fa-arrow-left"></i></a>
            <div class="profile-content">
                <div class="details">
                    <img src="php/images/<?php echo htmlspecialchars($row['img']); ?>" alt="">
                    <span><?php echo htmlspecialchars($row['fname']); ?></span>
                </div>
                <div class="content">
                    <div class="project-and-follower-counters">
                        <div class="project-counter">
                        <?php echo $project_count; ?> <br> posts
                        </div>
                        <div class="follower-counter">
                        <?php echo $follower_count; ?> <br> followers
                        </div>
                    </div>
                    <button class="edit-button">Edit Profile</button>
                </div>
            </div>
         </div>
         
         <div class="projects">
    <?php
    if ($user_id) {
        $projects_query = mysqli_query($conn, "SELECT * FROM Upload WHERE user_id = $user_id");

        if (!$projects_query) {
            die('Error in projects query: ' . mysqli_error($conn));
        }

        if (mysqli_num_rows($projects_query) > 0) {
            while ($project_row = mysqli_fetch_assoc($projects_query)) {
                $id = $project_row['id'];
                $project_content = htmlspecialchars($project_row['img']);
                $project_name = htmlspecialchars($project_row['name']);
                $project_price = htmlspecialchars($project_row['price']);
                $created_at = htmlspecialchars($project_row['created_at']);
    ?>
                <div class="project">
                    <img src="php/Pimages/<?php echo $project_content; ?>" alt="Project Image">
                    <div class="date">
                        <span><?php echo date('d M', strtotime($created_at)); ?></span>
                    </div>
                    <div class="project-info">
                        <p><?php echo $project_description; ?></p>
                        <div class="project-details">
                            <span class="project-name"><?php echo $project_name; ?></span>
                            <span class="project-price">price: <?php echo $project_price; ?></span>
                        </div>
                    
                     <a href="viewMyProject.php?id=<?php echo $id; ?>" class="circular-btn">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    </div>
                </div>
    <?php
            }
        } else {
            echo "No projects to display.";
        }
    } else {
        echo "User ID is not set.";
    }
    ?>
</div>

    </div>

    <div class="edit-account-form" style="display: none;">
        <div class="close-button">&times;</div>
        <form method="POST" enctype="multipart/form-data">
            <img src="php/images/<?php echo $row['img']; ?>" alt="">
            <div class="file-input-container">
                <input type="file" name="image" id="photo" accept="image/*">
                <button type="button">Select Image</button>
            </div>
            <label>First Name:</label>
            <input type="text" name="fname" id="fname" value="<?php echo $row['fname']; ?>" required><br>
            <label>Last Name:</label>
            <input type="text" name="lname" id="lname" value="<?php echo $row['lname']; ?>" required><br>
            <div class="button-container">
                <button type="submit" name="edit_account">Save</button>
            </div>
        </form>
    </div>

    <script>
        const editButton = document.querySelector(".edit-button");
        const editAccountForm = document.querySelector(".edit-account-form");

        editButton.addEventListener("click", () => {
            editAccountForm.style.display = "block";
        });

        editAccountForm.querySelector(".close-button").addEventListener("click", () => {
            editAccountForm.style.display = "none";
        });
    </script>
</body>
</html>

