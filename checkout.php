<?php require("utilities.php"); ?>
<?php require_once("database.php"); ?>
<?php include_once("header.php"); ?>

<?php
// User must be logged in
if (!isset($_SESSION['user_id'])) {
    echo "<div class='container my-5'><div class='alert alert-danger'>You must log in to pay for an auction.</div></div>";
    include_once("footer.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

if (!isset($_GET['auction_id']) || !is_numeric($_GET['auction_id'])) {
    echo "<div class='container my-5'><div class='alert alert-danger'>Invalid auction.</div></div>";
    include_once("footer.php");
    exit();
}

$auction_id = (int)$_GET['auction_id'];
$conn       = connectDB();

// Load auction + item info
$sql = "
    SELECT 
        a.auction_id,
        a.item_id,
        a.reserve_price AS deposit_required,
        a.end_time,
        a.is_paid,
        i.title
    FROM Auction a
    JOIN Item i ON a.item_id = i.item_id
    WHERE a.auction_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $auction_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "<div class='container my-5'><div class='alert alert-danger'>Auction not found.</div></div>";
    include_once("footer.php");
    exit();
}

$row = $res->fetch_assoc();

$item_title       = $row['title'];
$deposit_required = (float)$row['deposit_required'];
$end_time         = new DateTime($row['end_time']);
$is_paid          = (int)$row['is_paid'];

$now = new DateTime();
if ($now <= $end_time) {
    echo "<div class='container my-5'><div class='alert alert-warning'>This auction has not ended yet. You cannot pay at this time.</div></div>";
    include_once("footer.php");
    exit();
}

// Find winning bidder and final price
$sql_win = "
    SELECT b.bidder_id, b.bid_amount
    FROM Bid b
    WHERE b.auction_id = ?
    ORDER BY b.bid_amount DESC, b.bid_time ASC
    LIMIT 1
";
$stmt_win = $conn->prepare($sql_win);
$stmt_win->bind_param("i", $auction_id);
$stmt_win->execute();
$res_win = $stmt_win->get_result();

if ($res_win->num_rows === 0) {
    echo "<div class='container my-5'><div class='alert alert-info'>No bids were placed on this auction. There is nothing to pay.</div></div>";
    include_once("footer.php");
    exit();
}

$win_row     = $res_win->fetch_assoc();
$winner_id   = (int)$win_row['bidder_id'];
$final_price = (float)$win_row['bid_amount'];

// Make sure current user is the winner
if ($winner_id !== $user_id) {
    echo "<div class='container my-5'><div class='alert alert-danger'>You are not the winner of this auction, so you cannot pay for it.</div></div>";
    include_once("footer.php");
    exit();
}

// Calculate amount due (cannot be negative)
$amount_due = max($final_price - $deposit_required, 0.0);

// Load current balance
$sql_bal = "SELECT balance FROM user WHERE user_id = ?";
$stmt_bal = $conn->prepare($sql_bal);
$stmt_bal->bind_param("i", $user_id);
$stmt_bal->execute();
$bal_row = $stmt_bal->get_result()->fetch_assoc();
$current_balance = $bal_row ? (float)$bal_row['balance'] : 0.0;

// Messages for UI
$payment_success = false;
$error_message   = "";

// If no amount due and not marked as paid, mark as paid automatically
if ($amount_due <= 0 && !$is_paid) {
    $sql_mark_paid = "UPDATE Auction SET is_paid = 1 WHERE auction_id = ?";
    $stmt_mp = $conn->prepare($sql_mark_paid);
    $stmt_mp->bind_param("i", $auction_id);
    $stmt_mp->execute();
    $is_paid = 1;
}

// Handle POST (fake payment submit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_paid && $amount_due > 0) {

    if ($current_balance < $amount_due) {
        $error_message = "Your wallet balance (£" . number_format($current_balance, 2) .
                         ") is not enough to pay £" . number_format($amount_due, 2) . ". Please top up first.";
    } else {
        // Deduct from user balance and mark auction as paid
        $conn->begin_transaction();

        try {
            $sql_pay = "UPDATE user SET balance = balance - ? WHERE user_id = ?";
            $stmt_pay = $conn->prepare($sql_pay);
            $stmt_pay->bind_param("di", $amount_due, $user_id);
            $stmt_pay->execute();

            $sql_paid = "UPDATE Auction SET is_paid = 1 WHERE auction_id = ?";
            $stmt_paid = $conn->prepare($sql_paid);
            $stmt_paid->bind_param("i", $auction_id);
            $stmt_paid->execute();

            $conn->commit();

            $payment_success   = true;
            $is_paid           = 1;
            $current_balance  -= $amount_due;

        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Something went wrong while processing your payment. Please try again.";
        }
    }
}
?>

