<?php
session_start();
require_once 'database.php';

// 建立数据库连接（确保 $connection 存在）
$connection = connectDB();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$amount  = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

if ($amount <= 0) {
    die("Invalid top-up amount.");
}

$sql = "UPDATE user SET balance = balance + ? WHERE user_id = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("di", $amount, $user_id);
$stmt->execute();
$stmt->close();

header("Location: index.php?topup=success");
exit;
