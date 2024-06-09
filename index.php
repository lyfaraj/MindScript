<?php

include_once "php/config.php";

$users_query = mysqli_query($conn, "SELECT COUNT(*) AS user_count FROM users");
$user_count = ($users_query) ? mysqli_fetch_assoc($users_query)['user_count'] : 0;


$projects_query = mysqli_query($conn, "SELECT COUNT(*) AS project_count FROM Upload");
$project_count = ($projects_query) ? mysqli_fetch_assoc($projects_query)['project_count'] : 0;

function getRandomNumber() {
    return rand(1, 5);
}

$randomNumber = getRandomNumber();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindScript</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --dark: #615C83;
            --pink: #F0AEB6;
            --lavender: #D0CADB;
            --box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
            --font-family: 'Poppins', sans-serif;
        }

        body {
            font-family: var(--font-family);
            margin: 0;
            padding: 0;
            color: #333;
            background-color: #f5f5f5;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        header {
            background-color: var(--dark);
            padding: 20px;
            box-shadow: var(--box-shadow);
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        nav .logo {
            font-size: 24px;
            font-weight: bold;
            color: #fff;
        }

        nav ul {
            list-style-type: none;
            display: flex;
            align-items: center;
        }

        nav ul li {
            margin-right: 20px;
        }

        nav ul li a {
            color: #fff;
            transition: color 0.3s ease;
        }

        nav ul li a:hover {
            color: var(--pink);
        }

        .download-btn {
            background-color: var(--pink);
            color: #fff;
            padding: 12px 24px;
            border-radius: 5px;
            box-shadow: var(--box-shadow);
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .download-btn:hover {
            background-color: #e49da5;
        }

        .hero {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 130px 40px;
            background-color: var(--lavender);
            box-shadow: var(--box-shadow);
        }

        .hero-content {
            flex: 1;
            padding-right: 40px;
        }

        .hero-content h1 {
            color: var(--dark);
            font-size: 48px;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .hero-content p {
            margin-bottom: 30px;
            font-size: 18px;
            line-height: 1.6;
        }

        .hero-image {
            flex: 1;
            text-align: right;
        }

        .hero-image img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 40px;
            padding: 80px 20px;
            background-color: var(--dark);
            color: #fff;
        }

        .feature-item {
            text-align: center;
            background-color: #7c74a0;
            padding: 40px 20px;
            border-radius: 10px;
            box-shadow: var(--box-shadow);
            transition: transform 0.3s ease;
        }

        .feature-item:hover {
            transform: translateY(-10px);
        }

        .feature-item .icon {
            font-size: 50px;
            margin-bottom: 10px;
        }

        .feature-item h3 {
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .feature-item p {
            font-size: 16px;
            line-height: 1.6;
        }

        .cta {
            padding: 80px 20px;
            text-align: center;
            background-color: var(--lavender);
            box-shadow: var(--box-shadow);
        }

        .cta h2 {
            color: var(--dark);
            font-size: 38px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .cta p {
            margin-bottom: 30px;
            font-size: 18px;
            line-height: 1.6;
        }

        .explore {
            background-color: var(--lavender);
            padding: 50px 0;
        }

        .explore > div {
            max-width: 1200px;
            margin: 0 auto;
        }

        .chart-and-text {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 40px;
        }

        .chart-container {
            flex: 1;
        }

        .text-content {
            flex: 1;
            text-align: left;
        }

        .text-content p {
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 40px;
        }

        .stats {
            display: flex;
            justify-content: flex-start;
            gap: 60px;
        }

        .stats > div {
            flex: 1;
        }

        .stats small {
            display: block;
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .stats h3 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .stats p {
            font-size: 16px;
            color: #666;
        }
        
        footer {
            background-color: var(--dark);
            color: #fff;
            padding: 50px 20px;
            text-align: center;
            font-size: 14px;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-column {
            flex: 1;
            margin: 0 20px;
            text-align: left;
        }

        footer h2,
        footer h3 {
            color: #fff;
            font-weight: 600;
            margin-bottom: 20px;
        }

        footer p {
            margin-bottom: 20px;
            color: #fff;
        }

        footer hr {
            border: 0;
            height: 1px;
            background-color: var(--pink);
            margin: 20px auto;
            width: 50%;
        }

        footer ul {
            padding: 0;
            list-style: none;
        }

        footer ul li {
            margin-bottom: 10px;
        }

        footer ul li a {
            color: #fff;
            transition: color 0.3s ease;
        }

        footer ul li a:hover {
            color: #fff;
        }

        .input-wrap {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #fff;
            border-radius: 5px;
            padding: 5px;
            margin-bottom: 20px;
        }

        .input-wrap input {
            flex: 1;
            border: none;
            padding: 10px;
            background-color: transparent;
            color: #fff;
        }

        .input-wrap button {
            background-color: #e49da5;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .input-wrap button:hover {
            background-color: #d88790;
        }

        .my-input {
            color: #ffffff;
        }

        .copy-right {
            background-color: var(--dark);
            text-align: center;
            padding: 20px 0;
            color: #fff;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="#" class="logo">MindScript</a>
            <ul>
                <li><a href="#">Home</a></li>
                <li><a href="#">Projects</a></li>
                <li><a href="#">Collaborate</a></li>
                <li><a href="#">Chat</a></li>
                <li><a href="#">Marketplace</a></li>
                <li><a href="signup.php" class="download-btn">Sign Up</a></li>
                <li><a href="login.php" class="download-btn">Login</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="hero">
            <div class="hero-content">
                <h1>Welcome to MindScript</h1>
                <p>Where programmers collaborate, manage projects, and connect with the community.</p>
                <a href="login.php" class="download-btn">Login</a>
            </div>
            <div class="hero-image">
                <img src="php/images/logo2.png" alt="MindScript Screenshot">
            </div>
        </section>

        <section class="features">
            <div class="feature-item">
                <i class="fas fa-users icon"></i>
                <h3>Connect with Fellow Programmers</h3>
                <p>Collaborate and work together on exciting projects.</p>
            </div>
            <div class="feature-item">
                <i class="fas fa-tasks icon"></i>
                <h3>Efficient Project Management</h3>
                <p>Manage your projects with ease and stay organized.</p>
            </div>
            <div class="feature-item">
                <i class="fas fa-comments icon"></i>
                <h3>Real-time Chat</h3>
                <p>Interact with other programmers through live chat.</p>
            </div>
            <div class="feature-item">
                <i class="fas fa-store icon"></i>
                <h3>Marketplace</h3>
                <p>Buy or sell your projects in our marketplace.</p>
            </div>
        </section>

        <section class="cta">
            <h2>Explore Our Platform</h2>
            <section class="explore">
                <div class="chart-and-text">
                    <div class="chart-container">
                        <canvas class="chart" id="projectsChart"></canvas>
                    </div>
                    <div class="text-content">
                        <p>
                            Discover amazing projects created by talented programmers. Our platform is
                            revolutionizing the way projects are developed, managed, and shared within
                            the programming community.
                        </p>
                        <div class="stats">
                            <div>
                                <small>Projects</small>
                                <h3><?php echo $project_count; ?></h3>
                                <p>Explore a wide range of projects across various domains.</p>
                            </div>
                            <div>
                                <small>Users</small>
                                <h3><?php echo $user_count; ?></h3>
                                <p>Join a vibrant community of programmers and collaborators.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </section>
    </main>

    <footer>
        <div class="container">
            <div>
                <h2>MindScript</h2>
                <p>programming platform.</p>
                <hr>
                <h3>Get our latest updates</h3>
                <form action="">
                    <div class="input-wrap">
                        <input type="email" placeholder="Enter Your Email" class="my-input">
                        <button class="btn btn-primary">Send</button>
                    </div>
                </form>
            </div>
            <div>
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#">reg</a></li>
                    <li><a href="#">gf</a></li>
                    <li><a href="#">df</a></li>
                    <li><a href="#">fd</a></li>
                    <li><a href="#">df</a></li>
                    <li><a href="#">df</a></li>
                </ul>
            </div>
            <div>
                <h3>Informations</h3>
                <ul>
                    <li><a href="#">reg</a></li>
                    <li><a href="#">gf</a></li>
                    <li><a href="#">df</a></li>
                    <li><a href="#">fd</a></li>
                    <li><a href="#">df</a></li>
                    <li><a href="#">df</a></li>
                </ul>
            </div>
            <div>
                <h3>Company</h3>
                <ul>
                    <li><a href="#">reg</a></li>
                    <li><a href="#">gf</a></li>
                    <li><a href="#">df</a></li>
                    <li><a href="#">fd</a></li>
                    <li><a href="#">df</a></li>
                    <li><a href="#">df</a></li>
                </ul>
            </div>
            <div>
                <h3>Social Media</h3>
                <ul>
                    <li><a href="#">Facebook</a></li>
                    <li><a href="#">Instagram</a></li>
                    <li><a href="#">LinkedIn</a></li>
                    <li><a href="#">WhatsApp</a></li>
                    <li><a href="#">Twitter</a></li>
                    <li><a href="#">Telegram</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <div class="copy-right">
        <p>&copy; 2024 MindScript. All rights reserved.</p>
    </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('projectsChart').getContext('2d');
  const projectsChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Projects', 'Users'],
      datasets: [{
        label: 'Statistics',
        data: [<?php echo $project_count; ?>, <?php echo $user_count; ?>],
        backgroundColor: ['#6c63ff', '#f0f0f0'],
        borderColor: ['#6c63ff', '#f0f0f0'],
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1000
          }
        }
      },
      plugins: {
        title: {
          display: true,
          text: 'Project and User Statistics'
        }
      }
    }
  });
</script>
    </script>
</body>
</html>

           


       



