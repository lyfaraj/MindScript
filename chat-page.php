<?php 
  session_start();
  include_once "php/config.php";

  if(!isset($_SESSION['unique_id'])){
    header("location: login.php");
    exit();
  }

  $user_id = $_SESSION['unique_id'];
  
  if ($user_id === null) {
    die('User ID is not set.');
  }

  $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = '$user_id'");

  if ($sql && mysqli_num_rows($sql) > 0) {
    $row = mysqli_fetch_assoc($sql);
  } else {
    die('Error fetching user data: ' . mysqli_error($conn));
  }
?>

<?php include_once "header.php"; ?>

<!DOCTYPE html>
<html>
<head>
  <title>Chat Application</title>
    <style>
      :root {
        --dark: #615C83;
        --pink: #F0AEB6;
        --lavender: #D0CADB;
        --box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
      }

      body {
        font-family: 'Courier New', monospace;
        margin: 0;
        padding: 0;
      }

      .wrapper {
        display: flex;
        height: 100vh;
      }

      .back-icon {
        position: absolute;
        left: 20px;
        top: 3%;
        transform: translateY(-50%);
        color: #fff;
        font-size: 1.5em;
        transition: all 0.3s ease;
      }

      .search {
          position: absolute;
          left: 85%;
          transform: translateX(-50%) translateY(-50%); 
          top: 3%; 
          color: #fff;
          font-size: 1em;
          display: flex;
          align-items: center;
          transition: all 0.3s ease;
      }

      .search input {
          padding: 0.5rem;
          border: none;
          border-radius: 4px;
          background-color: white;
          color: #333;
          transition: all 0.3s ease;
          display: none;
          margin-left: 10px; 
      }

      .search input.active {
        display: inline-block;
        width: 200px;
      }

      .search button {
        background: none;
        border: none;
        color: #fff;
        font-size: 1.5em;
        cursor: pointer;
        padding: 0 10px;
      }

      .search .close-btn {
        display: none;
        font-size: 1.2em;
        margin-left: -25px;
        cursor: pointer;
        color: #333;
      }

      .search input.active + .close-btn {
        display: inline;
      }

      .users {
        background-color: var(--dark);
        width: 350px;
        overflow-y: auto;
        position: relative; 
      }

      .content {
        color: #fff;
        width: 350px;
        text-decoration: none;
        margin-bottom: 10px;
        border-bottom: 2px solid #fff;
        padding-bottom: 15px;
        display: flex;
        align-items: center;
        padding: 1rem;
      }

      .content img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 1rem;
      }

      .details {
        display: flex;
        flex-direction: column;
      }

      .details span {
        font-weight: bold;
      }

      .users-search {
        position: relative;
        display: flex;
        align-items: center;
        padding: 0.5rem 1rem;
        border-bottom: 1px solid #ccc;
      }

      .users-search input {
        flex-grow: 1;
        padding: 0.5rem;
        border: none;
        border-radius: 4px;
        margin-left: 0.5rem;
        background-color: #fff;
        color: #333;
      }

      .users-list {
        margin-top: 3rem;
      }

      .chat {
        flex-grow: 1;
        background-color: #fff;
        display: flex;
        flex-direction: column;
        height: 100%;
      }

      .chat-area {
        flex-grow: 1;
        overflow-y: auto;
      }

      .chat-area header {
        background: var(--lavender);
        padding: 10px;
        display: flex;
        align-items: center;
      }

      .details {
        margin-left: 10px;
      }

      .chat-box {
        overflow-y: auto;
      }

      .typing-area {
        padding: 18px 30px;
        display: flex;
        justify-content: space-between;
      }

      .input-field {
        flex: 1;
        padding-left: 1.5rem;
      }

      .typing-area button {
        color: #fff;
        width: 55px;
        border: none;
        outline: none;
        background: var(--dark);
        font-size: 19px;
        cursor: pointer;
        opacity: 0.7;
        pointer-events: none;
        border-radius: 0 5px 5px 0;
        transition: all 0.3s ease;
      }

      .typing-area button.active {
        opacity: 1;
        pointer-events: auto;
      }

      .chat-area header {
        display: flex;
        align-items: center;
        padding: 18px 30px;
      }

      .chat-area header img {
        height: 45px;
        width: 45px;
        margin: 0 15px;
        border-radius: 50%;
      }

      .chat-area header .details span {
        font-size: 17px;
        font-weight: 500;
      }

      .chat-box {
        position: relative;
        overflow-y: auto;
        min-height: 760px;
        max-height: 300px;
        padding: 20px 30px 20px 30px;
        background: #f7f7f7;
        margin-bottom: 10px;
        box-shadow: inset 0 32px 32px -32px rgb(0 0 0 / 5%),
                    inset 0 -32px 32px -32px rgb(0 0 0 / 5%);
      }

      .chat-box .text {
        position: absolute;
        top: 45%;
        left: 50%;
        width: calc(100% - 50px);
        text-align: center;
        transform: translate(-50%, -50%);
      }

      .chat-box .chat {
        margin: 15px 0;
      }

      .chat-box .chat p {
        word-wrap: break-word;
        padding: 8px 16px;
        box-shadow: 0 0 32px rgb(0 0 0 / 8%),
                    0rem 16px 16px -16px rgb(0 0 0 / 10%);
      }

      .chat-box .outgoing {
        display: flex;
        background: transparent;
      }

      .chat-box .outgoing .details {
        margin-left: auto;
        max-width: calc(100% - 130px);
      }

      .outgoing .details p {
        background: var(--pink);
        color: #fff;
        border-radius: 18px 18px 0 18px;
      }

      .chat-box .incoming {
        background: transparent;
        display: flex;
        align-items: flex-end;
      }

      .chat-box .incoming img {
        position: absolute;
        height: 35px;
        width: 35px;
      }

      .chat-box .incoming .details {
        margin-right: auto;
        margin-left: 10px;
        max-width: calc(100% - 130px);
      }

      .incoming .details p {
        background: lavender;
        color: purple;
        border-radius: 18px 18px 18px 0;
      }

      .typing-area input {
        height: 45px;
        width: calc(100% - 58px);
        font-size: 16px;
        padding: 0 13px;
        border: 1px solid #e6e6e6;
        outline: none;
        border-radius: 5px 0 0 5px;
        
      }
      
      .typing-area{
        position: absolute;
        bottom:0;
        width: 80%;
      }

    </style>