<div class="container my-5">
  <h2 class="mb-4">Complete your payment</h2>

  <?php if ($payment_success): ?>
    <div class="alert alert-success">
      Payment successful! Thank you for your purchase.
      <br>
      <a href="mybids.php" class="alert-link">View your bids</a> or 
      <a href="listing.php?item_id=<?php echo $row['item_id']; ?>" class="alert-link">return to this item</a>.
    </div>
  <?php elseif ($is_paid && $amount_due > 0): ?>
    <div class="alert alert-info">
      This auction has already been marked as paid.
    </div>
  <?php elseif ($is_paid && $amount_due <= 0): ?>
    <div class="alert alert-info">
      Your deposit fully covered the final price. No additional payment is required, and the auction is marked as paid.
    </div>
  <?php endif; ?>

  <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger">
      <?php echo htmlspecialchars($error_message); ?>
    </div>
  <?php endif; ?>

  <div class="row">
    <div class="col-md-5 mb-4">
      <div class="card">
        <div class="card-header">
          Order summary
        </div>
        <div class="card-body">
          <p class="mb-1"><strong>Item:</strong> <?php echo htmlspecialchars($item_title); ?></p>
          <p class="mb-1"><strong>Winning bid:</strong> £<?php echo number_format($final_price, 2); ?></p>
          <p class="mb-1"><strong>Deposit already paid:</strong> £<?php echo number_format($deposit_required, 2); ?></p>
          <hr>
          <h4 class="mb-1">
            Amount due: £<?php echo number_format($amount_due, 2); ?>
          </h4>
          <p class="text-muted mb-0">
            Wallet balance: £<?php echo number_format($current_balance, 2); ?>
          </p>
        </div>
      </div>
    </div>

    <div class="col-md-7">
      <?php if ($amount_due > 0 && !$is_paid): ?>
      <div class="card">
        <div class="card-header">
          Payment details
        </div>
        <div class="card-body">
          <p class="text-muted">
            This is a demo payment form. Card details are not sent to any real payment provider.
          </p>
          <form method="POST">
            <div class="form-group mb-3">
              <label for="cardName">Name on card</label>
              <input type="text" class="form-control" id="cardName" name="card_name" required>
            </div>

            <div class="form-group mb-3">
              <label for="cardNumber">Card number</label>
              <input type="text" class="form-control" id="cardNumber" name="card_number" maxlength="19" placeholder="1234 5678 9012 3456" required>
            </div>

            <div class="form-row d-flex mb-3">
              <div class="form-group me-2" style="flex: 1;">
                <label for="expiry">Expiry (MM/YY)</label>
                <input type="text" class="form-control" id="expiry" name="expiry" placeholder="08/27" required>
              </div>
              <div class="form-group" style="flex: 1;">
                <label for="cvv">CVV</label>
                <input type="password" class="form-control" id="cvv" name="cvv" maxlength="4" required>
              </div>
            </div>

            <div class="form-group mb-3">
              <label for="billingAddress">Billing address</label>
              <input type="text" class="form-control" id="billingAddress" name="billing_address" required>
            </div>

            <div class="form-row d-flex mb-3">
              <div class="form-group me-2" style="flex: 1;">
                <label for="city">City</label>
                <input type="text" class="form-control" id="city" name="city" required>
              </div>
              <div class="form-group" style="flex: 1;">
                <label for="postcode">Postcode</label>
                <input type="text" class="form-control" id="postcode" name="postcode" required>
              </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
              Pay £<?php echo number_format($amount_due, 2); ?>
            </button>
          </form>
        </div>
      </div>
      <?php else: ?>
        <div class="card">
          <div class="card-body">
            <p class="mb-0">
              <?php if ($amount_due <= 0): ?>
                No additional payment is required for this auction.
              <?php else: ?>
                Payment has already been completed.
              <?php endif; ?>
            </p>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include_once("footer.php"); ?>
