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

$user_id = isset($_SESSION['unique_id']) ? $_SESSION['unique_id'] : null;

if ($user_id === null) {
    die('User ID is not set.');
}

$projects_query = mysqli_query($conn, "SELECT * FROM projects WHERE user_id = $user_id");

if (!$projects_query) {
    die('Error in projects query: ' . mysqli_error($conn));
}

$personal_projects_count = 0;
$group_projects_count = 0;

while ($projects_row = mysqli_fetch_assoc($projects_query)) {
    $project_type = $projects_row['type'];
    if ($project_type === 'personal') {
        $personal_projects_count++;
    } else if ($project_type === 'group') {
        $group_projects_count++;
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
    } else {
        $delete_query = "DELETE FROM endTask WHERE task_id = '$task_id' AND user_id = '$user_id'";
        if (mysqli_query($conn, $delete_query)) {
            echo "Task unmarked as finished.";
        } else {
            echo "Error unmarking task as finished: " . mysqli_error($conn);
        }
    }
} else {
    echo "Task ID or finished status is not set.";
}

$project_ids_query = mysqli_query($conn, "SELECT project_id FROM project_members WHERE user_id = $user_id");

if (!$project_ids_query) {
    die('Error in fetching project IDs: ' . mysqli_error($conn));
}

$project_ids = [];
while ($row = mysqli_fetch_assoc($project_ids_query)) {
    $project_ids[] = $row['project_id'];
}

