<?php
include 'auth.php'; // Mengimpor file auth.php
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login Red Bear</title>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  <style>
    @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap");

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Poppins", sans-serif;
    }

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
      background-color: rgba(0, 0, 0, 0.5);
      padding: 30px;
      border-radius: 10px;
      max-width: 400px;
      width: 100%;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
      animation: fadeIn 1s ease-in-out;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .title span {
      color: white;
      font-size: 24px;
      font-weight: bold;
      display: block;
      text-align: center;
      margin-bottom: 15px;
    }

    .welcome {
      color: #fff;
      text-align: center;
      margin-bottom: 20px;
      font-size: 14px;
    }

    form {
      display: flex;
      flex-direction: column;
    }

    .row {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
      position: relative;
    }

    .row i {
      color: white;
      margin-right: 10px;
      font-size: 16px;
    }

    .row input[type="text"],
    .row input[type="password"] {
      flex: 1;
      background-color: rgba(255, 255, 255, 0.2);
      border: none;
      padding: 10px;
      color: white;
      border-radius: 5px;
      transition: background-color 0.3s;
    }

    .row input:focus {
      background-color: rgba(255, 255, 255, 0.3);
      outline: none;
    }

    input::placeholder {
      color: #ddd;
    }

    .pass {
      text-align: right;
      margin-top: -5px;
    }

    .pass a,
    .signup-link a {
      color: red;
      font-size: 14px;
      text-decoration: none;
    }

    .signup-link {
      text-align: center;
      margin-top: 15px;
      color: white;
    }

    .signup-link a:hover,
    .pass a:hover {
      text-decoration: underline;
    }

    input[type="submit"] {
      background-color: white;
      color: red;
      font-weight: bold;
      border: none;
      padding: 10px;
      width: 100%;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    input[type="submit"]:hover {
      background-color: #ffcccc;
    }

    #login-error-message {
      text-align: center;
      margin-top: 10px;
      color: white;
    }

    .toggle-password {
      position: absolute;
      right: 10px;
      color: white;
      cursor: pointer;
    }
  </style>
</head>

<body>
  <div class="wrapper">
    <div class="title"><span>Login Form</span></div>
    <div class="welcome">
      Selamat datang di <strong>Red Bear Restaurant</strong>!<br>Silakan login untuk melanjutkan.
    </div>

    <form action="script/php/login_backend.php" method="POST">
      <div class="row">
        <i class="fas fa-user"></i>
        <input type="text" name="email" placeholder="Email" required />
      </div>
      <div class="row">
        <i class="fas fa-lock"></i>
        <input type="password" name="password" placeholder="Password" id="password" required />
        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
      </div>
      <div class="row button">
        <input type="submit" value="Login" />
      </div>
      <div class="signup-link">Not a member? <a href="register.php">Signup now</a></div>
    </form>

    <div id="login-error-message" style="display: none;">Email atau password salah.</div>
  </div>

  <script>
    // Toggle show/hide password
    const togglePassword = document.getElementById("togglePassword");
    const passwordField = document.getElementById("password");

    togglePassword.addEventListener("click", () => {
      const type = passwordField.getAttribute("type") === "password" ? "text" : "password";
      passwordField.setAttribute("type", type);
      togglePassword.classList.toggle("fa-eye");
      togglePassword.classList.toggle("fa-eye-slash");
    });
  </script>
</body>

</html>
