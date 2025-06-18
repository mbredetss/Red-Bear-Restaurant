<?php
session_start();

echo json_encode(['loggedIn' => isset($_SESSION['user_id'])]);