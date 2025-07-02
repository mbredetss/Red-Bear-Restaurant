<?php
include 'auth.php'; // Mengimpor file auth.php
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login Red Bear</title>
    <link rel="stylesheet" href="style/style.css" />
    <!-- Font Awesome CDN link for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />

    <style>
        body {
            background-image: url('../img/image3.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .wrapper {
            background-color: rgba(0, 0, 0, 0.5); /* Merah transparan */
            padding: 30px;
            border-radius: 10px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        }

        .title span {
            color: white;
            font-size: 24px;
            font-weight: bold;
        }

        .row input,
        .row i,
        .pass a,
        .signup-link,
        .signup-link a {
            color: white;
        }

        input[type="text"],
        input[type="password"] {
            background-color: rgba(255, 255, 255, 0.2);
            border: none;
            padding: 10px;
            color: white;
        }

        input[type="submit"] {
            background-color: white;
            color: red;
            font-weight: bold;
            border: none;
            padding: 10px;
            cursor: pointer;
            transition: 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #ffcccc;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="title"><span>Login Form</span></div>
        <form action="script/php/login_backend.php" method="POST">
            <div class="row">
                <i class="fas fa-user"></i>
                <input type="text" name="email" placeholder="Email" required />
            </div>
            <div class="row">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required />
            </div>
            <div class="pass"><a href="#">Forgot password?</a></div>
            <div class="row button">
                <input type="submit" value="Login" />
            </div>
            <div class="signup-link">Not a member? <a href="register.php">Signup now</a></div>
        </form>

        <div id="login-error-message" style="display: none; color: white;">Email atau password salah.</div>
    </div>

    <script src="script/js/script.js"></script>
</body>

</html>
