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
    <title>Change Email</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
 body {
        background: 
        rgb(17,68,96);
      color: #fff;
      font-family: 'Courier New', monospace;
      text-decoration: none;
    }
    
    .changeEmail-form {
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
    
    .changeEmail-form h2 {
      margin-bottom: 20px;
    }
    
    .changeEmail-form label {
      display: block;
      margin-bottom: 10px;
      text-align: left;
    }
    
    .changeEmail-form input {
      width: 100%;
      padding: 10px;
      border-radius: 5px;
      border: none;
      margin-bottom: 10px;
    }
    
    .changeEmail-form button {
      background-color: #42a3c3;
      border: none;
      color: #fff;
      padding: 10px 20px;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s;
    }
    
    .changeEmail-form button:hover {
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $unique_id = $_SESSION['unique_id'];
    $currentEmail = mysqli_real_escape_string($conn, $_POST['current_email']);
    $newEmail = mysqli_real_escape_string($conn, $_POST['new_email']);

    if (!empty($currentEmail) && !empty($newEmail)) {
        $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = '{$unique_id}'");

        if (mysqli_num_rows($sql) > 0) {
            $row = mysqli_fetch_assoc($sql);
            $user_email = $row['email']; 

            if ($currentEmail === $user_email) {
                $sql2 = mysqli_query($conn, "UPDATE users SET email = '{$newEmail}' WHERE unique_id = '{$row['unique_id']}'");

                if ($sql2) {
                    echo "Email updated successfully!";
                } else {
                    echo "Something went wrong. Please try again!";
                }
            } else {
                echo "Current Email is incorrect!";
            }
        }
    } else {
        echo "All input fields are required!";
    }
}
?>

<div class="changeEmail-form">
    <h2>Change Email</h2>
    <form method="POST">
        <label>Current Email:</label>
        <input type="email" name="current_email" required><br>

        <label>New Email:</label>
        <input type="email" name="new_email" required><br>

        <button>Save</button>
        <button type="button" ><a href="users.php">Back</a></button>
    </form>
</div>

</body>
</html>