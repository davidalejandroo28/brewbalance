<?php
include '../database/db_connect.php';

$message = "";
$toastClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $checkEmailStmt = $conn->prepare("SELECT email FROM userdata WHERE email = ?");
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    $checkEmailStmt->store_result();
    
    if ($checkEmailStmt->num_rows > 0) {
        $message = "Email ID already exists";
        $toastClass = "#007bff"; // Primary color
    } else {
        // Check if username exists
        $checkUsernameStmt = $conn->prepare("SELECT username FROM userdata WHERE username = ?");
        $checkUsernameStmt->bind_param("s", $username);
        $checkUsernameStmt->execute();
        $checkUsernameStmt->store_result();
    
        if ($checkUsernameStmt->num_rows > 0) {
            $message = "Username already exists";
            $toastClass = "#007bff"; // Primary color
        } else {
            // Prepare and bind for insertion
            $stmt = $conn->prepare("INSERT INTO userdata (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $password);
    
            if ($stmt->execute()) {
                $message = "Account created successfully";
                $toastClass = "#28a745"; // Success color
    
                // Insert into user caffeine data
                $insertCaffeineStmt = $conn->prepare("INSERT INTO usercaffeinedata (username, average_week_consumption, weight, limit_caffeine) VALUES (?, 0, 0, 0)");
                $insertCaffeineStmt->bind_param("s", $username);
                $insertCaffeineStmt->execute();
                $insertCaffeineStmt->close();
            } else {
                $message = "Error: " . $stmt->error;
                $toastClass = "#dc3545"; // Danger color
            }
    
            $stmt->close();
        }
    
        $checkUsernameStmt->close();
    }
    
    $checkEmailStmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href=
"https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href=
"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
    <link rel="shortcut icon" href=
"https://cdn-icons-png.flaticon.com/512/295/295128.png">
    <script src=
"https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Parkinsans:wght@300..800&family=Poppins:wght@100..900&display=swap" rel="stylesheet">
    <title>Registration</title>
    <style>
        body {
            background-color: #CFBB99;
            font-family: 'Parkinsans', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .registration-container {
            background-color: #E5D7C4;
            border-radius: 10px;
            box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px, rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;
            padding: 30px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .registration-container h5 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #4C3D19;
        }
        .form-control {
            border-radius: 5px;
            border: 1px solid #889063;
        }
        .btn-success {
            background-color: #889063;
            border: none;
            font-weight: bold;
            color: #E5D7C4;
            transition: background-color 0.3s ease;
        }
        .btn-success:hover {
            background-color: #4C3D19;
        }
        .text-link {
            font-weight: bold;
            color: #4C3D19;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .text-link:hover {
            color: #889063;
        }
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1055;
        }
    </style>
</head>

<body>
    <div class="registration-container">
        <?php if ($message): ?>
            <div class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true"
                style="background-color: <?php echo $toastClass; ?>;">
                <div class="d-flex">
                    <div class="toast-body">
                        <?php echo $message; ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
        <i class="fa fa-user-circle-o fa-3x mb-3" style="color: #4C3D19;"></i>
        <h5>Create Your Account</h5>
        <form method="post" class="mt-4 position-relative">
            <div class="mb-3">
                <label for="username" class="form-label"><i class="fa fa-user"></i> Username</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label"><i class="fa fa-envelope"></i> Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label"><i class="fa fa-lock"></i> Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Create Account</button>
            <p class="mt-4">
                Already have an account? <a href="./login.php" class="text-link">Login</a>
            </p>
        </form>
    </div>
    <script>
        const toastElList = [].slice.call(document.querySelectorAll('.toast'));
        const toastList = toastElList.map(function (toastEl) {
            return new bootstrap.Toast(toastEl, { delay: 3000 });
        });
        toastList.forEach(toast => toast.show());
    </script>
</body>

</html>
