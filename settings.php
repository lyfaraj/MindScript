<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include_once "php/config.php";
if(!isset($_SESSION['unique_id'])){
  header("location: login.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['upload_cv'])) {
        if (isset($_FILES['cv_file'])) {
            $file_name = $_FILES['cv_file']['name'];
            $file_tmp = $_FILES['cv_file']['tmp_name'];
            $file_type = $_FILES['cv_file']['type'];

            if ($file_type == "application/pdf") {
                $file_content = file_get_contents($file_tmp);
                $file_content = mysqli_real_escape_string($conn, $file_content);

                $insert_query = "INSERT INTO cv_files (user_id, file_name, file_content) VALUES ('{$_SESSION['unique_id']}', '$file_name', '$file_content')";
                $insert_result = mysqli_query($conn, $insert_query);

                if ($insert_result) {
                    echo "<div class='success-message'>CV file uploaded successfully!</div>";
                } else {
                    echo "<div class='error-message'>Error uploading CV file: " . mysqli_error($conn) . "</div>";
                }
            } else {
                echo "<div class='error-message'>Invalid file type. Please upload a PDF file.</div>";
            }
        }
    } else {
        $gender = mysqli_real_escape_string($conn, $_POST['gender']);
        $dob = mysqli_real_escape_string($conn, $_POST['dob']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $creditCard = mysqli_real_escape_string($conn, $_POST['creditCard']);
        $country = mysqli_real_escape_string($conn, $_POST['country']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);

        $update_query = "UPDATE users SET gender = '$gender', dob = '$dob', password = '$password', email = '$email', creditCard = '$creditCard', country = '$country', phoneNb = '$phone', address = '$address' WHERE unique_id = '{$_SESSION['unique_id']}'";
        $update_result = mysqli_query($conn, $update_query);

        if ($update_result) {
            echo "<div class='success-message'>Account details updated successfully!</div>";
        } else {
            echo "<div class='error-message'>Error updating account details: " . mysqli_error($conn) . "</div>";
        }
    }
}

$project_query = "SELECT COUNT(*) AS project_count FROM Upload WHERE user_id = '{$_SESSION['unique_id']}'";
$project_result = mysqli_query($conn, $project_query);
$project_count = 0;
if ($project_result && mysqli_num_rows($project_result) > 0) {
    $project_row = mysqli_fetch_assoc($project_result);
    $project_count = $project_row['project_count'];
}

$likes_query = "SELECT COUNT(*) AS likes_count FROM likes WHERE user_id = '{$_SESSION['unique_id']}'";
$likes_result = mysqli_query($conn, $likes_query);
$likes_count = 0;
if ($likes_result && mysqli_num_rows($likes_result) > 0) {
    $likes_row = mysqli_fetch_assoc($likes_result);
    $likes_count = $likes_row['likes_count'];
}

$follows_query = "SELECT COUNT(*) AS follows_count FROM follows WHERE following_id = '{$_SESSION['unique_id']}'";
$follows_result = mysqli_query($conn, $follows_query);
$follows_count = 0;
if ($follows_result && mysqli_num_rows($follows_result) > 0) {
    $follows_row = mysqli_fetch_assoc($follows_result);
    $follows_count = $follows_row['follows_count'];
}

$sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$_SESSION['unique_id']}");
if (mysqli_num_rows($sql) > 0) {
    $row = mysqli_fetch_assoc($sql);
}
?>


<!DOCTYPE html>
<html>
<head>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
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
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
}

.header {
    background-color: var(--dark);
    height: 170px;
    position: relative;
    z-index: 1;
}

.header .back-arrow {
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: white;
    font-size: 1.5em;
}

.forms-container {
    display: flex;
    max-width: 1200px;
    height: 600px;
    margin: -50px auto;
    gap: 50px;
    z-index: 2;
    position: relative;
}

.edit-account-form {
    display: flex;
    flex-wrap: wrap;
    flex: 2;
    padding: 2rem;
    background: #fff;
    border-radius: 10px;
    justify-content: center;
    align-items: center;
    box-shadow: var(--box-shadow);
}

.edit-account-form input, select {
    width: 250px;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    transition: border-color 0.3s ease;
}

.cv-form {
    flex: 1;
    padding: 2rem;
    background: #fff;
    border-radius: 10px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    box-shadow: var(--box-shadow);
    gap: 30px;
}

