<?php
session_start();
include_once "php/config.php";

if (!isset($_SESSION['unique_id'])) {
    header("location: login.php");
    exit();
}

$user_id = $_SESSION['unique_id'];

if ($user_id === null) {
    die('User ID is not set.');
}

$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($id === null) {
    die('Project ID is not set.');
}

$project_query = mysqli_query($conn, "SELECT * FROM Upload WHERE id = $id");

if (!$project_query) {
    die('Error in project query: ' . mysqli_error($conn));
}

$project_row = mysqli_fetch_assoc($project_query);

if (!$project_row) {
    die('Project not found.');
}

$project_name = $project_row['name'];
$project_category = $project_row['Category'];
$project_description = $project_row['description'];
$project_image = 'php/Pimages/' . $project_row['img'];  

$user_name = null;
$user_image = 'php/images/default-user.jpg'; 
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$project_row['user_id']}");
if ($user_query && mysqli_num_rows($user_query) > 0) {
    $user_row = mysqli_fetch_assoc($user_query);
    $user_name = $user_row['fname'] . " " . $user_row['lname'];
    $user_image = 'php/images/' . $user_row['img'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hostname = "localhost";
    $username = "root";
    $password = "";
    $bank_dbname = "bank_db";
    $users_dbname = "web-project";

    $bank_conn = mysqli_connect($hostname, $username, $password, $bank_dbname);
    if (!$bank_conn) {
        die("Bank database connection failed: " . mysqli_connect_error());
    }

    $users_conn = mysqli_connect($hostname, $username, $password, $users_dbname);
    if (!$users_conn) {
        die("Users database connection failed: " . mysqli_connect_error());
    }

    $project_id = $_POST['project_id'];
    $receiver_user_id = $_POST['user_id']; 
    $amount = $_POST['amount'];
    $card_number = $_POST['card_number'];
    $expiration_date = $_POST['expiration_date'];
    $cvv = $_POST['cvv'];

    $sql = "SELECT * FROM CreditCards WHERE card_number = ? AND cvv = ?";
    $stmt = mysqli_prepare($bank_conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ss', $card_number, $cvv);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $user_balance = $row['balance'];
        $sender_user_id = $row['user_id']; 

        if ($user_balance >= $amount) {
            $new_balance = $user_balance - $amount;
            $update_sql = "UPDATE CreditCards SET balance = ? WHERE card_number = ?";
            $update_stmt = mysqli_prepare($bank_conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, 'ds', $new_balance, $card_number);
            mysqli_stmt_execute($update_stmt);

            $receiver_card_sql = "SELECT creditCard FROM users WHERE user_id = ?";
            $receiver_card_stmt = mysqli_prepare($users_conn, $receiver_card_sql);
            mysqli_stmt_bind_param($receiver_card_stmt, 'i', $receiver_user_id);
            mysqli_stmt_execute($receiver_card_stmt);
            $receiver_card_result = mysqli_stmt_get_result($receiver_card_stmt);
            $receiver_card_row = mysqli_fetch_assoc($receiver_card_result);
            $receiver_card_number = $receiver_card_row['creditCard'];

            $receiver_balance_sql = "UPDATE CreditCards SET balance = balance + ? WHERE card_number = ?";
            $receiver_balance_stmt = mysqli_prepare($bank_conn, $receiver_balance_sql);
            mysqli_stmt_bind_param($receiver_balance_stmt, 'ds', $amount, $receiver_card_number);
            mysqli_stmt_execute($receiver_balance_stmt);

            $transaction_sql = "INSERT INTO Transactions (user_id, project_id, amount, status, transaction_date) VALUES (?, ?, ?, 'pending', NOW())";
            $transaction_stmt = mysqli_prepare($bank_conn, $transaction_sql);
            mysqli_stmt_bind_param($transaction_stmt, 'iii', $sender_user_id, $project_id, $amount); 
            mysqli_stmt_execute($transaction_stmt);

            $receiver_unique_id_query = mysqli_query($users_conn, "SELECT unique_id FROM users WHERE user_id = $receiver_user_id");
            $receiver_unique_id_row = mysqli_fetch_assoc($receiver_unique_id_query);
            $receiver_unique_id = $receiver_unique_id_row['unique_id'];

            $notification_sql = "INSERT INTO notifications (user_id, type, from_user_id, created_at) VALUES (?, ?, ?, NOW())";
            $notification_stmt = mysqli_prepare($users_conn, $notification_sql);
            $notification_type = "purchase";
            mysqli_stmt_bind_param($notification_stmt, 'iss', $receiver_unique_id, $notification_type, $user_id);
            mysqli_stmt_execute($notification_stmt);

            header("Location: download.php?project_id=$project_id");
            exit();            
        } else {
            $error_message = "Insufficient balance.";
        }
    } else {
        $error_message = "Invalid credit card.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
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
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .background-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 900px;
            height: 1480px;
            background-color: var(--dark);
            transform: skewY(-60deg);
            transform-origin: top left;
            z-index: 0;
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

        .project-details {
            position: relative;
            z-index: 1;
            background-color: transparent;
            padding: 150px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-gap: 50px;
        }

        .project-image-container {
            position: relative;
            width: 600px;
            height: 700px;
        }

        .project-image {
            width: 100%;
            height: 100%;
            border-radius: 10px;
            box-shadow: var(--box-shadow);
        }

        .like-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 30px;
            height: 30px;
            background-color: var(--dark);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            cursor: pointer;
            box-shadow: var(--box-shadow);
            transition: background-color 0.3s ease;
        }

        .like-icon.liked {
            background-color: var(--pink);
        }

        .project-info {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .project-info h2 {
            font-size: 32px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 50px;
        }

        .project-info p {
            font-size: 18px;
            color: #555;
            line-height: 1.6;
            margin-bottom: 50px;
        }

        .project-tools {
            font-size: 16px;
            color: #777;
            margin-bottom: 50px;
        }

        .project-price {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 50px;
        }

        button {
            background-color: var(--dark);
            color: #fff;
            border: none;
            padding: 10px 20px;
            margin-left: 15px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: var(--box-shadow);
            transition: background-color 0.3s ease;
        }

        .creator-info {
            display: flex;
            align-items: center;
            font-size: 16px;
            color: #777;
            margin-top: 80px;
        }

        .creator-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            box-shadow: var(--box-shadow);
        }

        .creator-details {
            display: flex;
            flex-direction: column;
        }

        .creator-details span {
            margin-bottom: 5px;
        }

        .view-account-btn {
            background-color: var(--dark);
            border: none;
            padding: 0.2rem;
            font-size: 14px;
            color: white;
            text-decoration: none;
            cursor: pointer;
        }

        .view-account-btn a {
            color: white;
            text-decoration: none;
        }


        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            box-shadow: var(--box-shadow);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .billing-form {
            display: flex;
            flex-direction: column;
        }

        .billing-form input {
            margin-bottom: 10px;
            padding: 10px;
            font-size: 16px;
        }

        .billing-form button {
            background-color: var(--dark);
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: var(--box-shadow);
            transition: background-color 0.3s ease;
        }

    </style>
</head>
<body>
<div class="container">
    <div class="background-overlay"></div>
    <a href="users.php" class="back-arrow"><i class="fas fa-arrow-left"></i></a>
    <div class="project-details">
        <div class="project-image-container">
            <img src="<?php echo $project_image; ?>" alt="Project Image" class="project-image">
            <div class="like-icon" onclick="likeProject(this, <?php echo $project_row['id']; ?>, <?php echo $project_row['user_id']; ?>)">
                <i class="fas fa-heart"></i>
            </div>

        </div>
        <div class="project-info">
            <h2><?php echo $project_name; ?></h2>
            <p><?php echo $project_description; ?></p>
            <p class="project-tools"><?php echo "Tools: " . $project_row['tools']; ?></p>
            <div class="project-price-container">
                <span class="project-price">$<?php echo $project_row['price']; ?></span>
                <button class="buy-btn" onclick="openBillingForm()">Buy Now</button>
            </div>
            <div class="creator-info">
                <img src="<?php echo $user_image; ?>" alt="Creator Image">
                <div class="creator-details">
                    <span>Created by <?php echo $user_name; ?></span>
                    <button class="view-account-btn"><a href="viewProfile.php?creator_id=<?php echo $project_row['user_id']; ?>">View Account</a></button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="billingModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeBillingForm()">&times;</span>
        <form class="billing-form" id="billingForm" action="" method="POST">
            <h2>Billing Information</h2>
            <input type="hidden" name="project_id" value="<?php echo $project_row['id']; ?>">
            <input type="hidden" name="user_id" value="<?php echo $user_row['user_id']; ?>">
            <input type="hidden" name="amount" value="<?php echo $project_row['price']; ?>">
            <label for="cardholder_name">Cardholder Name</label>
            <input type="text" id="cardholder_name" name="cardholder_name" required>
            <label for="card_number">Card Number</label>
            <input type="text" id="card_number" name="card_number" required>
            <label for="expiration_date">Expiration Date</label>
            <input type="text" id="expiration_date" name="expiration_date" required>
            <label for="cvv">CVV</label>
            <input type="text" id="cvv" name="cvv" required>
            <button type="submit">Submit</button>
        </form>
        <?php if (isset($error_message)): ?>
            <p style="color: red;"><?php echo $error_message; ?></p>
        <?php endif; ?>
    </div>
</div>

<script>
function likeProject(icon, projectId, userId) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "like_project.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.status === 'success') {
                if (response.action === 'liked') {
                    icon.classList.add('liked');
                } else if (response.action === 'unliked') {
                    icon.classList.remove('liked');
                }
            } else {
                alert('Error: ' + response.message);
            }
        }
    };
    xhr.send("project_id=" + projectId + "&user_id=" + userId);
}

function openBillingForm() {
    document.getElementById('billingModal').style.display = "flex";
}

function closeBillingForm() {
    document.getElementById('billingModal').style.display = "none";
}

</script>
</body>
</html>