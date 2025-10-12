<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && $error['type'] === E_ERROR) {
        die("Fatal error: {$error['message']} in {$error['file']} on line {$error['line']}");
    }
});

require_once 'config/session.php';

// If user is already logged in, redirect to appropriate dashboard
if (isLoggedIn()) {
    if ($_SESSION['user_role'] === 'teacher') {
        header('Location: teacher/dashboard.php');
    } else if ($_SESSION['user_role'] === 'student') {
        header('Location: student/dashboard.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>LearnWise — Eco Learning Hub</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
  /* === GLOBAL STYLES === */
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  html {
    scroll-behavior: smooth;
  }

  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #fff;
    background-color: #0a2e0a;
    overflow-x: hidden;
  }

  a {
    color: inherit;
    text-decoration: none;
  }

  /* === NAVBAR === */
  header {
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 100;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(12px);
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 60px;
  }

  header h1 {
    font-size: 1.8rem;
    color: #00ff88;
  }

  nav ul {
    list-style: none;
    display: flex;
    gap: 25px;
  }

  nav ul li a, .nav-btn {
    font-weight: 500;
    transition: color 0.3s;
    cursor: pointer;
  }

  nav ul li a:hover, .nav-btn:hover {
    color: #00ff88;
  }

  /* === HERO === */
  .hero {
    height: 100vh;
    background: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1920&q=80') center/cover fixed;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    backdrop-filter: blur(5px);
    padding: 0 20px;
  }

  .hero h2 {
    font-size: 3rem;
    max-width: 800px;
  }

  .hero p {
    font-size: 1.2rem;
    margin: 20px 0;
  }

  .hero .btn {
    background: #00ff88;
    color: #000;
    padding: 12px 28px;
    border-radius: 8px;
    font-weight: bold;
    transition: transform 0.3s;
    cursor: pointer;
  }

  .hero .btn:hover {
    transform: scale(1.1);
  }

  /* === FEATURES === */
  .features {
    background: url('https://images.unsplash.com/photo-1503264116251-35a269479413?auto=format&fit=crop&w=1920&q=80') center/cover fixed;
    padding: 120px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    backdrop-filter: blur(3px);
  }

  .features h2 {
    font-size: 2.5rem;
    margin-bottom: 50px;
  }

  .feature-cards {
    display: flex;
    flex-direction: column;
    gap: 40px;
    width: 100%;
  }

  .card {
    width: 100%;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    padding: 60px;
    border-radius: 16px;
    text-align: center;
  }

  .card h3 {
    color: #00ff88;
    margin-bottom: 20px;
    font-size: 1.8rem;
  }

  .card p {
    font-size: 1.1rem;
  }

  /* === ABOUT SECTION === */
  .about {
    background: #062406;
    padding: 100px 20px;
    text-align: center;
  }

  .about h2 {
    font-size: 2.5rem;
    margin-bottom: 20px;
    color: #00ff88;
  }

  .about p {
    max-width: 800px;
    margin: auto;
    line-height: 1.6;
    font-size: 1.1rem;
  }

  /* === FOOTER === */
  footer {
    background: #031503;
    text-align: center;
    padding: 30px;
    font-size: 0.9rem;
  }

  footer a {
    color: #00ff88;
  }

  @media (max-width: 768px) {
    header {
      flex-direction: column;
      padding: 10px;
    }
    nav ul {
      flex-wrap: wrap;
      justify-content: center;
    }
  }
</style>
</head>
<body>

<!-- HEADER -->
<header>
  <h1>EcoLearn</h1>
  <nav>
    <ul>
      <li><a href="#hero">Home</a></li>
      <li><a href="#features">Features</a></li>
      <li><a href="#about">About</a></li>
      <li><span><a href="login.php">Login</a></span></li>
      <li><span><a href="register.php">Sign-Up</a></span></li>
    </ul>
  </nav>
</header>

<!-- HERO -->
<section id="hero" class="hero">
  <h2>Empowering Sustainable Learning Through Technology</h2>
  <p>Join EcoLearn — your eco-friendly digital classroom platform.</p>
  <a href="login.php" class="btn">Get Started</a>
</section>

<!-- FEATURES -->
<section id="features" class="features">
  <h2>Our Key Features</h2>
  <div class="feature-cards">
    <div class="card">
      <h3>Interactive Lessons</h3>
      <p>Engage with dynamic, hands-on lessons that bring topics to life.</p>
    </div>
    <div class="card">
      <h3>Community Collaboration</h3>
      <p>Connect with students and teachers worldwide to share ideas.</p>
    </div>
    <div class="card">
      <h3>Eco Data Insights</h3>
      <p>Access real-world data to make informed, sustainable choices.</p>
    </div>
  </div>
</section>

<!-- ABOUT -->
<section id="about" class="about">
  <h2>About EcoLearn</h2>
  <p>EcoLearn is an innovative platform designed to connect educators and learners with interactive, eco-friendly tools. We believe that knowledge and sustainability go hand-in-hand, creating a better future for both people and the planet.</p>
</section>

<!-- FOOTER -->
<footer>
  <p>© 2025 EcoLearn | <a href="#">Privacy Policy</a> | <a href="#">Terms</a></p>
</footer>

</body>
</html>