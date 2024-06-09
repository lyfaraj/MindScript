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

$projects_query = mysqli_query($conn, "SELECT * FROM Upload");
?>

<?php include_once "header.php"; ?>

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
        background-color: #fff;
        color: purple;
        font-family: 'Courier New', monospace;
        text-decoration: none;
        display: flex;
    }

    .header {
        position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 300px;
            background-color: #615C83;
            color: #fff;
            padding: 20px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            text-align: center;
        }

    .settings-content {
        margin-bottom: 12rem;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .settings-content a {
        width: 300px;
        color: #fff;
        text-decoration: none;
        margin-bottom: 10px;
        border-bottom: 1px solid #fff;
        padding-bottom: 15px;
    }

    .profile {
        margin-top: 2rem;
    }

    .profile img {
        width: 50px;
        height: 50px;
        border-radius: 50%;

    }

    .details {
        display: flex;
        flex-direction: column;
    }

    .projects {
    margin-left: 300px;
    width: calc(100% - 320px);
    padding: 40px;
    box-sizing: border-box;
}

.project {
    display: flex;
    margin: 2rem 0;
    padding: 2rem;
    background-color: var(--lavender);
    color: #000;
    border-radius: 30px;
    width: 100%;
    position: relative;
    box-shadow: var(--box-shadow);
}

.project-details {
    flex: 1;
    padding-right: 60px; 
}

    .project img {
        width: 450px;
        height: 200px;
        border-radius: 30px;
        margin-right: 1rem;
        box-shadow: var(--box-shadow);
    }

    .project-details h3 {
        margin: 0;
        font-size: 20px;
        color: var(--dark);
        margin-bottom: 15px;  
    }

    .project-details p {
        font-size: 16px;
        color: #666;
        margin-bottom: 20px;
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
</style>
</head>
<body>
<section class="header">
    <div class="profile">
        <?php
        $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$_SESSION['unique_id']}");
        if (mysqli_num_rows($sql) > 0) {
            $row = mysqli_fetch_assoc($sql);
        }
        ?>
        <img src="php/images/<?php echo $row['img']; ?>" alt="">
        <div class="details">
            <span><?php echo $row['fname'] . " " . $row['lname'] ?></span>
            <p><?php echo $row['status']; ?></p>
        </div>
    </div>
    <div class="settings-content">
        <?php
        $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$_SESSION['unique_id']}");
        if(mysqli_num_rows($sql) > 0){
            $row = mysqli_fetch_assoc($sql);
        }
        ?>
        <a href="account.php">Account</a>
        <a href="projectManager.php">Projects</a>
        <a href="chat-page.php">Chat</a>
        <a href="settings.php">Settings</a>
        <a href="notification.php">Notifications</a>
        <a href="php/logout.php?logout_id=<?php echo $row['unique_id']; ?>" class="logout">Logout</a>
    </div>
</section>

<div class="projects">
    <?php
    $projects_query = mysqli_query($conn, "SELECT * FROM Upload");
    $project_counter = 0;
    if ($projects_query) {
        while ($project_row = mysqli_fetch_assoc($projects_query)) {
            $id = htmlspecialchars($project_row['id']);
            $project_pic = htmlspecialchars($project_row['img']);
            $project_name = htmlspecialchars($project_row['name']);
            $project_category = htmlspecialchars($project_row['Category']);

            if ($project_counter < 20) {
                ?>
                <div class="project">
                    <img src="php/Pimages/<?php echo $project_pic; ?>" alt="Project Image">
                    <div class="project-details">
                        <h3><?php echo $project_name; ?></h3>
                        <p><?php echo $project_row['description']; ?></p>
                        <?php
                        $user_query = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$project_row['user_id']}");
                        if ($user_query && mysqli_num_rows($user_query) > 0) {
                            $user_row = mysqli_fetch_assoc($user_query);
                            $user_name = $user_row['fname'] . " " . $user_row['lname'];
                            ?>
                            <p>Created by: <?php echo $user_name; ?></p>
                            <?php
                        }
                        ?>
                     <a href="viewProject.php?id=<?php echo $id; ?>" class="circular-btn"> 
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    </div>
                </div>
                <?php
                $project_counter++;
            }
        }
    } else {
        echo "Error fetching projects: " . mysqli_error($conn);
    }
    ?>
</div>

</body>
</html>
