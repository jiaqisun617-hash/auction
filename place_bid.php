<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    die("You must log in to place a bid.");
}

$bidder_id  = (int)$_SESSION['user_id'];
$auction_id = (int)$_POST['auction_id'];
$bid_amount = (float)$_POST['bid_amount'];

require_once("database.php");
$conn = connectDB();

/*
 * Step 1: Get current highest bid for this auction
 */
$sql_max = "SELECT MAX(bid_amount) AS max_bid 
            FROM Bid 
            WHERE auction_id = ?";
$stmt_max = $conn->prepare($sql_max);
$stmt_max->bind_param("i", $auction_id);
$stmt_max->execute();
$max_res = $stmt_max->get_result();
$max_row = $max_res->fetch_assoc();

$current_price = $max_row['max_bid'] ?? null;

/*
 * Step 2: Get auction info (start price, item id, and required deposit)
 * Here we treat `reserve_price` as the required deposit.
 */
$sql_auction = "SELECT start_price, item_id, reserve_price AS deposit_required
                FROM Auction 
                WHERE auction_id = ?";
$stmt_auction = $conn->prepare($sql_auction);
$stmt_auction->bind_param("i", $auction_id);
$stmt_auction->execute();
$auction_res = $stmt_auction->get_result();
$auction_row = $auction_res->fetch_assoc();

if (!$auction_row) {
    die("Auction not found.");
}

$item_id          = (int)$auction_row['item_id'];
$start_price      = (float)$auction_row['start_price'];
$deposit_required = (float)$auction_row['deposit_required'];

/*
 * If no one has bid yet, the current price is the start price.
 */
if ($current_price === null) {
    $current_price = $start_price;
}

/*
 * Step 3: Check bidder's balance against required deposit (reserve_price)
 * If deposit_required is 0, it means there is no deposit requirement.
 */
if ($deposit_required > 0) {
    $sql_balance = "SELECT balance FROM user WHERE user_id = ?";
    $stmt_balance = $conn->prepare($sql_balance);
    $stmt_balance->bind_param("i", $bidder_id);
    $stmt_balance->execute();
    $balance_res = $stmt_balance->get_result();
    $balance_row = $balance_res->fetch_assoc();

    $buyer_balance = $balance_row ? (float)$balance_row['balance'] : 0.0;

    if ($buyer_balance < $deposit_required) {
        // Redirect back to listing page and show error message under the button
        header("Location: listing.php?item_id=" . $item_id . "&error=lowbalance");
        exit();
    }
}

/*
 * Step 4: Price check – new bid must be higher than current price
 */
if ($bid_amount <= $current_price) {
    die("<div class='alert alert-danger'>
            Bid must be higher than current price (£" . number_format($current_price, 2) . ").
        </div>");
}

/*
 * Step 5: Find previous highest bidder (for outbid email notification)
 */
$sql_prev = "SELECT bidder_id, bid_amount 
             FROM Bid 
             WHERE auction_id = ? 
             ORDER BY bid_amount DESC 
             LIMIT 1";

$stmt_prev = $conn->prepare($sql_prev);
$stmt_prev->bind_param("i", $auction_id);
$stmt_prev->execute();
$result_prev = $stmt_prev->get_result();
$prev_bidder = $result_prev->fetch_assoc();

/*
 * Step 6: Trigger outbid email if a different user has been outbid
 */
if ($prev_bidder && $prev_bidder['bidder_id'] != $bidder_id) {

    // New bid is higher than previous highest
    if ($bid_amount > (float)$prev_bidder['bid_amount']) {

        // Get email of the outbid user
        $sql_email = "SELECT email FROM user WHERE user_id = ?";
        $stmt_email = $conn->prepare($sql_email);
        $stmt_email->bind_param("i", $prev_bidder['bidder_id']);
        $stmt_email->execute();
        $email_res = $stmt_email->get_result()->fetch_assoc();
        $outbid_email = $email_res['email'];

        // Send notification email
        $subject = "You have been outbid!";
        $message = "Hi,\n\nYour bid for auction #$auction_id has been surpassed.\n\nNew bid amount: £$bid_amount\n\nLog in to place a higher bid.";
        $headers = "From: noreply@auctionsite.com";

        // You may want to check return value of mail() in a real system
        mail($outbid_email, $subject, $message, $headers);
    }
}

/*
 * Step 7: Insert new bid record
 */
$sql = "INSERT INTO Bid (auction_id, bidder_id, bid_amount, bid_time)
        VALUES (?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iid", $auction_id, $bidder_id, $bid_amount);

if ($stmt->execute()) {
    header("Location: bid_success.php?item_id=" . $item_id);
    exit();
} else {
    echo "<div class='alert alert-danger'>Error placing bid.</div>";
}

$conn->close();
?>
