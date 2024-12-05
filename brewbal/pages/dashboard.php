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



function getTotalLimit($email, $conn) {
  $username ="";
  $sql = "SELECT username FROM userdata WHERE email = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->bind_result($username);
  $stmt->fetch();
  $stmt->close();

  
  $limit_caffeine = 0;

  $query = "SELECT limit_caffeine FROM usercaffeinedata WHERE username = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($limit_caffeine);
  $stmt->fetch();
  $stmt->close();

  return $limit_caffeine ? $limit_caffeine : 130;
}

$limit_caffeine = getTotalLimit($email, $conn);

$results = null; // Initialize search results
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Check if this is an AJAX request
  $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

  // Handle adding caffeine to the tracker
  if (isset($_POST['mg_coff'])) {
      $mg_coff = intval($_POST['mg_coff']);
      $today = date('Y-m-d');

      // Insert the value into the caffeine_tracker table
      $stmt = $conn->prepare("INSERT INTO caffeine_tracker (email, mg_coff, date) VALUES (?, ?, ?)");
      $stmt->bind_param("sis", $email, $mg_coff, $today);
      $stmt->execute();
      $stmt->close();

      // Respond to AJAX request
      if ($isAjax) {
          echo json_encode([
              'status' => 'success',
              'new_total' => getTotalCaffeineConsumedToday($email, $conn)
          ]);
          exit;
      }
  }

  // Handle search functionality
  if (isset($_POST['search_query'])) {
      $search_query = '%' . $conn->real_escape_string($_POST['search_query']) . '%';
      $stmt = $conn->prepare("SELECT * FROM caffeine_content WHERE drink LIKE ?");
      $stmt->bind_param("s", $search_query);
      $stmt->execute();
      $results = $stmt->get_result();
      $stmt->close();

      if ($isAjax) {
          $drinks = [];
          while ($row = $results->fetch_assoc()) {
              $drinks[] = $row;
          }

          echo json_encode(['status' => 'success', 'results' => $drinks]);
          exit;
      }
  }
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
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Parkinsans:wght@300..800&family=Poppins:wght@100..900&display=swap" rel="stylesheet">
  <title>Coffee Tracker</title>
  
  <style>
