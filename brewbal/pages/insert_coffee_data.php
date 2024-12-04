<?php
include '../database/db_connect.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    echo "User not logged in.";
    exit();
}


if ($stmt->execute()) {
    echo "Data inserted successfully";
} else {
    echo "Error inserting data: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
