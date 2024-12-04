<?php
include '../database/db_connect.php';

function getTotalCaffeineToday($email, $pdo) {

}

// Include database connection

header('Content-Type: application/json');

// Get the raw POST data from the request body
$data = json_decode(file_get_contents('php://input'), true);

// Check if the required fields are present in the request
if (isset($data['email']) && isset($data['consumed']) && isset($data['date'])) {
    $email = $data['email'];
    $consumed = $data['consumed'];
    $date = $data['date'];

    // Prepare SQL query to insert the data into the coffee_tracker table
    $sql = "INSERT INTO coffee_tracker (email, caffeine_amount, date) VALUES (?, ?, ?)";
    
    // Prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters to the SQL query
        $stmt->bind_param("sis", $email, $consumed, $date);
        
        // Execute the query
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Data inserted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to insert data']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database prepare error']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
}

$conn->close();
?>