body {
      font-family: 'Parkinsans', sans-serif;
      margin: 0;
      padding: 0;
      display: flex;
      flex-direction: column;
      height: auto;
      background-color: #CFBB99;
      justify-content: center; /* Center horizontally */
      align-items: center; /* Center vertically */
      position: relative;
      overflow-y: auto;
    }
    .logo-bottom-left {
    position: fixed;
    bottom: 10px; /* Adjust this value for vertical spacing */
    left: 10px; /* Adjust this value for horizontal spacing */
    z-index: 100; /* Make sure it appears on top of other elements */
    }

    .logo-bottom-left img {
      max-width: 30%; /* Adjust size proportionally based on the screen */
      height: auto; /* Maintain aspect ratio */
      opacity: 0.8; /* Optional transparency */
      transition: opacity 0.3s ease, max-width 0.3s ease;
    }
    /* Media Query for smaller screens */
    @media (max-width: 900px) {
        .logo-bottom-left img {
            max-width: 10%; /* Make the logo smaller on smaller screens */
        }
    }

    @media (max-width: 480px) {
        .logo-bottom-left img {
            max-width: 5%; /* Further adjust size for very small screens */
        }
    }

    .logo-bottom-left img:hover {
        opacity: 1; /* Fully visible on hover */
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
      background-color: #354024;
      color: #E5D7C4;
      padding-top: 60px;
      transition: 0.3s;
      z-index: 2000; /* Ensure sidebar is above the navbar */
    }

    .sidebar a {
      padding: 15px 25px;
      text-decoration: none;
      font-size: 18px;
      color: #E5D7C4;
      display: block;
      transition: 0.3s;
    }

    .sidebar a:hover {
      background-color: #889063;
    }

    .sidebar .close-btn {
      position: absolute;
      top: 20px;
      right: 25px;
      font-size: 36px;
      color: #E5D7C4;
      cursor: pointer;
    }

    /* Top bar styles */
    .top-bar {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 50px;
      background-color: #4C3D19; /* Brown color */
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 20px;
      color: #E5D7C4;
      font-size: 20px;
      font-weight: bold;
      z-index: 1000; /* Lower than the sidebar */
    }
    
    /* Menu toggle button styles */
    .menu-btn {
      background-color: transparent;
      border: none;
      color: #E5D7C4;
      font-size: 24px;
      cursor: pointer;
      margin-left: 10px;
      height: 80%;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background-color 0.3s ease;
      padding: 0 15px;
      
    }

    /* Center Welcome Text */
    .welcome-text {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        font-weight: bold;
    }
    .menu-btn:hover {
      background-color: rgba(0, 0, 0, 0.2); /* Highlight effect on hover */
    }
    
    .top-bar .logout-btn {
      position: absolute;
      right: 40px;
      background-color: #889063;
            color: #E5D7C4;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
    }

    .top-bar .logout-btn:hover {
      background-color: #354024;
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
      margin-top: 5px; /* Space for the top bar */
    }

    .circle {
      width: 300px;
      height: 300px;
      border-radius: 50%;
      background: conic-gradient(#889063 var(--progress), #ddd 0%);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .inner-circle {
      width: 211px;
      height: 211px;
      border-radius: 50%;
      background: #E5D7C4;
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
      color: #E5D7C4;
      background-color: #354024;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    button:hover {
      background-color: #889063;
    }
    .search-container {
      margin: 20px auto;
      text-align: center;
      width: 80%; /* Center the container and make it responsive */
      padding: 15px;
      background-color: #889063; /* Light background for contrast */
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
      border: 1px solid #E5D7C4;
      border-radius: 5px;
    }

    .search-container button {
      padding: 10px 20px;
      font-size: 16px;
      margin-left: 10px;
      color: #E5D7C4;
      background-color: #354024;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .search-container button:hover {
      background-color: #889063;
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
      background-color: #889063;
      font-weight: bold;
    }

    .results-container td {
      background-color: #E5d7c4;
    }

    .results-container button {
      padding: 8px 16px;
      color: #e5d7c4;
      background-color: #354024;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .results-container button:hover {
      background-color: #889063;
    }

    
    #total-consumed {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 10px;
    color: #2B3031;
    }    </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <span class="close-btn" onclick="toggleMenu()">&times;</span>
    <a href="profile.php" class="menu-item">Profile</a>
    <a href="charts.php" class="menu-item">Charts</a>
    <a href="products.php" class="menu-item">Products</a>
    <a href="info.php" class="menu-item">Info</a>
  </div>

  <!-- Top brown bar with dynamic welcome message -->
  <div class="top-bar">
  <button class="menu-btn" onclick="toggleMenu()">&#9776;</button>
  <div class="welcome-text">Welcome, <?php echo htmlspecialchars($username); ?></div>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>
  <div class="main-content">
    <div class="tracker-container">
      <div id="total-consumed">Total Consumed: <?php echo $totalConsumedToday; ?> mg</div>
      <div class="circle" style="--progress: 0%;">
        <div class="inner-circle" id="percentage">0%</div>
      </div>
      
      <form method="POST" id="caffeine-form">
        <input type="number" id="mg_coffee" name="mg_coff" placeholder="Caffeine in mg" required>
        <button type="submit">Update Tracker</button>
      </form>
    </div>

    <div class="search-container">
          <h3>Search for a Drink</h3>
          <form method="POST">
            <input type="text" name="search_query" placeholder="Search for a drink..." required>
            <button type="submit" class="btn btn-secondary">Search</button>
          </form>
      </div>


      <?php if ($results && $results->num_rows > 0): ?>
        <div class="results-container">
          <h4>Search Results</h4>
          <!-- Button to hide results -->
          <button id="hideResultsBtn" class="btn btn-secondary" onclick="toggleResults()">Hide Results</button>
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
                  <button type="button" class="btn btn-success add-to-tracker" data-caffeine="<?php echo $row['caffeine_mg']; ?>">
                      Add to Tracker
                  </button>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_query'])): ?>
        <p class="text-center">No results found for "<?php echo htmlspecialchars($_POST['search_query']); ?>"</p>
      <?php endif; ?>  
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
    const goal = <?php echo $limit_caffeine; ?>; // The caffeine goal (in mg)
    const percentage = Math.min((totalConsumed / goal) * 100, 100); // Calculate the percentage (max 100%)

    // Update the circle based on the total consumed caffeine
    document.querySelector(".circle").style.setProperty("--progress", percentage + "%");
    document.getElementById("percentage").textContent = Math.round(percentage) + "%";

    // JavaScript logic for handling form submission
  document.getElementById('caffeine-form').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent default form submission

    const inputField = document.getElementById("mg_coffee");
    const consumed = parseInt(inputField.value, 10);

    if (!consumed || consumed <= 0) {
      alert("Please enter a valid amount of caffeine consumed!");
      return;
    }

    // Add consumed caffeine to the global total
    totalConsumed += consumed;
    const percentage = Math.min((totalConsumed / goal) * 100, 100); // Ensure percentage doesn't exceed 100
    const progress = `${percentage}%`;

    // Update the progress circle
    const circle = document.querySelector(".circle");
    circle.style.setProperty("--progress", progress);
    document.getElementById("percentage").textContent = `${Math.round(percentage)}%`;

    // Change circle color to red if percentage is 100% or above
    if (percentage >= 100) {
        circle.style.background = `conic-gradient(#843221 ${progress}, #ddd 0%)`;

        // Display warning popup
        alert("Warning: You have exceeded your caffeine limit!");
    } else if (percentage >= 75) {
        circle.style.background = `conic-gradient(#9a4c21 ${progress}, #ddd 0%)`; // Yellow-Orange for nearing the limit
        alert("Warning: You are approaching your caffeine limit!");
    }else {
        // Reset to green if under the limit
        circle.style.background = `conic-gradient(#889063 ${progress}, #ddd 0%)`;
    }

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

