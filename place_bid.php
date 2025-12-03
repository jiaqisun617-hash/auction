<?php
// place_bid.php
// Handle placing a bid: price check, margin (reserve_price) handling,
// balance updates, and outbid email notifications.

session_start();

if (!isset($_SESSION['user_id'])) {
    die("You must log in to place a bid.");
}

$bidder_id  = (int)$_SESSION['user_id'];
$auction_id = isset($_POST['auction_id']) ? (int)$_POST['auction_id'] : 0;
$bid_amount = isset($_POST['bid_amount']) ? (float)$_POST['bid_amount'] : 0.0;

if ($auction_id <= 0 || $bid_amount <= 0) {
    die("Invalid bid request.");
}

require_once("database.php");
require_once("utilities.php");

$conn = connectDB();

// 1. Load auction & item info
$sqlAuction = "
    SELECT
        a.auction_id,
        a.item_id,
        a.start_price,
        a.reserve_price,
        a.end_time,
        i.seller_id
    FROM auction a
    JOIN item i ON a.item_id = i.item_id
    WHERE a.auction_id = ?
";

$stmtA = $conn->prepare($sqlAuction);
if (!$stmtA) {
    die("Database error (auction query).");
}
$stmtA->bind_param("i", $auction_id);
$stmtA->execute();
$resA = $stmtA->get_result();

if (!$resA || $resA->num_rows === 0) {
    die("Auction not found.");
}

$rowA = $resA->fetch_assoc();

$item_id        = (int)$rowA['item_id'];
$start_price    = (float)$rowA['start_price'];
$deposit_amount = (float)$rowA['reserve_price'];   // Using reserve_price as margin
$seller_id      = (int)$rowA['seller_id'];
$end_time       = new DateTime($rowA['end_time']);
$now            = new DateTime();

// 2. Basic checks: auction still live & not bidding on own item
if ($now >= $end_time) {
    $_SESSION['bid_error'] = "This auction has already ended.";
    header("Location: listing.php?item_id=" . $item_id);
    exit();
}

if ($bidder_id === $seller_id) {
    $_SESSION['bid_error'] = "You cannot bid on your own item.";
    header("Location: listing.php?item_id=" . $item_id);
    exit();
}

// 3. Find current highest bid (if any)
$sqlTop = "
    SELECT bidder_id, bid_amount
    FROM bid
    WHERE auction_id = ?
    ORDER BY bid_amount DESC, bid_time ASC
    LIMIT 1
";

$stmtTop = $conn->prepare($sqlTop);
if (!$stmtTop) {
    die("Database error (top bid query).");
}
$stmtTop->bind_param("i", $auction_id);
$stmtTop->execute();
$resTop = $stmtTop->get_result();

$current_price    = $start_price;
$prev_bidder_id   = null;
$prev_highest_bid = null;

if ($resTop && $resTop->num_rows > 0) {
    $rowTop           = $resTop->fetch_assoc();
    $current_price    = (float)$rowTop['bid_amount'];
    $prev_bidder_id   = (int)$rowTop['bidder_id'];
    $prev_highest_bid = (float)$rowTop['bid_amount'];
}

// 4. Price check: new bid must be strictly higher than current
if ($bid_amount <= $current_price) {
    $_SESSION['bid_error'] =
        "Bid must be higher than the current price (£" . number_format($current_price, 2) . ").";
    header("Location: listing.php?item_id=" . $item_id);
    exit();
}

// 5. Margin / deposit check: bidder must have enough balance for reserve_price
if ($deposit_amount > 0) {
    $sqlBal = "SELECT balance FROM user WHERE user_id = ?";
    $stmtBal = $conn->prepare($sqlBal);
    if (!$stmtBal) {
        die("Database error (balance query).");
    }
    $stmtBal->bind_param("i", $bidder_id);
    $stmtBal->execute();
    $resBal = $stmtBal->get_result();
    if (!$resBal || $resBal->num_rows === 0) {
        die("User not found.");
    }
    $rowBal  = $resBal->fetch_assoc();
    $balance = (float)$rowBal['balance'];

    if ($balance < $deposit_amount) {
        $_SESSION['bid_error'] =
            "Your balance (£" . number_format($balance, 2) .
            ") is not enough to cover the required deposit of £" .
            number_format($deposit_amount, 2) . ".";
        header("Location: listing.php?item_id=" . $item_id);
        exit();
    }
}

// 6. Start transaction: handle balance updates + bid insert atomically
$conn->begin_transaction();

try {
    // 6.1 Margin handling: refund previous highest bidder, charge new highest bidder
    if ($deposit_amount > 0) {
        // Refund previous highest bidder if exists and is different from current bidder
        if (!empty($prev_bidder_id) && $prev_bidder_id !== $bidder_id) {
            $sqlRefund = "UPDATE user SET balance = balance + ? WHERE user_id = ?";
            $stmtRefund = $conn->prepare($sqlRefund);
            if (!$stmtRefund) {
                throw new Exception("Database error (refund).");
            }
            $stmtRefund->bind_param("di", $deposit_amount, $prev_bidder_id);
            $stmtRefund->execute();
        }

        // Charge deposit from current bidder
        $sqlCharge = "UPDATE user SET balance = balance - ? WHERE user_id = ?";
        $stmtCharge = $conn->prepare($sqlCharge);
        if (!$stmtCharge) {
            throw new Exception("Database error (charge).");
        }
        $stmtCharge->bind_param("di", $deposit_amount, $bidder_id);
        $stmtCharge->execute();
    }

    // 6.2 Insert the new bid
    $sqlInsert = "
        INSERT INTO bid (auction_id, bidder_id, bid_amount, bid_time)
        VALUES (?, ?, ?, NOW())
    ";
    $stmtInsert = $conn->prepare($sqlInsert);
    if (!$stmtInsert) {
        throw new Exception("Database error (insert bid).");
    }
    $stmtInsert->bind_param("iid", $auction_id, $bidder_id, $bid_amount);
    $stmtInsert->execute();

    // Commit transaction
    $conn->commit();

} catch (Exception $e) {
    $conn->rollback();
    die("Error placing bid: " . htmlspecialchars($e->getMessage()));
}

// 7. Outbid email notification to previous highest bidder (if any and different user)
if (!empty($prev_bidder_id) && $prev_bidder_id !== $bidder_id && $prev_highest_bid !== null && $bid_amount > $prev_highest_bid) {

    $outbid_email = getUserEmailById($conn, $prev_bidder_id);
    $itemTitle    = getItemTitleByAuctionId($conn, $auction_id);

    if ($outbid_email && $itemTitle) {
        $subject = "You have been outbid on: " . $itemTitle;
        $body =
            "Hi,\n\n" .
            "Your bid on \"" . $itemTitle . "\" has just been outbid.\n\n" .
            "New bid amount: £" . number_format($bid_amount, 2) . "\n\n" .
            "Please log in to place a higher bid if you are still interested.\n\n" .
            "Princess Auction";

        sendEmail($outbid_email, $subject, $body);
    }
}

// 8. Redirect to success page
header("Location: bid_success.php?item_id=" . $item_id);
exit();
