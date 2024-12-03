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
    <link rel="stylesheet" href="style.css">
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
                <input type="text" id="username" name="username" value="<?php echo $username; ?>" disabled>
            </div>

            <div class="form-group">
                <label for="weight">Weight (kg):</label>
                <input type="number" id="weight" name="weight" value="<?php echo $weight; ?>" required>
            </div>

            <div class="form-group">
                <label for="limit_caffeine">Caffeine Limit (mg):</label>
                <input type="number" id="limit_caffeine" name="limit_caffeine" value="<?php echo $limit_caffeine; ?>" required>
            </div>

            <button type="submit" class="btn">Update Profile</button>
        </form>
    </div>
</body>
</html>
