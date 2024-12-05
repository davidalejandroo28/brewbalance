<?php 
include '../database/db_connect.php';

session_start();

// Check if the user is logged in, if not then redirect them to the login page
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email']; // Get the logged-in user's email

$sql = "SELECT username FROM userdata WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Parkinsans:wght@300..800&family=Poppins:wght@100..900&display=swap" rel="stylesheet">
  <title>Info</title>
  <style>
    body {
      font-family: 'Parkinsans', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #CFBB99;
    }

    .main-content {
        padding: 80px 20px 20px;
        max-width: 800px;
        margin: auto;
    }

    .top-bar {
      position: fixed;
      top: 0;
      width: 100%;
      height: 50px;
      background-color: #4C3D19;
      color: #E5D7C4;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 20px;
      z-index: 1000;
    }
    .menu-btn {
      background-color: transparent;
      border: none;
      color: #E5D7C4;
      font-size: 24px;
      cursor: pointer;
      margin-left: 10px;
    }

    .top-bar .logout-btn {
      background-color: #889063;
      color: #4C3D19;
      border: none;
      padding: 5px 10px;
      border-radius: 5px;
    }

    .top-bar .logout-btn:hover {
      background-color: #354024;
    }
    .sidebar {
      height: 100%;
      width: 250px;
      position: fixed;
      top: 0;
      left: -250px; /* Initially hidden */
      background-color: #354024;
      color: #E5D7C4;
      padding-top: 60px;
      transition: left 0.3s ease-in-out;
      z-index: 2000; /* Ensure it's above other elements */
    }

    .sidebar a {
      padding: 15px 25px;
      text-decoration: none;
      font-size: 18px;
      color: #E5D7C4;
      display: block;
      transition: 0.3s;
    }
    .sidebar .close-btn {
      position: absolute;
      top: 20px;
      right: 25px;
      font-size: 36px;
      color: #E5D7C4;
      cursor: pointer;
    }

    .sidebar a:hover {
      background-color: #889063;
    }

    .section {
      background-color: #E5D7C4;
      padding: 20px;
      margin: 20px 0;
      border-radius: 10px;
    }

    .section-title {
      font-size: 1.5em;
      margin-bottom: 10px;
      color: #2B3031;
    }

    .text {
      font-size: 1em;
      color: #4C3D19;
      line-height: 1.5;
    }

    .input {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .button {
      background-color: #354024;
      color: #E5D7C4;
      border: none;
      padding: 10px 15px;
      border-radius: 5px;
      cursor: pointer;
    }

    .button:hover {
      background-color: #889063;
    }

    .result {
      font-weight: bold;
      margin: 10px 0;
    }
  </style>
</head>
<body>
  <div class="top-bar">
  <button class="menu-btn" onclick="toggleMenu()">☰</button>
    <div>Welcome, <?php echo htmlspecialchars($username); ?></div>
    <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
    <span class="close-btn" onclick="toggleMenu()">&times;</span>
    <a href="dashboard.php" class="menu-item">Dashboard</a>
    <a href="info.php" class="menu-item">Info</a>
    <a href="charts.php" class="menu-item">Charts</a>
    </div>

  <div class="main-content">
    <h1 class="text-center">Caffeine and Its Effects</h1>

    <div class="section">
      <div class="section-title">Why Limit Caffeine?</div>
      <p class="text">
        Caffeine is a powerful stimulant that acts on the central nervous system to increase alertness and combat fatigue. While it has its benefits, excessive consumption can lead to several short-term and long-term health issues.
      </p>
      <ul class="text">
        <li><b>Anxiety and Restlessness:</b> Overstimulation can lead to nervousness, jitteriness, and panic attacks.</li>
        <li><b>Insomnia:</b> Caffeine can disrupt sleep patterns, reducing sleep quality.</li>
        <li><b>Heart and Blood Pressure Issues:</b> Temporary increases in blood pressure can put strain on the heart.</li>
        <li><b>Digestive Problems:</b> Excessive caffeine can worsen acid reflux and stomach issues.</li>
        <li><b>Dependency:</b> Long-term use can lead to dependency, with withdrawal symptoms like headaches and fatigue.</li>
        <li><b>Impact on Pregnancy:</b> High caffeine intake during pregnancy may pose risks to the fetus.</li>
      </ul>
    </div>

    <div class="section">
      <div class="section-title">Daily Limit Recommendations</div>
      <ul class="text">
        <li><b>Adults:</b> 300 mg - 400 mg per day.</li>
        <li><b>Teens:</b> No more than 100 mg per day.</li>
        <li><b>Children:</b> Avoid caffeine entirely.</li>
      </ul>
    </div>

    <div class="section">
      <div class="section-title">Risks of Excessive Caffeine Consumption</div>
      <ul class="text">
        <li><b>Heart Palpitations:</b> High doses can cause irregular heartbeats and increased heart rate.</li>
        <li><b>Bone Health:</b> Excess caffeine can interfere with calcium absorption, affecting bone density.</li>
        <li><b>Dehydration:</b> Caffeine acts as a diuretic, leading to potential dehydration.</li>
        <li><b>Overdose Risk:</b> Consuming extremely high amounts can lead to caffeine toxicity, which is life-threatening.</li>
      </ul>
    </div>

    <div class="section">
      <div class="section-title">Caffeine Sensitivity</div>
      <p class="text">
        Caffeine sensitivity varies between individuals and can depend on genetics, age, and metabolism. 
        Some people experience strong effects with small amounts of caffeine, including rapid heartbeat, anxiety, and digestive issues.
        Others may metabolize caffeine more quickly and feel minimal effects, even at higher doses.
      </p>
      <p class="text"><b>Factors that increase sensitivity:</b></p>
      <ul class="text">
        <li>Pregnancy</li>
        <li>Medications or health conditions</li>
        <li>Lower body weight</li>
      </ul>
    </div>

    <div class="section">
      <div class="section-title">Healthier Alternatives</div>
      <ul class="text">
        <li><b>Herbal Teas:</b> Naturally caffeine-free and come in a variety of flavors.</li>
        <li><b>Decaffeinated Coffee:</b> Provides the coffee experience with minimal caffeine.</li>
        <li><b>Infused Water:</b> Refreshing and hydrating, infused with fruits or herbs.</li>
        <li><b>Matcha:</b> Lower caffeine levels than coffee but packed with antioxidants.</li>
        <li><b>Golden Milk:</b> A turmeric-based drink known for its anti-inflammatory properties.</li>
      </ul>
    </div>
  </div>
  <!-- Script for sidebar toggle -->
  <script>
    function toggleMenu() {
      const sidebar = document.getElementById("sidebar");
      if (sidebar.style.left === "0px") {
        sidebar.style.left = "-250px"; // Hide sidebar
      } else {
        sidebar.style.left = "0"; // Show sidebar
      }
    }
  </script>
</body>
</html>
