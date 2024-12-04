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
        // Generate the last 7 days' labels (e.g., for "Past Week")
        const labels = [
            'Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7'
        ];

        // Bar Chart for the Past 7 Days
        const barCtx = document.getElementById('barChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Daily Caffeine',
                    data: [65, 59, 80, 81, 56, 75, 90],  // Example data for each day
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

        // Line Chart for the Past 7 Days
        const lineCtx = document.getElementById('lineChart').getContext('2d');
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Caffeine (mg)',
                    data: [30, 25, 28, 35, 40, 32, 31],  // Example temperature data for each day
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
                        text: 'Caffeine intake over the Last 7 Days'
                    }
                }
            }
        });
    </script>

</body>
</html>
