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

$query = "SELECT limit_caffeine FROM usercaffeinedata WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($limit_caffeine);
$stmt->fetch();
$stmt->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <style>
        body {
            background-color: #CFBB99; /* Matching the theme */
            font-family: 'Parkinsans', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            flex-direction: column;
        }
        .logo-top-center {
            text-align: center; /* Center the logo */
        }

        .logo-top-center img {
            width: 150px; /* Adjust the size of the logo */
            height: auto;
        }

        .profile-container {
            background-color: #E5D7C4; /* Light background for the form */
            border-radius: 10px;
            box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px, rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;
            padding: 30px;
            width: 100%;
            max-width: 600px;
            text-align: center;
        }

        .profile-container h1 {
            font-size: 1.8rem;
            font-weight: bold;
            color: #4C3D19; /* Matching title color */
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #4C3D19;
        }

        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #889063;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .btn {
            flex: 1;
            padding: 10px;
            font-size: 16px;
            color: #E5D7C4;
            background-color: #889063;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #4C3D19;
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
    <!-- Logo Section -->
    <div class="logo-top-center">
        <img src="logo.png" alt="Brew Balance Logo">
    </div>

    <div class="profile-container">
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

            <div class="form-group">
                <button type="submit" class="btn">Update Profile</button>
                <a href="dashboard.php" class="btn btn-secondary">Go to Dashboard</a>
            </div>
        </form>
    </div>
</body>
</html>

