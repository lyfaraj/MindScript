<?php 
session_start();
include_once "php/config.php";
if(!isset($_SESSION['unique_id'])){
  header("location: login.php");
}
?>
<?php include_once "header.php"; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
          body {
        background: 
        rgb(17,68,96);
      color: #fff;
      font-family: 'Courier New', monospace;
      text-decoration: none;
    }
    
    .changePass-form {
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
    
    .changePass-form h2 {
      margin-bottom: 20px;
    }
    
    .changePass-form label {
      display: block;
      margin-bottom: 10px;
      text-align: left;
    }
    
    .changePass-form input {
      width: 100%;
      padding: 10px;
      border-radius: 5px;
      border: none;
      margin-bottom: 10px;
    }
    
    .changePass-form button {
      background-color: #42a3c3;
      border: none;
      color: #fff;
      padding: 10px 20px;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s;
    }
    
    .changePass-form button:hover {
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
    button a {
  text-decoration: none;
  color: #fff;
}
    </style>
</head>
<body>

<?php
$unique_id = $_SESSION['unique_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = mysqli_real_escape_string($conn, $_POST['current_password']);
    $newPassword = mysqli_real_escape_string($conn, $_POST['new_password']);

    if (!empty($currentPassword)) {
        $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = '{$unique_id}'");
        
        if (mysqli_num_rows($sql) > 0) {
            $row = mysqli_fetch_assoc($sql);
            $user_pass = md5($currentPassword);
            $enc_pass = $row['password'];
            
            if ($user_pass === $enc_pass) {
                if (!empty($newPassword)) {
                    $hashedNewPassword = md5($newPassword);
                    $sql2 = mysqli_query($conn, "UPDATE users SET password = '{$hashedNewPassword}' WHERE unique_id = '{$row['unique_id']}'");
                    if ($sql2) {
                        echo "success";
                    } else {
                        echo "Something went wrong. Please try again!";
                    }
                } else { 
                    echo "New password is required!";
                }
            } else {
                echo "Password is incorrect!";
            }
        }
    } else {
    echo "All input fields are required!";
    }
}
?>

<div class="changePass-form">
    <h2>Change Password</h2>
    <form method="POST">
        <label>Current Password:</label>
        <input type="password" name="current_password" required><br>

        <label>New Password:</label>
        <input type="password" name="new_password" required><br>

        <button>Save</button>
        <button type="button" ><a href="users.php">Back</a></button>
    </form>
</div>
<script src="javascript/pass-show-hide.js"></script>
</body>
</html>