<?php
include '../database/db_connect.php';

$message = "";
$toastClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($password === $confirmPassword) {
        // Prepare and execute
        $stmt = $conn->prepare("UPDATE userdata SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $password, $email);

        if ($stmt->execute()) {
            $message = "Password updated successfully";
            $toastClass = "bg-success";
        } else {
            $message = "Error updating password";
            $toastClass = "bg-danger";
        }

        $stmt->close();
    } else {
        $message = "Passwords do not match";
        $toastClass = "bg-warning";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" 
          content="width=device-width, 
                   initial-scale=1.0">
    <link href=
"https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href=
"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
    <link rel="shortcut icon" href=
"https://cdn-icons-png.flaticon.com/512/295/295128.png">
    <script src=
"https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src=
"https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Reset Password</title>
<style>
        body {
            background-color: #CFBB99; /* Matching the theme */
            font-family: 'Parkinsans', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .reset-container {
            background-color: #E5D7C4; /* Light background for the form */
            border-radius: 10px;
            box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px, rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;
            padding: 30px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .reset-container h5 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #4C3D19; /* Matching title color */
        }
        .form-control {
            border-radius: 5px;
            border: 1px solid #889063;
        }
        .btn-dark {
            background-color: #889063;
            border: none;
            font-weight: bold;
            color: #E5D7C4;
            transition: background-color 0.3s ease;
        }
        .btn-dark:hover {
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
        #email-check {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <?php if ($message): ?>
            <div class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true"
                style="background-color: <?php echo $toastClass === 'bg-success' ? '#28a745' : ($toastClass === 'bg-danger' ? '#dc3545' : ($toastClass === 'bg-warning' ? '#ffc107' : '')); ?>">
                <div class="d-flex">
                    <div class="toast-body">
                        <?php echo $message; ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
        <i class="fa fa-user-circle-o fa-3x mb-3" style="color: #4C3D19;"></i>
        <h5>Change Your Password</h5>
        <form action="" method="post" class="mt-4 position-relative">
            <div class="mb-3 position-relative">
                <label for="email" class="form-label"><i class="fa fa-envelope"></i> Email</label>
                <input type="text" name="email" id="email" class="form-control" required>
                <span id="email-check"></span>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label"><i class="fa fa-lock"></i> Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label"><i class="fa fa-lock"></i> Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-dark w-100">Reset Password</button>
            <p class="mt-4">
                <a href="./register.php" class="text-link">Create Account</a> OR 
                <a href="./login.php" class="text-link">Login</a>
            </p>
        </form>
    </div>
    <script>
        $(document).ready(function () {
            $('#email').on('blur', function () {
                const email = $(this).val();
                if (email) {
                    $.ajax({
                        url: 'check_email.php',
                        type: 'POST',
                        data: { email: email },
                        success: function (response) {
                            if (response === 'exists') {
                                $('#email-check').html('<i class="fa fa-check text-success"></i>');
                            } else {
                                $('#email-check').html('<i class="fa fa-times text-danger"></i>');
                            }
                        }
                    });
                } else {
                    $('#email-check').html('');
                }
            });

            const toastElList = [].slice.call(document.querySelectorAll('.toast'));
            const toastList = toastElList.map(function (toastEl) {
                return new bootstrap.Toast(toastEl, { delay: 3000 });
            });
            toastList.forEach(toast => toast.show());
        });
    </script>
</body>

</html>