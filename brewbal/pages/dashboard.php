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

// Handle search functionality
$results = null; // Initialize search results
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_query'])) {
  $search_query = '%' . $_POST['search_query'] . '%';
  $stmt = $conn->prepare("SELECT * FROM caffeine_content WHERE drink LIKE ?");
  $stmt->bind_param("s", $search_query);
  $stmt->execute();
  $results = $stmt->get_result();
  $stmt->close();
}

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
function getTotalCaffeineConsumedToday($email, $conn) {
  $today = date('Y-m-d');  // Get today's date
  $totalConsumed = 0;
  $sql = "SELECT SUM(mg_coff) FROM caffeine_tracker WHERE email = ? AND date = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ss", $email, $today);
  $stmt->execute();
  $stmt->bind_result($totalConsumed);
  $stmt->fetch();
  $stmt->close();

  return $totalConsumed ? $totalConsumed : 0; // Return the total, or 0 if no data
}

$totalConsumedToday = getTotalCaffeineConsumedToday($email, $conn); 

if ($_SERVER['REQUEST_METHOD'] === 'POST' & isset($_POST['mg_coff'])) {
  if (isset($_POST['mg_coff'])) {
      $mg_coff = $_POST['mg_coff'];
      $today = date('Y-m-d');

      // Insert the value into the caffeine_tracker table
      $stmt = $conn->prepare("INSERT INTO caffeine_tracker (email, mg_coff, date) VALUES (?, ?, ?)");
      $stmt->bind_param("sis", $email, $mg_coff, $today);
      $stmt->execute();
      $stmt->close();
  }
  
}