// Event listener for adding caffeine to the tracker
document.querySelectorAll('.add-to-tracker').forEach(button => {
    button.addEventListener('click', function() {
        const mg_coffee = parseInt(this.getAttribute('data-caffeine'), 10);
        
        if (!mg_coffee || mg_coffee <= 0) {
            alert("Invalid caffeine value!");
            return;
        }

        // Add the caffeine to the global total
        totalConsumed += mg_coffee;
        const percentage = Math.min((totalConsumed / goal) * 100, 100); // Ensure percentage doesn't exceed 100
        const progress = `${percentage}%`;

        // Update the progress circle
        const circle = document.querySelector(".circle");
        circle.style.setProperty("--progress", progress);
        document.getElementById("percentage").textContent = `${Math.round(percentage)}%`;

        // Change circle color to red if percentage is 100% or above
        if (percentage >= 100) {
            circle.style.background = `conic-gradient(#843221 ${progress}, #ddd 0%)`;

            // Display warning popup
            alert("Warning: You have exceeded your caffeine limit!");
        } else if (percentage >= 75) {
            circle.style.background = `conic-gradient(#9a4c21 ${progress}, #ddd 0%)`; // Yellow-Orange for nearing the limit
            alert("Warning: You are approaching your caffeine limit!");
        }else {
            // Reset to green if under the limit
            circle.style.background = `conic-gradient(#889063 ${progress}, #ddd 0%)`;
        }

        // Update the total consumed display
        document.getElementById("total-consumed").textContent = `Total Consumed: ${totalConsumed}mg`;

        // Send the updated value to the server via AJAX
        const formData = new FormData();
        formData.append('mg_coff', mg_coffee);

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Optionally update the total consumed after server-side processing
                document.getElementById("total-consumed").textContent = `Total Consumed: ${data.new_total}mg`;
            }
        })
        .catch(error => console.error('Error:', error));
    });
});

function toggleResults() {
    const resultsContainer = document.querySelector('.results-container');
    const hideResultsBtn = document.getElementById('hideResultsBtn');
    
    // Toggle visibility
    if (resultsContainer.style.display === 'none') {
      resultsContainer.style.display = 'block';
      hideResultsBtn.textContent = 'Hide Results'; // Change button text back
    } else {
      resultsContainer.style.display = 'none';
      hideResultsBtn.textContent = 'Show Results'; // Change button text to "Show Results"
    }
  }



  </script>
  <!-- Logo Section -->
  <div class="logo-bottom-left">
        <img src="logo.png" alt="Brew Balance Logo">
  </div>
</body>
</html>