$projects_info = [];
foreach ($project_ids as $project_id) {
    $project_query = mysqli_query($conn, "SELECT * FROM projects WHERE project_id = $project_id AND user_id = $user_id");

    if (!$project_query) {
        die('Error in fetching project details: ' . mysqli_error($conn));
    }

    if (mysqli_num_rows($project_query) > 0) {
        $project_row = mysqli_fetch_assoc($project_query);
        $projects_info[] = $project_row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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

        .counter {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }

        .counter div {
            width: 100px; 
            height: 100px;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            border-radius: 5px;
            color: white;
        }

        .personal {
            background-color: var(--lavender);
            padding: 10px;
            border-radius: 5px;
        }

        .group {
            background-color: var(--pink);
            padding: 10px;
            border-radius: 5px;
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
        .form-container select,
        .form-container textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            margin-bottom: 20px;
            transition: border-color 0.3s ease;
        }

        .form-container select {
            padding-right: 36px;
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

        .form-container input[type="file"] {
            display: none;
        }

        .form-container .file-button {
            display: inline-block;
            width: 85%;
            padding: 8px 16px;
            color: var(--dark);
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
        }

        .projects-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between; 
            gap: 20px;
            overflow-x: auto; 
            padding: 10px;
        }

        .projects {
            display: flex;
            flex-wrap: nowrap;
            gap: 20px; 
        }

        .project {
            flex: 1; 
            width: 220px;
            height: 150px;
            position: relative;
            border-radius: 5px;
            box-shadow: var(--box-shadow);
            color: white;
            margin-bottom: 20px; 
        }

        .project .folder {
            width: 80px;
            height: 20px;
            position: absolute;
            top: -10px;
            left: 10px;
        }

        .project-details {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            text-align: center;
            color: white;
        }

        .project-name {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .project-category {
            font-size: 14px;
        }

        .project-details a {
            text-decoration: none; 
        }

        .project .personal {
            background-color: var(--dark);
        }

        .project .group {
            background-color: var(--pink);
        }

        h3 {
            margin-left: 20px;
        }

        .tasks-container {
            background-color: #fff;
            margin: 20px;
            padding: 10px;
            margin-top: 60px;
            border-radius: 5px;
        }

        .task {
            display: flex;
            justify-content: space-between;
        }

        .task-content {
            flex: 1;
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

        .row {
            margin-left: 320px;
            padding-top: 20px;
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

        a {
            text-decoration: none;
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <a href="users.php" class="back-arrow"><i class="fas fa-arrow-left"></i></a>
            <div class="counter">
                <div class="personal"><?php echo $personal_projects_count;?><br>personal</div>
                <div class="group"><?php echo $group_projects_count;?><br>group</div>
            </div>
            <div class="form-container">
                <form action="addProject.php" method="POST" enctype="multipart/form-data">
                    <label for="name">Project Name:</label>
                    <input type="text" name="name" id="name" required>

                    <label for="type">Project Type:</label>
                    <select name="type" id="type" required>
                        <option value="personal">personal</option>
                        <option value="group">group</option>
                    </select>

                    <div id="memberForm" style="display: none;">
                        <label for="email">Member's Email:</label>
                        <input type="email" name="email" id="email">
                        <i class="fas fa-search" id="checkEmailIcon" style="display: none;"></i>
                    </div>

                    <label for="category">Project Category:</label>
                    <input type="text" name="category" id="category" required>

                    <label for="image">Project Image:</label>
                    <label class="file-button">
                        Choose File
                        <input type="file" name="image" id="image" accept="image/*" required>
                    </label>

                    <button type="submit">Add Project</button>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="projects-container">
                <div class="projects">
                    <?php
                    if ($user_id) {
                        $project_members_query = mysqli_query($conn, "SELECT * FROM project_members WHERE user_id = $user_id");

                        if (!$project_members_query) {
                            die('Error in project members query: ' . mysqli_error($conn));
                        }

                        if (mysqli_num_rows($project_members_query) > 0) {
                            while ($project_member_row = mysqli_fetch_assoc($project_members_query)) {
                                $project_id = $project_member_row['project_id'];

                                $projects_query = mysqli_query($conn, "SELECT * FROM projects WHERE project_id = $project_id");

                                if (!$projects_query) {
                                    die('Error in projects query: ' . mysqli_error($conn));
                                }

                                if (mysqli_num_rows($projects_query) > 0) {
                                    while ($projects_row = mysqli_fetch_assoc($projects_query)) {
                                        $project_name = htmlspecialchars($projects_row['name']);
                                        $project_type = htmlspecialchars($projects_row['type']);
                                        $project_category = htmlspecialchars($projects_row['category']);

                                        $project_link = ($project_type === 'group') ? 'groupProject.php' : 'personalProject.php';
                                        ?>
                                        <div class="project">
                                            <div class="folder" style="background-color: <?php echo ($project_type === 'group') ? 'var(--pink)' : 'var(--dark)'; ?>;"></div> 
                                            <a href="<?php echo $project_link . '?project_id=' . $project_id; ?>">
                                                <div class="project-details <?php echo ($project_type === 'group') ? 'group' : 'personal'; ?>">
                                                    <span class="project-name"><?php echo $project_name; ?></span><br>
                                                    <span class="project-category"><?php echo $project_category; ?></span>
                                                </div>
                                            </a>
                                        </div>
                                        <?php
                                    }
                                } else {
                                    echo "No projects found.";
                                }
                            }
                        } else {
                            echo "You are not a member of any project.";
                        }
                    } else {
                        echo "User ID is not set.";
                    }
                    ?>
                </div>
            </div>

            <div class="tasks-container">
                <h3>Tasks</h3>
                <?php
                    $projects_query = mysqli_query($conn, "SELECT * FROM projects WHERE user_id = $user_id");
                    $tasks_query = mysqli_query($conn, "SELECT * FROM tasks WHERE user_id = $user_id");

                    if (!$projects_query) {
                        die('Error in projects query: ' . mysqli_error($conn));
                    }

                    if (mysqli_num_rows($tasks_query) > 0) {
                        while ($task_row = mysqli_fetch_assoc($tasks_query)) {
                            $task_title = htmlspecialchars($task_row['title']);
                            $task_description = htmlspecialchars($task_row['description']);
                            $task_color = ($project_type === 'group') ? 'var(--pink)' : 'var(--lavender)';
                            ?>
                            <div class="task">
                                <div class="task-content" style="background-color: <?php echo $task_color; ?>">
                                    <div class="task-title"><?php echo $task_title; ?></div>
                                    <div class="task-description"><?php echo $task_description; ?></div>
                                </div>
                                <div class="icons">
                                    <form method="POST" class="task-form">
                                        <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
                                        <input type="hidden" name="title" value="<?php echo $task_title; ?>">
                                        <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                                        <label class="icon finish-checkbox">
                                            <input type="checkbox" name="finished" value="1" class="finish-checkbox" data-task-id="<?php echo $task_id; ?>" style="display: none;">
                                            <i id="check-icon-<?php echo $task_id; ?>" class="fas fa-check-circle"></i>
                                        </label>
                                    </form>
                                    <a href="deleteTask2.php?task_id=<?php echo $task_row['id']; ?>&project_id=<?php echo $project_id; ?>" class="icon delete-icon" onclick="return confirm('Are you sure you want to delete this task?')">
                                        <i class="fas fa-times-circle"></i>
                                    </a>
                                </div>
                            </div>
                        <?php
                        }
                    } else {
                        echo "No tasks to display.";
                    }
                ?>
            </div>
        </div>
    </div>

    <script>
        function confirmTaskDelete(taskId) {
            if (confirm("Are you sure you want to delete this task?")) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "projectManager.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        location.reload();
                    }
                };
                xhr.send("delete_task=" + taskId);
            }
        }

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
                        checkIcon.style.color = 'green'; 
                    } else {
                        checkIcon.style.color = 'initial'; 
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
                    })
                    .catch(error => console.error('Error:', error));
                });
            });
        });

    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        const memberForm = document.getElementById('memberForm');

        typeSelect.addEventListener('change', function() {
            if (typeSelect.value === 'group') {
                memberForm.style.display = 'block';
            } else {
                memberForm.style.display = 'none';
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[name="addProjectForm"]');

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(form);

        fetch('addProject.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            console.log(data);
        })
        .catch(error => console.error('Error:', error));
    });
});

    </script>
</body>
</html>