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
            font-family: 'Parkinsans', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #CFBB99;
            overflow-x: hidden;
            color: #E5d7c4;
        }
        .main-content {
            padding: 65px 20px 20px; /* Leave space for the top bar */
            max-width: 1200px;
            margin: auto;
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
        .top-bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 40px;
            background-color: #4C3D19; /* Brown color */
            display: flex;
            align-items: center;
            padding: 0 20px;
            color: #E5D7C4;
            font-size: 20px;
            font-weight: bold;
            z-index: 1000;
        }
        .menu-btn {
            font-size: 24px;
            background-color: transparent;
            border: none;
            color: #E5D7C4;
            cursor: pointer;
            margin-right: 15px;
        }
        .top-bar .welcome-text {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            font-weight: bold;
        }
        .sidebar {
            height: 100%;
            width: 250px;
            position: fixed;
            top: 0;
            left: -250px;
            background-color: #354024;
            color: #E5D7C4;
            padding-top: 60px;
            transition: 0.3s;
            z-index: 2000;
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
        .close-btn {
            position: absolute;
            top: 20px;
            right: 25px;
            font-size: 36px;
            color: #E5D7C4;
            cursor: pointer;
        }
        .container {
            width: 80%;
            max-width: 800px; /* Set a maximum width for the container */
            text-align: center; /* Center content inside the container */
        }
        h1 {
            margin-bottom: 30px;
        }
        .chart-container {
            background-color: #E5D7C4;
            border-radius: 10px;
            padding: 20px;
            margin: 20px auto;
            text-align: center;
            margin: 15px auto; /* Added extra margin for spacing */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .chart {
            margin-bottom: 30px;
            width: 100%;
            max-width: 600px; /* Limit the chart's max width */
            height: 260px;    /* Set a fixed height for the chart */
            margin-left: auto;
            margin-right: auto; /* Center the chart horizontally */
        }
        h2 {
            color: #4C3D19; 
            margin-bottom: 20px;
            font-size: 1.8rem;
            font-weight: bold;
            text-align: center;
        }
        .back-btn {
            display: block;
            width: 150px;
            margin: 30px auto;
            padding: 10px;
            text-align: center;
            background-color: #889063;
            color: #4C3D19;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .back-btn:hover {
            background-color: #354024;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <button class="menu-btn" onclick="toggleSidebar()">☰</button>
        <span class="welcome-text">Charts for the Past 7 Days</span>
    </div>
     <!-- Sidebar -->
     <div class="sidebar" id="sidebar">
        <span class="close-btn" onclick="toggleSidebar()">&times;</span>
        <a href="dashboard.php">Dashboard</a>
        <a href="charts.php">Charts</a>
        <a href="info.php">Info</a>
    </div>
    <div class="container">
        <div class="main-content">
            <!-- Bar Chart -->
            <div class="chart-container">
                <h2>Daily Caffeine Intake</h2>
                <div class="chart">
                    <canvas id="barChart"></canvas>
                </div>
            </div>

            <!-- Line Chart -->
            <div class="chart-container">
                <h2>Caffeine Intake Trends</h2>
                <div class="chart">
                    <canvas id="lineChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="logo-bottom-left">
        <img src="logo.png" alt="Brew Balance Logo">
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById("sidebar");
            if (sidebar.style.left === "0px") {
                sidebar.style.left = "-250px";
            } else {
                sidebar.style.left = "0";
            }
        }
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
            backgroundColor: 'rgba(136, 144, 99, 0.9)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                grid: {
                    color: '#354024', // Color of grid lines for the x-axis
                },
                ticks: {
                    color: '#354024', // Color of x-axis labels
                    font: {
                        size: 14, // Font size for x-axis labels
                        family: 'Arial', // Font family for x-axis labels
                        weight: 'bold', // Font weight for x-axis labels
                    }
                }
            },
            y: {
                grid: {
                    color: '#354024', // Color of grid lines for the y-axis
                },
                ticks: {
                    color: '#354024', // Color of y-axis labels
                    font: {
                        size: 14, // Font size for y-axis labels
                        family: 'Arial', // Font family for y-axis labels
                        weight: 'bold', // Font weight for y-axis labels
                    }
                },
                beginAtZero: true
            }
        },
        plugins: {
            legend: {
                labels: {
                    color: '#354024', // Color of the legend text
                    font: {
                        size: 14, // Font size for legend text
                        family: 'Arial', // Font family for legend text
                        weight: 'bold', // Font weight for legend text
                    }
                }
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
            borderColor: 'rgba(53, 64, 36, 1)',
            backgroundColor: 'rgba(53, 64, 36, 1)',
            borderWidth: 2,
            fill: false
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                grid: {
                    color: '#4C3D19', // Color of grid lines for the x-axis
                },
                ticks: {
                    color: '#4C3D19', // Color of x-axis labels
                    font: {
                        size: 14, // Font size for x-axis labels
                        family: 'Arial', // Font family for x-axis labels
                        weight: 'bold', // Font weight for x-axis labels
                    }
                }
            },
            y: {
                grid: {
                    color: '#4C3D19', // Color of grid lines for the y-axis
                },
                ticks: {
                    color: '#4C3D19', // Color of y-axis labels
                    font: {
                        size: 14, // Font size for y-axis labels
                        family: 'Arial', // Font family for y-axis labels
                        weight: 'bold', // Font weight for y-axis labels
                    }
                },
                beginAtZero: true
            }
        },
        plugins: {
            legend: {
                labels: {
                    color: '#4C3D19', // Color of the legend text
                    font: {
                        size: 14, // Font size for legend text
                        family: 'Arial', // Font family for legend text
                        weight: 'bold', // Font weight for legend text
                    }
                }
            },
            title: {
                display: true,
                text: 'Caffeine Intake Over the Last 7 Days',
                color: '#4C3D19', // Color of the chart title
                font: {
                    size: 18, // Font size for the chart title
                    family: 'Arial', // Font family for the chart title
                    weight: 'bold', // Font weight for the chart title
                }
            }
        }
    }
});
</script>


</body>
</html>