</head>
<body>
<div class="wrapper">
    <section class="users">
        <a href="users.php" class="back-icon" id="backIcon"><i class="fas fa-arrow-left"></i></a>
        <div class="search" id="searchContainer">
            <input type="text" placeholder="Enter name to search..." id="searchInput">
            <span class="close-btn" id="closeBtn">&times;</span>
            <button id="searchButton"><i class="fas fa-search"></i></button>
        </div>
        <div class="users-list"></div>
    </section>

    <div class="chat">
        <section class="chat-area">
            <header>
                <?php 
                if(isset($_GET['user_id'])){
                    $user_id = mysqli_real_escape_string($conn, $_GET['user_id']);
                    $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$user_id}");
                    if(mysqli_num_rows($sql) > 0){
                        $row = mysqli_fetch_assoc($sql);
                    }else{
                        header("location: users.php");
                    }
                }
                ?>
                <img src="php/images/<?php echo $row['img']; ?>" alt="">
                <div class="details">
                    <span><?php echo $row['fname']. " " . $row['lname'] ?></span>
                    <p><?php echo $row['status']; ?></p>
                </div>
            </header>
            <div class="chat-box"></div>
            <form action="#" class="typing-area" enctype="multipart/form-data" id="chatForm">
                <input type="text" class="incoming_id" name="incoming_id" value="<?php echo $user_id; ?>" hidden>
                <input type="text" name="message" class="input-field" placeholder="Type a message here..." autocomplete="off" style="padding-left: 1.5rem;">
                <button type="submit"><i class="fab fa-telegram-plane"></i></button>
            </form>
        </section>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', (event) => {
        const searchButton = document.getElementById('searchButton');
        const searchInput = document.getElementById('searchInput');
        const backIcon = document.getElementById('backIcon');
        const searchContainer = document.getElementById('searchContainer');
        const closeBtn = document.getElementById('closeBtn');

        searchButton.addEventListener('click', (e) => {
            e.preventDefault();
            const buttonRect = searchButton.getBoundingClientRect();
            const containerWidth = searchContainer.offsetWidth;
            searchInput.classList.toggle('active');
            backIcon.classList.toggle('active');
            searchButton.classList.toggle('hidden');
            searchContainer.classList.toggle('active');
            searchContainer.style.left = `${buttonRect.left - containerWidth}px`;
            searchButton.style.display = 'none';
        });

        closeBtn.addEventListener('click', () => {
            searchInput.value = '';
            searchInput.classList.remove('active');
            backIcon.classList.remove('active');
            searchButton.classList.remove('hidden');
            searchContainer.classList.remove('active');
            searchButton.style.display = 'block';
        });
    });
</script>


<script src="javascript/users.js"></script>
<script src="javascript/chat.js"></script>
</body>
</html>