<?php
session_start(); // Start the session to get the logged-in user's details
include '../database/db_connect.php';
// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

$email = $_SESSION['email'];
// Get the username from the session
$username = '';
if ($stmt = $conn->prepare("SELECT username FROM userdata WHERE email = ?")) {
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($username);
    $stmt->fetch();
    $stmt->close();
}
// Fetch current data from the database
$query = "SELECT weight, limit_caffeine FROM usercaffeinedata WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($weight, $limit_caffeine);
$stmt->fetch();
$stmt->close();

// Handle form submission to update the data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_weight = $_POST['weight'];
    $new_limit_caffeine = $_POST['limit_caffeine'];

    if (empty($new_limit_caffeine)) {
        $new_limit_caffeine = $new_weight * 2;
    }

    // Update user data in the database
    $updateQuery = "UPDATE usercaffeinedata SET weight = ?, limit_caffeine = ? WHERE username = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("dds", $new_weight, $new_limit_caffeine, $username);

    if ($updateStmt->execute()) {
        $message = "Profile updated successfully!";
        $message_class = "success"; // Success message class
    } else {
        $message = "Error updating profile: " . $updateStmt->error;
        $message_class = "error"; // Error message class
    }
    $updateStmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #444;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .button-group {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 20px;
        }
        .btn {
            flex: 1;
            padding: 10px;
            font-size: 16px;
            color: #fff;
            background-color: #007BFF;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .message {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>User Profile</h1>

        <?php if (isset($message)) { ?>
            <div class="message <?php echo $message_class; ?>"><?php echo $message; ?></div>
        <?php } ?>

        <form method="POST" action="profile.php">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" disabled>
            </div>

            <div class="form-group">
                <label for="weight">Weight (kg):</label>
                <input type="number" id="weight" name="weight" value="<?php echo htmlspecialchars($weight); ?>" required>
                </div>

            <div class="form-group">
                <label for="limit_caffeine">Caffeine Limit (mg):</label>
                <input type="number" id="limit_caffeine" name="limit_caffeine" value="<?php echo htmlspecialchars($limit_caffeine); ?>" required>
            </div>

            <div class="button-group">
                <button type="submit" class="btn">Update Profile</button>
                <a href="dashboard.php" class="btn btn-secondary">Go to Dashboard</a>
            </div>
        </form>
    </div>
</body>
</html>
