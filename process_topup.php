<?php
session_start();
require_once 'database.php';

// Create DB connection
$connection = connectDB();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$amount  = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

// Where to redirect after top-up
$redirect = $_POST['redirect_url'] ?? 'index.php';
if (strpos($redirect, 'http') === 0) {
    $redirect = 'index.php';
}

if ($amount <= 0) {
    die("Invalid top-up amount.");
}

$sql  = "UPDATE user SET balance = balance + ? WHERE user_id = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("di", $amount, $user_id);
$stmt->execute();
$stmt->close();

// If we are going back to home page, keep the success flag; 
// otherwise just go back to the previous page.
if ($redirect === 'index.php') {
    header("Location: index.php?topup=success");
} else {
    header("Location: " . $redirect);
}
exit;
