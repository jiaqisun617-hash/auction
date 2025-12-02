<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'database.php';

?>

<?php include 'header.php'; ?>

<div class="container my-5">
    <h2>Add Balance</h2>
    <form action="process_topup.php" method="POST">
        <div class="mb-3">
            <label for="amount" class="form-label">Top-up Amount (£):</label>
            <input type="number" step="0.01" min="1" class="form-control" name="amount" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Funds</button>
    </form>
</div>

<?php include 'footer.php'; ?>
