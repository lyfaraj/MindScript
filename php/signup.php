<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include_once "config.php";

$fname = mysqli_real_escape_string($conn, $_POST['fname']);
$lname = mysqli_real_escape_string($conn, $_POST['lname']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = mysqli_real_escape_string($conn, $_POST['password']);

if (!empty($fname) && !empty($lname) && !empty($email) && !empty($password)) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $sql_email = mysqli_query($conn, "SELECT * FROM users WHERE email = '{$email}'");
        if (mysqli_num_rows($sql_email) > 0) {
            echo "$email - This email already exists!";
        } else {
            $sql_name = mysqli_query($conn, "SELECT * FROM users WHERE fname = '{$fname}' AND lname = '{$lname}'");
            if (mysqli_num_rows($sql_name) > 0) {
                echo "$fname $lname - This username already exists!";
            } else {
                if (strlen($password) >= 8) {
                    $ran_id = rand(time(), 100000000);
                    $status = "Active now";
                    $encrypt_pass = md5($password);
                    $insert_query = mysqli_query($conn, "INSERT INTO users (unique_id, fname, lname, email, password, status)
                    VALUES ({$ran_id}, '{$fname}','{$lname}', '{$email}', '{$encrypt_pass}', '{$status}')");
                    if ($insert_query) {
                        $select_sql2 = mysqli_query($conn, "SELECT * FROM users WHERE email = '{$email}'");
                        if (mysqli_num_rows($select_sql2) > 0) {
                            $result = mysqli_fetch_assoc($select_sql2);
                            $_SESSION['unique_id'] = $result['unique_id'];
                            echo "success";
                        } else {
                            echo "This email address not Exist!";
                        }
                    } else {
                        echo "Something went wrong. Please try again!";
                    }
                } else {
                    echo "Password should be at least 8 characters long!";
                }
            }
        }
    } else {
        echo "$email is not a valid email!";
    }
} else {
    echo "All input fields are required!";
}
?>