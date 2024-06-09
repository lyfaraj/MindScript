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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'follow') {
    $user_id = $_SESSION['unique_id'];
    $creator_id = $_POST['creator_id'];

    $follow_check_query = $conn->prepare("SELECT * FROM follows WHERE follower_id = ? AND following_id = ?");
    $follow_check_query->bind_param("ss", $user_id, $creator_id);
    $follow_check_query->execute();
    $follow_check_result = $follow_check_query->get_result();

    if ($follow_check_result && $follow_check_result->num_rows > 0) {
        $unfollow_query = $conn->prepare("DELETE FROM follows WHERE follower_id = ? AND following_id = ?");
        $unfollow_query->bind_param("ss", $user_id, $creator_id);
        $unfollow_result = $unfollow_query->execute();

        if ($unfollow_result) {
            echo json_encode(['success' => true, 'message' => 'Unfollowed']);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
    } else {
        $follow_query = $conn->prepare("INSERT INTO follows (follower_id, following_id) VALUES (?, ?)");
        $follow_query->bind_param("ss", $user_id, $creator_id);
        $follow_result = $follow_query->execute();

        $notification_query = $conn->prepare("INSERT INTO notifications (user_id, type, from_user_id) VALUES (?, 'follow', ?)");
        $notification_query->bind_param("ss", $creator_id, $user_id);
        $notification_result = $notification_query->execute();

        if ($follow_result && $notification_result) {
            echo json_encode(['success' => true, 'message' => 'Followed']);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
    }
    exit();
}

$user_id = $_SESSION['unique_id'];
$creator_id = isset($_GET['creator_id']) ? $_GET['creator_id'] : null;

if ($creator_id === null) {
    die('Creator ID is not set.');
}

$sql = $conn->prepare("SELECT * FROM users WHERE unique_id = ?");
$sql->bind_param("s", $creator_id);
$sql->execute();
$result = $sql->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    die('Error fetching user data: ' . $conn->error);
}

$projects_query = $conn->prepare("SELECT COUNT(*) AS project_count FROM Upload WHERE user_id = ?");
$projects_query->bind_param("s", $creator_id);
$projects_query->execute();
$projects_result = $projects_query->get_result();

if ($projects_result) {
    $projects_row = $projects_result->fetch_assoc();
    $project_count = $projects_row['project_count'];
} else {
    $project_count = 0;
    echo 'Error fetching project count: ' . $conn->error;
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
            margin-left: 1rem;
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
                    <div class="buttons">
                        <button class="follow-button" data-creator-id="<?php echo $row['unique_id']; ?>">Follow</button>
                        <button class="view-cv-button" data-creator-id="<?php echo $row['unique_id']; ?>">View CV</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="projects">
            <?php
            if ($creator_id) {
                $projects_query = $conn->prepare("SELECT * FROM Upload WHERE user_id = ?");
                $projects_query->bind_param("s", $creator_id);
                $projects_query->execute();
                $projects_result = $projects_query->get_result();

                if ($projects_result && $projects_result->num_rows > 0) {
                    while ($project_row = $projects_result->fetch_assoc()) {
                        $id = $project_row['id'];
                        $project_content = htmlspecialchars($project_row['img']);
                        $project_name = htmlspecialchars($project_row['name']);
                        $project_price = htmlspecialchars($project_row['price']);
                        $created_at = htmlspecialchars($project_row['created_at']);
                        $project_description = isset($project_row['description']) ? htmlspecialchars($project_row['description']) : '';
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
                                <a href="viewProject.php?id=<?php echo $id; ?>" class="circular-btn">
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
                echo "Creator ID is not set.";
            }
            ?>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".follow-button").forEach(button => {
                button.addEventListener("click", function() {
                    const creatorId = this.getAttribute("data-creator-id");

                    fetch('', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'follow',
                            creator_id: creatorId
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok ' + response.statusText);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            if (data.message === 'Followed') {
                                this.textContent = "Following";
                            } else if (data.message === 'Unfollowed') {
                                this.textContent = "Follow";
                            }
                            this.disabled = true;
                        } else {
                            console.error('Follow failed:', data.error);
                            alert('Follow failed: ' + data.error);
                        }
                    })
                    .catch(error => {
                        console.error('There has been a problem with your fetch operation:', error);
                        alert('There has been a problem with your follow operation. Please try again.');
                    })
                    .finally(() => {
                        this.disabled = false;
                    });
                });
            });

            document.querySelectorAll(".view-cv-button").forEach(button => {
                button.addEventListener("click", function() {
                    const creatorId = this.getAttribute("data-creator-id");
                    window.location.href = 'download_cv.php?creator_id=' + creatorId;
                });
            });
        });

    </script>
</body>
</html>