$coffeeLimit = 0;
function getTotalLimit($email, $conn) {
  //
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
  <link rel="shortcut icon" href="https://cdn-icons-png.flaticon.com/512/295/295128.png">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Coffee Tracker</title>
  
  <style>
    /* Body and layout */
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      display: flex;
      flex-direction: row;
      height: auto;
      background-color: #f4f4f9;
      justify-content: center; /* Center horizontally */
      align-items: center; /* Center vertically */
      position: relative;
      overflow-y: auto;
    }
    .main-content {
      padding-top: 100px; /* Leave space for the top bar */
      width: 90%; /* Adjust content width for better visibility */
      max-width: 1200px; /* Prevent content from being too wide */
    }

    /* Sidebar styles */
    .sidebar {
      height: 100%;
      width: 250px;
      position: fixed;
      top: 0;
      left: -250px; /* Start hidden */
      background-color: #333;
      color: white;
      padding-top: 60px;
      transition: 0.3s;
      z-index: 2000; /* Ensure sidebar is above the navbar */
    }

    .sidebar a {
      padding: 15px 25px;
      text-decoration: none;
      font-size: 18px;
      color: white;
      display: block;
      transition: 0.3s;
    }

    .sidebar a:hover {
      background-color: #575757;
    }

    .sidebar .close-btn {
      position: absolute;
      top: 20px;
      right: 25px;
      font-size: 36px;
      color: white;
      cursor: pointer;
    }

    /* Menu toggle button styles */
    .menu-btn {
      font-size: 30px;
      background-color: transparent;
      border: none;
      color: #333;
      cursor: pointer;
      padding: 20px;
      position: absolute;
      top: 20px;
      left: 20px;
      z-index: 1500; /* Make sure the toggle button is on top */
    }

    /* Top bar styles */
    .top-bar {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 50px;
      background-color: #6f4f1f; /* Brown color */
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 20px;
      font-weight: bold;
      z-index: 1000; /* Lower than the sidebar */
    }

    /* Tracker and input positioning */
    .tracker-container {
      position: relative;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      width: 100%;
      text-align: center;
      margin-top: 70px; /* Space for the top bar */
    }

    .circle {
      width: 200px;
      height: 200px;
      border-radius: 50%;
      background: conic-gradient(#4caf50 var(--progress), #ddd 0%);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .inner-circle {
      width: 140px;
      height: 140px;
      border-radius: 50%;
      background: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      font-weight: bold;
    }
    
    table {
      margin: auto;
      border-collapse: collapse;
      width: 80%;
    }
    table, th, td {
      border: 1px solid #ddd;
    }
    th, td {
      padding: 8px;
      text-align: center;
    }
    input {
      margin-top: 20px;
      padding: 10px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 5px;
      width: 250px;
      text-align: center;
    }

    button {
      margin-top: 10px;
      padding: 10px 20px;
      font-size: 16px;
      color: white;
      background-color: #4caf50;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    button:hover {
      background-color: #45a049;
    }
    .search-container {
      margin: 20px auto;
      text-align: center;
      width: 80%; /* Center the container and make it responsive */
      padding: 15px;
      background-color: #f9f9f9; /* Light background for contrast */
      border: 1px solid #ddd;
      border-radius: 8px;
      box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
    }

    .search-container h3 {
      margin-bottom: 10px;
      font-size: 1.2rem;
      font-weight: bold;
    }

    .search-container input[type="text"] {
      width: 70%;
      padding: 10px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .search-container button {
      padding: 10px 20px;
      font-size: 16px;
      margin-left: 10px;
      color: #fff;
      background-color: #4caf50;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .search-container button:hover {
      background-color: #45a049;
    }

    .results-container table {
      width: 90%; /* Adjust to take most of the container width */
      margin: 20px auto;
      border-collapse: collapse;
    }

    .results-container th,
    .results-container td {
      padding: 10px;
      text-align: center;
      border: 1px solid #ddd;
    }

    .results-container th {
      background-color: #f4f4f9;
      font-weight: bold;
    }

    .results-container td {
      background-color: #fff;
    }

    .results-container button {
      padding: 8px 16px;
      color: #fff;
      background-color: #007bff;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .results-container button:hover {
      background-color: #0056b3;
    }

    
    #total-consumed {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 10px;
    color: #333;
    }  </style>
</head>
<body>
  <!-- Menu toggle button -->
  <button class="menu-btn" onclick="toggleMenu()">&#9776;</button>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <span class="close-btn" onclick="toggleMenu()">&times;</span>
    <a href="profile.php" class="menu-item">Profile</a>
    <a href="charts.php" class="menu-item">Charts</a>
    <a href="products.php" class="menu-item">Products</a>
  </div>

  <!-- Top brown bar with dynamic welcome message -->
  <div class="top-bar">
    Welcome, <?php echo htmlspecialchars($username); ?>
    <a href="logout.php" class="btn btn-danger" style="margin-left: 20px;">Logout</a>
  </div>

  <div class="main-content">
    <div class="tracker-container">
      <div id="total-consumed">Total Consumed: <?php echo $totalConsumedToday; ?> mg</div>
      <div class="circle" style="--progress: 0%;">
        <div class="inner-circle" id="percentage">0%</div>
      </div>

      <!-- Search -->
      <div class="search-container">
        <h3>Search for a Drink</h3>
        <form method="POST">
          <input type="text" name="search_query" placeholder="Search for a drink..." required>
          <button type="submit" class="btn btn-secondary">Search</button>
        </form>
      </div>

      <!-- Search Results -->
      <?php if ($results && $results->num_rows > 0): ?>
        <div class="results-container">
          <h4>Search Results</h4>
          <table>
            <thead>
              <tr>
                <th>Drink</th>
                <th>Volume (ml)</th>
                <th>Calories</th>
                <th>Caffeine (mg)</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $results->fetch_assoc()): ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['drink']); ?></td>
                  <td><?php echo htmlspecialchars($row['volume_ml']); ?></td>
                  <td><?php echo htmlspecialchars($row['calories']); ?></td>
                  <td><?php echo htmlspecialchars($row['caffeine_mg']); ?></td>
                  <td>
                    <form method="POST">
                      <input type="hidden" name="mg_coff" value="<?php echo $row['caffeine_mg']; ?>">
                      <button type="submit" class="btn btn-success">Add to Tracker</button>
                    </form>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_query'])): ?>
        <p class="text-center">No results found for "<?php echo htmlspecialchars($_POST['search_query']); ?>"</p>
      <?php endif; ?>

      <!-- Manual Input -->
      <form method="POST" id="caffeine-form">
        <input type="number" id="mg_coffee" name="mg_coff" placeholder="Caffeine in mg" required>
        <button type="submit">Update Tracker</button>
      </form>
    </div>
  </div>

  <script>
    function toggleMenu() {
      const sidebar = document.getElementById("sidebar");
      if (sidebar.style.left === "0px") {
        sidebar.style.left = "-250px"; // Hide sidebar
      } else {
        sidebar.style.left = "0"; // Show sidebar
      }
    }
    
    // Initialize the total consumed caffeine today
    let totalConsumed = <?php echo $totalConsumedToday; ?>;
    const goal = 400; // The caffeine goal (in mg)
    const percentage = Math.min((totalConsumed / goal) * 100, 100); // Calculate the percentage (max 100%)

    // Update the circle based on the total consumed caffeine
    document.querySelector(".circle").style.setProperty("--progress", percentage + "%");
    document.getElementById("percentage").textContent = Math.round(percentage) + "%";

    // JavaScript logic for handling form submission
    document.getElementById('caffeine-form').addEventListener('submit', function(event) {
      event.preventDefault(); // Prevent default form submission

      const inputField = document.getElementById("mg_coffee");
      const consumed = parseInt(inputField.value, 10);
      const goal = 400;

      if (!consumed || consumed <= 0) {
          alert("Please enter a valid amount of caffeine consumed!");
          return;
      }

      // Add consumed caffeine to the global total
      totalConsumed += consumed;
      const percentage = Math.min((totalConsumed / goal) * 100, 100);
      const progress = `${percentage}%`;

      // Update the progress circle
      document.querySelector(".circle").style.setProperty("--progress", progress);
      document.getElementById("percentage").textContent = `${Math.round(percentage)}%`;

      // Update the total consumed display
      document.getElementById("total-consumed").textContent = `Total Consumed: ${totalConsumed}mg`;

      // Clear the input field
      inputField.value = '';

      // Create an AJAX request to submit the form data to the server
      const formData = new FormData();
      formData.append('mg_coff', consumed);

      fetch('', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(data => {
        console.log(data); // Handle the response from the server (optional)
      })
      .catch(error => console.error('Error:', error));
    });
  </script>
</body>
</html>
