<?php
include '../database/db_connect.php';

$message = "";
$toastClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and execute
    $stmt = $conn->prepare("SELECT password FROM userdata WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($db_password);
        $stmt->fetch();

        if ($password === $db_password) {
            $message = "Login successful";
            $toastClass = "bg-success";
            // Start the session and redirect to the dashboard or home page
            session_start();
            $_SESSION['email'] = $email;
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "Incorrect password";
            $toastClass = "bg-danger";
        }
    } else {
        $message = "Email not found";
        $toastClass = "bg-warning";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" 
          content="width=device-width, initial-scale=1.0">
    <link href=
"https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href=
"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
    <link rel="shortcut icon" href=
"https://cdn-icons-png.flaticon.com/512/295/295128.png">
    <script src=
"https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../css/login.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Parkinsans:wght@300..800&family=Poppins:wght@100..900&display=swap" rel="stylesheet">
    <title>Login Page</title>

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
            text-align: center; /* Adjust spacing from the top */
        }

        .logo-top-center img {
            width: 200px; /* Adjust the size of the logo */
            height: auto;
        }
        .login-container {
            background-color: #E5D7C4; /* Light background for the form */
            border-radius: 10px;
            box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px, rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;
            padding: 30px;
            width: 100%;
            max-width: 600px;
            text-align: center;
        }
        .login-container img {
            width: 100px;
            height: auto;
            margin-bottom: 20px;
        }
        .login-container h5 {
            font-family: 'Parkinsans', sans-serif;
            font-size: 1.5rem;
            font-weight: bold;
            color: #4C3D19; /* Matching title color */
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
    <!-- Logo Section -->
    <div class="logo-top-center">
        <img src="logo.png" alt="Brew Balance Logo">
    </div>
    <div class="login-container">
        <?php if ($message): ?>
            <div class="toast align-items-center text-white <?php echo $toastClass; ?> border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <?php echo $message; ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
        <i class="fa fa-user-circle-o fa-3x mb-3" style="color: #4C3D19;"></i>
        <h5>Login Into Your Account</h5>
        <form action="" method="post" class="mt-4">
            <div class="mb-3">
                <label for="email" class="form-label"><i class="fa fa-envelope"></i> Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label"><i class="fa fa-lock"></i> Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Login</button>
            <p class="mt-4">
                <a href="./register.php" class="text-link">Create Account</a> OR 
                <a href="./resetpassword.php" class="text-link">Forgot Password</a>
            </p>
        </form>
    </div>
    <script>
        var toastElList = [].slice.call(document.querySelectorAll('.toast'))
        var toastList = toastElList.map(function (toastEl) {
            return new bootstrap.Toast(toastEl, { delay: 3000 });
        });
        toastList.forEach(toast => toast.show());
    </script>
</body>

</html>