.cv-form .profile {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.cv-form .cv {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
}

.cv-form .cv form {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.cv-form .cv form button {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    margin-top: 0;
}

.row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.column {
    margin: 15px;
    flex-basis: calc(50% - 20px);
}

label {
    display: block;
    margin-bottom: 5px;
    color: var(--dark);
    font-weight: bold;
}

a {
    text-decoration: none;
    color: #fff;
    transition: color 0.3s ease;
}

.profile img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 20px;
}

.profile .details span {
    font-size: 24px;
    font-weight: bold;
    color: var(--dark);
}

.btn-group {
    display: flex;
    gap: 10px;
    margin-top: 10px;
    justify-content: center;
    align-items: center;
}

button {
    background-color: var(--dark);
    border: none;
    color: #fff;
    padding: 12px 24px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: var(--pink);
}

button.logout a,
button.delete a {
    color: #fff;
}

button.logout a:hover,
button.delete a:hover {
    color: #ddd;
}

.success-message,
.error-message {
    margin-top: 10px;
    padding: 10px;
    border-radius: 5px;
}

.success-message {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.error-message {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.cv-form .cv form input[type="file"] {
    display: none;
}

.cv-form .cv form label {
    background-color: var(--dark);
    color: #fff;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.cv-form .cv form label:hover {
    background-color: var(--pink);
}

.cv-form .cv form button {
    margin-top: 0;
    background-color: var(--dark);
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.cv-form .cv form button:hover {
    background-color: var(--pink);
}
</style>
</head>
<body>
<div class="header">
    <a href="users.php" class="back-arrow"><i class="fas fa-arrow-left"></i></a>
</div>

<div class="forms-container">
    <div class="cv-form">
        <div class="profile">
            <img src="php/images/<?php echo $row['img']; ?>" alt="">
            <div class="details">
                <span><?php echo $row['fname'] . " " . $row['lname']; ?></span>
            </div>
        </div>
        <div class="cv">
            <form method="POST" enctype="multipart/form-data">
                <label for="cv_file">Choose CV</label>
                <input type="file" id="cv_file" name="cv_file" accept=".pdf" onchange="this.form.submit();">
                <input type="hidden" name="upload_cv" value="1">
            </form>
            <div class="chart">
                <canvas id="uploadChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <div class="edit-account-form">
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="column">
                    <label>Gender:</label>
                    <select name="gender" id="gender" required>
                        <option value="male" <?php echo ($row['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo ($row['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
                <div class="column">
                    <label>Date of Birth:</label>
                    <input type="date" name="dob" id="dob" value="<?php echo $row['dob']; ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="column">
                    <label>Password:</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <div class="column">
                    <label>Email:</label>
                    <input type="email" name="email" id="email" value="<?php echo $row['email']; ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="column">
                    <label>Credit Card:</label>
                    <input type="text" name="creditCard" id="creditCard" value="<?php echo $row['creditCard']; ?>">
                </div>
                <div class="column">
                    <label>Country:</label>
                    <input type="text" name="country" id="country" value="<?php echo $row['country']; ?>">
                </div>
            </div>

            <div class="row">
                <div class="column">
                    <label>Phone Number:</label>
                    <input type="text" name="phone" id="phone" value="<?php echo $row['phoneNb']; ?>">
                </div>
                <div class="column">
                    <label>Address:</label>
                    <input type="text" name="address" id="address" value="<?php echo $row['address']; ?>">
                </div>
            </div>

            <div class="btn-group">
                <button type="submit">Edit</button>
                <button type="submit" name="logout">
                    <a href="php/logout.php?logout_id=<?php echo $row['unique_id']; ?>" class="logout">Logout</a>
                </button>
                <button type="button" class="delete" onclick="confirmDelete('<?php echo $row['unique_id']; ?>')">
                    Delete
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function confirmDelete(userId) {
        const userConfirmed = confirm("Are you sure you want to delete your account?");
        if (userConfirmed) {
            window.location.href = `php/delete_account.php?user_id=${userId}`;
        } else {
            console.log("Deletion canceled by user.");
        }
    }

    function updateChart() {
    const likesCount = <?php echo $likes_count; ?>;
    const followsCount = <?php echo $follows_count; ?>;
    const projectsCount = <?php echo $project_count; ?>; 

    const ctx = document.getElementById('uploadChart').getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Likes', 'Follows', 'Projects'],
            datasets: [{
                label: 'Likes',
                data: [likesCount, 0, 0],
                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }, {
                label: 'Follows',
                data: [0, followsCount, 0],
                backgroundColor: 'rgba(208, 202, 219, 1)',
                borderColor: 'rgba(97, 92, 131, 1)',
                borderWidth: 1
            }, {
                label: 'Projects',
                data: [0, 0, projectsCount],
                backgroundColor: 'rgba(201, 172, 189, 0.5)', 
                borderColor: 'rgba(201, 172, 189, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });
}
updateChart();
</script>
</body>
</html>




