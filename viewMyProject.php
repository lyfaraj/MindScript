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

$project_id = $project_row['id'];
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

        .delete-icon {
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

        .price-input-container {
            position: relative;
            display: inline-block;
        }

        #newPrice {
            padding-right: 30px; 
        }

        .cancel-icon {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            color: #ccc;
            cursor: pointer;
            z-index: 1;
        }

        .cancel-icon:hover {
            color: #999;
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="background-overlay"></div>
        <a href="account.php" class="back-arrow"><i class="fas fa-arrow-left"></i></a>
        <div class="project-details">
            <div class="project-image-container">
                <img src="<?php echo $project_image; ?>" alt="Project Image" class="project-image">
                <div class="delete-icon" onclick="confirmDelete(<?php echo $project_id; ?>)">
                    <i class="fas fa-trash"></i>
                </div>
            </div>
            <div class="project-info">
                <h2><?php echo $project_name; ?></h2>
                <p><?php echo $project_description; ?></p>
                <p class="project-tools"><?php echo "Tools: " . $project_row['tools']; ?></p>
                <div class="project-price-container" id="priceContainer">
                    <span class="project-price">$<?php echo $project_row['price']; ?></span>
                    <button class="price-btn" onclick="togglePriceInput()">Change Price</button>
                </div>
                <div class="change-price-container" id="changePriceContainer" style="display: none;">
                    <div class="price-input-container">
                        <input type="number" id="newPrice" placeholder="Enter new price">
                        <i class="fas fa-times cancel-icon" onclick="cancelPriceChange()"></i>
                    </div>
                    <button class="price-btn" onclick="submitPriceChange(<?php echo $project_id; ?>)">Submit</button>
                </div>

                <div class="creator-info">
                    <img src="<?php echo $user_image; ?>" alt="Creator Image">
                    <span>Created by <?php echo $user_name; ?></span>
                </div>
            </div>
        </div>
    </div>
    <script>
        function confirmDelete(projectId) {
            if (confirm("Are you sure you want to delete this project?")) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "deleteUpload.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        window.location.href = "account.php";
                    }
                };
                xhr.send("project_id=" + projectId);
            }
        }

        function togglePriceInput() {
            var priceContainer = document.getElementById("priceContainer");
            var changePriceContainer = document.getElementById("changePriceContainer");

            if (priceContainer && changePriceContainer) {
                priceContainer.style.display = "none";
                changePriceContainer.style.display = "block";
            }
        }

        function submitPriceChange(projectId) {
            var newPrice = document.getElementById("newPrice").value.trim();
            if (newPrice === "") {
                alert("Please enter a valid price.");
                return;
            }

            if (confirm("Are you sure you want to change the price?")) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "changePrice.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        if (xhr.responseText.trim() === "success") {
                            alert("Price changed successfully.");
                            var priceElement = document.querySelector(".project-price");
                            if (priceElement) {
                                priceElement.textContent = "$" + newPrice;
                            }

                            var priceContainer = document.getElementById("priceContainer");
                            var changePriceContainer = document.getElementById("changePriceContainer");
                            if (priceContainer && changePriceContainer) {
                                priceContainer.style.display = "block";
                                changePriceContainer.style.display = "none";
                            }
                        } else {
                            alert("Failed to change price. Please try again later.");
                        }
                    }
                };
                xhr.send("project_id=" + projectId + "&new_price=" + encodeURIComponent(newPrice));
            }
        }

        function cancelPriceChange() {
            var priceContainer = document.getElementById("priceContainer");
            var changePriceContainer = document.getElementById("changePriceContainer");
            var newPriceInput = document.getElementById("newPrice");
            
            if (newPriceInput) {
                newPriceInput.value = "";
            }

            if (priceContainer && changePriceContainer) {
                priceContainer.style.display = "block";
                changePriceContainer.style.display = "none";
            }
        }

    </script>
</body>
</html>
