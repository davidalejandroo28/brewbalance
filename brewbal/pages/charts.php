<?php
include '../database/db_connect.php';

session_start();

// Check if the user is logged in, if not then redirect them to the login page
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];


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
  
  
  function getCaffeineDataForLast7Days($email, $conn) {
    $data = [];
    $dates = [];
    $total = 0;
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $sql = "SELECT SUM(mg_coff) FROM caffeine_tracker WHERE email = ? AND date = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $date);
        $stmt->execute();
        $stmt->bind_result($total);
        $stmt->fetch();
        $stmt->close();
        $data[] = $total ? $total : 0; // Use 0 if no data
        $dates[] = $date; // Save the date for labels
    }

    return ['data' => $data, 'labels' => $dates];
}

$chartData = getCaffeineDataForLast7Days($email, $conn);

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charts for the Past 7 Days</title>
    <!-- Include Chart.js library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
        }
        .container {
            width: 80%;
            max-width: 800px; /* Set a maximum width for the container */
            text-align: center; /* Center content inside the container */
        }
        h1 {
            margin-bottom: 30px;
        }
        .chart {
            margin-bottom: 30px;
            width: 100%;
            max-width: 600px; /* Limit the chart's max width */
            height: 300px;    /* Set a fixed height for the chart */
            margin-left: auto;
            margin-right: auto; /* Center the chart horizontally */
        }
        .back-btn {
            display: block;
            width: 150px;
            margin: 30px auto;
            padding: 10px;
            text-align: center;
            background-color: #007bff;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .back-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Charts for the Past 7 Days</h1>

        <!-- Bar Chart -->
        <div class="chart">
            <canvas id="barChart"></canvas>
        </div>

        <!-- Line Chart -->
        <div class="chart">
            <canvas id="lineChart"></canvas>
        </div>

        <!-- Back Button -->
        <button class="back-btn" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
    </div>

    <script>
    const labels = <?php echo json_encode($chartData['labels']); ?>;
    const caffeineData = <?php echo json_encode($chartData['data']); ?>;

    // Bar Chart
    const barCtx = document.getElementById('barChart').getContext('2d');
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Daily Caffeine (mg)',
                data: caffeineData,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Line Chart
    const lineCtx = document.getElementById('lineChart').getContext('2d');
    new Chart(lineCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Caffeine Intake (mg)',
                data: caffeineData,
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 2,
                fill: false
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Caffeine Intake Over the Last 7 Days'
                }
            }
        }
    });
</script>


</body>
</html>
