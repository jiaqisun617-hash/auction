<?php
session_start();
require_once 'database.php';
require_once 'header.php';

$connection = connectDB();
$user_id = (int)$_SESSION['user_id'];

$stmt = $connection->prepare("SELECT username, email, balance FROM user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email, $balance);
$stmt->fetch();
$stmt->close();
?>

<div class="container my-5">
    <h2>My Account</h2>
    <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
    <p><strong>Balance:</strong> £<?php echo number_format($balance, 2); ?></p>

    <?php if (isset($_GET['topup']) && $_GET['topup'] === 'success'): ?>
        <div class="alert alert-success mt-3">
            Top-up successful!
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
