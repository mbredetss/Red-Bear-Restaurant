<?php include 'auth.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register Red Bear</title>

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
    .row input[type="email"],
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

    .pass,
    .signup-link {
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
  </style>
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
      <div class="signup-link">
        Already have an account? <a href="login.php">Login now</a>
      </div>
    </form>
  </div>
</body>

</html>
