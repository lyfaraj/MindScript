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
$project_id = isset($_GET['project_id']) ? $_GET['project_id'] : null;

if ($project_id === null) {
    die('Project ID is not set.');
}

$project_query = mysqli_query($conn, "SELECT * FROM projects WHERE project_id = $project_id AND user_id = $user_id");
if (!$project_query) {
    die('Error in project query: ' . mysqli_error($conn));
}
$project_row = mysqli_fetch_assoc($project_query);
if (!$project_row) {
    die('Project not found.');
}

$project_pic = htmlspecialchars($project_row['pic']);
$project_name = htmlspecialchars($project_row['name']);
$project_type = htmlspecialchars($project_row['type']);
$project_category = htmlspecialchars($project_row['category']);

$tasks_query = mysqli_query($conn, "SELECT * FROM tasks WHERE project_id = $project_id");
if (!$tasks_query) {
    die('Error in tasks query: ' . mysqli_error($conn));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['upload_project'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $tools = mysqli_real_escape_string($conn, $_POST['tools']);
        $price = mysqli_real_escape_string($conn, $_POST['price']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $created_at = date('Y-m-d');

        $image_uploaded = false;
        $zip_uploaded = false;
        $new_img_name = '';
        $new_zip_name = '';

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
                        $image_uploaded = true;
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

        if (isset($_FILES['zip_file'])) {
            $zip_name = $_FILES['zip_file']['name'];
            $zip_type = $_FILES['zip_file']['type'];
            $tmp_name = $_FILES['zip_file']['tmp_name'];

            $zip_explode = explode('.', $zip_name);
            $zip_ext = end($zip_explode);

            if ($zip_ext === "zip") {
                $time = time();
                $new_zip_name = $time . $zip_name;

                $upload_dir = "php/Pzips/";
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                if (move_uploaded_file($tmp_name, $upload_dir . $new_zip_name)) {
                    $zip_uploaded = true;
                } else {
                    echo "Something went wrong while uploading the zip file. Please try again!";
                }
            } else {
                echo "Please upload a zip file.";
            }
        }

        if ($image_uploaded && $zip_uploaded) {
            $insert_query = mysqli_query($conn, "INSERT INTO Upload (user_id, project_id, Category, name, description, tools, price, img, zip, created_at)
                VALUES ('$user_id', '$project_id', '$category', '$name', '$description', '$tools', '$price', '$new_img_name', '$new_zip_name', '$created_at')");

            if ($insert_query) {
                echo "Post added successfully.";
                header("location: users.php");
                exit();
            } else {
                echo "Something went wrong. Please try again!";
            }
        } else {
            echo "Both image and zip file are required.";
        }
    }

    if (isset($_POST['task_id']) && isset($_POST['finished'])) {
        $task_id = mysqli_real_escape_string($conn, $_POST['task_id']);
        $finished = mysqli_real_escape_string($conn, $_POST['finished']);
        $task_title = mysqli_real_escape_string($conn, $_POST['title']);
        $project_id = mysqli_real_escape_string($conn, $_POST['project_id']);

        if ($finished == 1) {
            $insert_query = "INSERT INTO endTask (task_id, user_id, project_id, name) VALUES ('$task_id', '$user_id', '$project_id', '$task_title')";
            if (mysqli_query($conn, $insert_query)) {
                echo "Task marked as finished.";
            } else {
                echo "Error marking task as finished: " . mysqli_error($conn);
            }
        } else {               $delete_query = "DELETE FROM endTask WHERE task_id = '$task_id' AND user_id = '$user_id'";
            if (mysqli_query($conn, $delete_query)) {
                echo "Task unmarked as finished.";
            } else {
                echo "Error unmarking task as finished: " . mysqli_error($conn);
            }
        }
    } else {
        echo "Task ID or finished status is not set.";
    }
}

$finished_tasks_query = mysqli_query($conn, "SELECT COUNT(*) as finished_tasks FROM endTask WHERE project_id = $project_id AND user_id = $user_id");
$finished_tasks_row = mysqli_fetch_assoc($finished_tasks_query);
$finished_tasks = $finished_tasks_row['finished_tasks'];

$total_tasks_query = mysqli_query($conn, "SELECT COUNT(*) as total_tasks FROM tasks WHERE project_id = $project_id");
$total_tasks_row = mysqli_fetch_assoc($total_tasks_query);
$total_tasks = $total_tasks_row['total_tasks'];
?>  

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <style>
        :root {
            --dark: #615C83;
            --pink: #F0AEB6;
            --lavender: #D0CADB;
            --box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
        }

        body {
            background-color: var(--lavender);
            margin: 0;
            padding: 0;
        }

        .container {
            display: flex;
            flex-direction: column;
        }

        .info {
            margin-left: 320px;
            padding-top: 20px;
        }

        .project-info {
            display: flex;
            align-items: center;
            justify-content: space-between; 
            margin: 20px;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: var(--box-shadow);
        }

        .project-info img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-right: 20px;
            margin-left: 30px;
        }

        .project-info h2 {
            margin: 0;
            font-size: 30px;
            color: var(--dark);
        }

        .project-info p {
            margin: 0;
            font-size: 24px;
            color: var(--dark);
        }

        .tasks-container {
            background-color: #fff;
            margin: 20px;
            padding: 10px;
            margin-top: 20px;
            border-radius: 15px;
        }

        .task {
            display: flex;
            justify-content: space-between;
        }

        .task-content {
            flex: 1;
            background-color: var(--lavender);
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 15px;
            box-shadow: var(--box-shadow);
            margin-left: 30px;
        }

        .task-title {
            font-weight: bold;
        }

        .task-description {
            margin-top: 5px;
        }

        h2 {
            margin-left: 20px;
        }

        .task h4 {
            margin-top: 0;
            margin-bottom: 5px;
        }

        .task p {
            margin: 0;
        }

        .icons {
            margin: 10px;
            display: flex;
            align-items: center;
            margin-top: 10px;
        }

        .icons .icon {
            margin: 10px;
            margin-right: 20px;
            color: var(--dark);
            text-decoration: none;
            font-size: 20px;
        }

        .icons button{
            background-color: #fff;
            border: none;
        }

        .sidebar {
            background-color: var(--dark);
            padding: 20px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: fixed;
            left: 0;
            top: 0;
            width: 300px;
            height: 100vh;
            overflow-y: auto;
        }
        .chart canvas {
            max-width: 100%;
            height: auto;
        }

        .form-container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 500px;
            margin: 0 auto;
        }

        .form-container label {
            display: block;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--dark);
        }

        .form-container input,
        .form-container textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            margin-bottom: 20px;
            transition: border-color 0.3s ease;
        }

        .form-container input:focus,
        .form-container textarea:focus {
            border-color: var(--dark);
            outline: none;
        }

        .form-container button[type="submit"] {
            background-color: var(--dark);
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: block;
            margin: 0 auto;
        }

        button[type="submit"] {
            background-color: var(--dark);
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: block;
            margin: 0 auto;
        }

        img {
            max-width: 100%;
            height: auto;
            margin-bottom: 10px;
        }

        .back-arrow {
            position: absolute;
            left: 20px;
            top: 30px; 
            transform: translateY(-50%);
            color: white;
            font-size: 1.5em;
            z-index: 1; 
        }

        .edit-project-form{
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
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

        .edit-project-form h2 {
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            font-weight: 600;
            text-align: center;
            color: var(--dark);
        }

        .edit-project-form img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1.2rem;
            margin-left: auto;
            margin-right: auto;
            display: block;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .edit-project-form label {
            display: block;
            margin-bottom: 0.8rem;
            font-weight: 500;
            color: var(--dark);
        }

        .edit-project-form input {
            width: 95%;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-bottom: 1.2rem;
            font-size: 0.95rem;
            transition: border-color 0.3s ease;
        }

        .edit-project-form input:focus {
            outline: none;
            border-color: var(--dark);
        }

        #upload-form-container {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            z-index: 999;
            width: 80%;
            max-width: 600px;
            animation: fadeIn 0.5s ease;
        }

        #upload-form-container form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        #upload-form-container label {
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        #upload-form-container input,
        #upload-form-container textarea {
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        #upload-form-container textarea {
            resize: vertical;
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
            background-color: #fff
            border: 1px solid #ccc; 
            color: var(--dark);
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

    <div class="container">
        <div class="sidebar">
           <a href="projectManager.php" class="back-arrow"><i class="fas fa-arrow-left"></i></a>
            <div class="chart">
               <canvas id="myChart"></canvas>
            </div>
            <div class="form-container">
                <form method="POST" enctype="multipart/form-data" action="addTask2.php?project_id=<?php echo $project_id; ?>"> 
                    <label for="title">Title:</label>
                    <input type="text" name="title" id="title" required>

                    <label for="time">Time:</label>
                    <input type="text" name="time" id="time" required>

                    <label for="description">Description:</label>
                    <textarea name="description" id="description" rows="4" required></textarea>

                    <button type="submit">Add Task</button>
                </form>
            </div>
        </div>

        <div class="info">
            <div class="project-info">
                <img src="Pimages/<?php echo $project_pic; ?>" alt="Project Image">
                <h2><?php echo $project_name; ?></h2>
                <p>Type: <?php echo $project_type; ?></p>
                <p>Category: <?php echo $project_category; ?></p>
                <div class="icons">
                    <a href="compiler.html" class="icon compiler-icon"><i class="fas fa-code"></i></a>
                    <button class="icon edit-button"><i class="fas fa-edit"></i></button>
                    <button class="icon upload-button"><i class="fas fa-upload"></i></button>
                    <a href="deleteProject.php?project_id=<?php echo $project_id; ?>" class="icon delete-icon" onclick="return confirmDelete('<?php echo $project_row['project_id']; ?>')">
                       <i class="fas fa-trash"></i>
                    </a>
                </div>
            </div>
            <div class="tasks-container">
                <h2>tasks</h2>
                <?php 
                while ($task_row = mysqli_fetch_assoc($tasks_query)) { 
                    $task_id = htmlspecialchars($task_row['id']);
                    $task_title = htmlspecialchars($task_row['title']);
                    $task_time = htmlspecialchars($task_row['time']);
                    $task_description = htmlspecialchars($task_row['description']);
                ?>
                <div class="task">
                    <div class="task-content">
                        <h4><?php echo $task_title; ?></h4>
                        <p><?php echo $task_time; ?> days</p>
                        <p><?php echo $task_description; ?></p>
                    </div>
                    <div class="icons">
                        <form method="POST" class="task-form">
                            <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
                            <input type="hidden" name="title" value="<?php echo $task_title; ?>">
                            <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                            <?php
                                $finished_check_query = $conn->prepare("SELECT * FROM endTask WHERE task_id = ?");
                                $finished_check_query->bind_param("s", $task_id);
                                $finished_check_query->execute();
                                $finished_check_result = $finished_check_query->get_result();

                                if ($finished_check_result && $finished_check_result->num_rows > 0) {
                                    $button_text = "Finished";
                                    $icon_color = "#F0AEB6";
                                    $checked = true;
                                } else {
                                    $button_text = "Unfinished";
                                    $icon_color = "#615C83";
                                    $checked = false; 
                                }
                            ?>
                            <label class="icon finish-checkbox">
                                <input type="checkbox" name="finished" value="1" class="finish-checkbox" data-task-id="<?php echo $task_id; ?>" style="display: none;" <?php if ($checked) echo 'checked'; ?>>
                                <i id="check-icon-<?php echo $task_id; ?>" class="fas fa-check-circle" style="color: <?php echo $icon_color; ?>"></i>
                            </label>
                        </form>
                        <a href="deleteTask.php?task_id=<?php echo $task_row['id']; ?>&project_id=<?php echo $project_id; ?>" class="icon delete-icon" onclick="return confirm('Are you sure you want to delete this task?')">
                            <i class="fas fa-times-circle"></i>
                        </a>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="edit-project-form" style="display:none;">
        <div class="close-button">&times;</div>
        <form method="POST" action="editProject.php" enctype="multipart/form-data">
            <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">

            <img src="Pimages/<?php echo $project_pic; ?>" alt="">
            <div class="file-input-container">
                <input type="file" name="image" id="photo" accept="image/*">
                <button>Choose File</button>
            </div><br>

            <label>Name:</label>
            <input type="text" name="name" id="name" value="<?php echo $project_name; ?>" required><br>

            <label>Category:</label>
            <input type="text" name="category" id="category" value="<?php echo $project_category; ?>" required><br>

            <button type="submit">Save</button>
        </form>
    </div>

    <div id="upload-form-container" style="display:none;">
    <div class="close">&times;</div>
    <form method="POST" enctype="multipart/form-data">
        <div class="file-input-container">
            <input type="file" name="image" id="photo" accept="image/*">
            <button>Choose File</button>
        </div><br>

        <label>Name:</label>
        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($project_row['name']); ?>" required><br>

        <label>Category:</label>
        <input type="text" name="category" id="category" value="<?php echo htmlspecialchars($project_row['category']); ?>" required><br>

        <label>Zip Folder:</label>
        <div class="file-input-container">
            <input type="file" name="zip_file" id="zip_file" accept=".zip" required>
            <button type="button">Choose File</button>
        </div><br>
        
        <label>Tools:</label>
        <input type="text" name="tools" id="tools" required><br>

        <label>Price:</label>
        <input type="number" name="price" id="price" required><br>

        <label>Description:</label>
        <textarea name="description" id="description" rows="4" required></textarea><br>

        <button type="submit" name="upload_project">Publish</button>
    </form>
</div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function confirmDelete(projectId) {
            if (confirm("Are you sure you want to delete this project?")) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "projectManager.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                // xhr.onreadystatechange = function () {
                //     if (xhr.readyState == 4 && xhr.status == 200) {
                //         location.reload();
                //     }
                // };
                xhr.send("delete_project=" + projectId);
            }
        }

        function confirmTaskDelete(taskId) {
            if (confirm("Are you sure you want to delete this task?")) {
                var xhr = new XMLHttpRequest();
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        location.reload();
                    }
                };
                xhr.send("delete_task=" + taskId);
            }
        }

        function openUploadForm() {
            var formContainer = document.getElementById('upload-form-container');
            formContainer.style.display = 'block';
        }

        const editButton = document.querySelector(".edit-button");
        const editProjectForm = document.querySelector(".edit-project-form");
        const uploadButton = document.querySelector(".upload-button");
        const uploadFormContainer = document.getElementById("upload-form-container");

        editButton.addEventListener("click", () => {
            editProjectForm.style.display = "block";
        });

        editProjectForm.querySelector(".close-button").addEventListener("click", () => {
            editProjectForm.style.display = "none";
        });

        uploadButton.addEventListener("click", () => {
            uploadFormContainer.style.display = "block";
        });

        uploadFormContainer.querySelector(".close").addEventListener("click", () => {
            uploadFormContainer.style.display = "none";
        });

        document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.finish-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const taskId = this.dataset.taskId;
            const finished = this.checked ? 1 : 0;
            const form = this.closest('form');
            const formData = new FormData(form);
            const checked = this.checked;
            const checkIcon = document.getElementById('check-icon-' + taskId);

            if (checked) {
                checkIcon.style.color = '#F0AEB6';
            } else {
                checkIcon.style.color = '#615C83';
            }

            formData.append('task_id', taskId);
            formData.append('finished', finished);

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                console.log(data);
                location.reload(); // Reload the page
            })
            .catch(error => console.error('Error:', error));
        });
    });
});

        var ctx = document.getElementById('myChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Finished Tasks', 'Remaining Tasks'],
                datasets: [{
                    label: '# of Tasks',
                    data: [<?php echo $finished_tasks; ?>, <?php echo $total_tasks - $finished_tasks; ?>],
                    backgroundColor: [
                        '#F0AEB6',
                        '#D0CADB'
                    ],
                    borderColor: [
                        '#615C83',
                        '#615C83'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Project Progress',
                        font: {
                            size: 24,
                            weight: 'bold'
                        },
                        color: '#fff'
                    },
                    legend: {
                        labels: {
                            font: {
                                size: 18,
                                weight: 'bold'
                            },
                            color: '#fff'
                        }
                    }
                },
                cutout: '60%',
                animation: {
                    animateScale: true,
                    animateRotate: true
                },
                layout: {
                    padding: {
                        top: 20,
                        bottom: 20
                    }
                }
            }
        });
</script>

</body>
</html>