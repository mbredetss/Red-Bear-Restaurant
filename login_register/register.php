<?php
include 'auth.php'; // Mengimpor file auth.php
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register Form</title>
    <link rel="stylesheet" href="style/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
</head>

<body>
    <div class="wrapper">
        <div class="title"><span>Register Form</span></div>
        <form action="script/php/register_backend.php" method="POST">
            <div class="row">
                <i class="fas fa-user"></i>
                <input type="text" name="name" placeholder="Full Name" required />
            </div>
            <div class="row">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Email" required />
            </div>
            <div class="row">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required />
            </div>
            <div class="row button">
                <input type="submit" value="Register" />
            </div>
            <div class="signup-link">Already have an account? <a href="login.php">Login now</a></div>
        </form>
    </div>
    <script src="script/js/script.js"></script>
</body>

</html>