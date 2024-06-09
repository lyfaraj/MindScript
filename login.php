<?php 
  session_start();
  if(isset($_SESSION['unique_id'])){
    header("location: users.php");
  }
?>

<!DOCTYPE html>
<html>
<head>  
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
  <style>
    :root {
      --dark: #615C83;
      --pink: #F0AEB6;
      --lavender: #D0CADB;
      --box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
    }

    body {
      margin: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: Arial, sans-serif;
    }

    .register {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      height: 100vh;
      background-color: transparent; 
    }

    .form.login {
      position: relative;
      background-color: #fff;
      padding: 40px;
    }

    .form.login header {
      text-align: center;
      font-size: 25px;
      font-weight: 600;
      padding-bottom: 10px;
      border-bottom: 1px solid #99cbe2;
    }

    .form.login form {
      margin: 20px 0;
    }

    .form.login .field {
      margin-bottom: 20px;
      position: relative; 
    }

    .form.login .field input {
      width: 250px;
      padding: 10px 30px 10px 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .form.login .field .fa-eye {
      position: absolute;
      top: 70%;
      right: 10px;
      transform: translateY(-50%);
      cursor: pointer;
      color: #999;
    }

    .form.login .field label {
      display: block;
      font-weight: bold;
      margin-bottom: 5px;
    }

    .form.login .field input[type="submit"] {
      height: 45px;
      width: 100%;
      border: none;
      color: #fff;
      font-size: 17px;
      background: linear-gradient(90deg, var(--dark), var(--pink) 100%);
      box-shadow: var(--box-shadow); 
      border-radius: 15px;
      cursor: pointer;
      margin-top: 13px;
    }

    .form.login .link {
      text-align: center;
      margin-top: 10px;
    }

    .form.login .link a {
      color: #666;
      text-decoration: none;
    }

    .form.login .link a:hover {
      text-decoration: underline;
    }

    .half-circle {
      position: relative;
      width: 550px;
      height: 100%;
      border-radius: 500px 0 0 500px;
      background: linear-gradient(20deg, var(--dark), var(--pink) 100%);
      transform: translateX(50%);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      color: #fff;
      padding: 40px;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
    }

    .half-circle::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      border-radius: 500px 0 0 500px;
      background: radial-gradient(circle at center, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
      z-index: 1;
    }

    .half-circle h2 {
      font-size: 42px;
      font-weight: 700;
      margin-bottom: 10px;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .half-circle p {
      font-size: 20px;
      line-height: 1.6;
      margin: 30px;
      opacity: 0.8;
    }
    .half-circle .buttons {
      display: flex;
      width: 100%;
      z-index: 2;
      justify-content: center;
    }

    .half-circle .buttons button {
      background-color: #fff;
      margin-right: 10px;
      color: var(--dark);
      border: none;
      padding: 12px 24px;
      border-radius: 30px;
      font-size: 16px;
      font-weight: 600;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      cursor: pointer;
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .half-circle .buttons button:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    .half-circle .buttons .circular-btn {
      background-color: transparent;
      margin-left: 20px;
      border: 2px solid #fff;
      border-radius: 50%;
      width: 50px;
      height: 50px;
      display: flex;
      justify-content: center;
      align-items: center;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s, box-shadow 0.3s;
      text-decoration: none;
    }

    .half-circle .buttons .circular-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    .half-circle .buttons .circular-btn i {
      color: #fff;
      font-size: 24px;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <div class="register">
    <section class="form login">
      <header>Login to MindScript</header>
      <form action="#" method="POST" enctype="multipart/form-data" autocomplete="off">
        <div class="error-text"></div>
          <div class="field input">
            <label>Email Address</label>
            <input type="text" name="email" placeholder="Enter your email" required>
          </div>
          <div class="field input">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter your password" required>
            <i class="fas fa-eye"></i>
          </div>
          <div class="field button">
            <input type="submit" name="submit" value="Login">
          </div>
      </form>
      <div class="link">Not signed up yet? <a href="signup.php">Signup now</a></div>
    </section>
    <div class="half-circle">
      <h2>Mind Script</h2>
      <p>
        Unlock your programming potential with our comprehensive platform. 
        Collaborate on projects, manage tasks, and connect with a community of 
        developers to bring your ideas to life.
      </p>
      <div class="buttons">
        <button>Mind Script</button>
        <a href="index.php" class="circular-btn">
          <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>
  
  <script src="javascript/pass-show-hide.js"></script>
  <script src="javascript/login.js"></script>

</body>
</html>