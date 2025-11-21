<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    die("You must log in to place a bid.");
}

$bidder_id = $_SESSION['user_id'];
$auction_id = $_POST['auction_id'];
$bid_amount = $_POST['bid_amount'];

require_once("database.php");
$conn = connectDB();

// 先取得当前最高bid
$sql_max = "SELECT MAX(bid_amount) AS max_bid FROM bid WHERE auction_id = ?";
$stmt_max = $conn->prepare($sql_max);
$stmt_max->bind_param("i", $auction_id);
$stmt_max->execute();
$max_res = $stmt_max->get_result();
$max_row = $max_res->fetch_assoc();

$current_price = $max_row['max_bid'] ?? null;


// 如果没有人出价过，就取 start_price
if ($current_price === null) {

    $sql_start = "SELECT start_price, item_id FROM auction WHERE auction_id = ?";
    $stmt_start = $conn->prepare($sql_start);
    $stmt_start->bind_param("i", $auction_id);
    $stmt_start->execute();

    $start_res = $stmt_start->get_result();
    $start_row = $start_res->fetch_assoc();

    $current_price = $start_row['start_price'];
    $item_id = $start_row['item_id'];

} else {

    $sql_item = "SELECT item_id FROM auction WHERE auction_id = ?";
    $stmt_item = $conn->prepare($sql_item);
    $stmt_item->bind_param("i", $auction_id);
    $stmt_item->execute();

    $item_id = $stmt_item->get_result()->fetch_assoc()['item_id'];
}



// 价格检查
if ($bid_amount <= $current_price) {
    die("<div class='alert alert-danger'>
        Bid must be higher than current price (£$current_price).
    </div>");
}


// 找到之前出价最高的人
$sql_prev = "SELECT bidder_id, bid_amount 
             FROM bid 
             WHERE auction_id = ? 
             ORDER BY bid_amount DESC 
             LIMIT 1";

$stmt_prev = $conn->prepare($sql_prev);
$stmt_prev->bind_param("i", $auction_id);
$stmt_prev->execute();
$result_prev = $stmt_prev->get_result();
$prev_bidder = $result_prev->fetch_assoc();


// 触发发邮件机制
if ($prev_bidder && $prev_bidder['bidder_id'] != $user_id) {

    // 新出价比旧最高高
    if ($bid_amount > $prev_bidder['bid_amount']) {

        // 获取被超过的人 email
        $sql_email = "SELECT email FROM user WHERE user_id = ?";
        $stmt_email = $conn->prepare($sql_email);
        $stmt_email->bind_param("i", $prev_bidder['bidder_id']);
        $stmt_email->execute();
        $email_res = $stmt_email->get_result()->fetch_assoc();
        $outbid_email = $email_res['email'];

    //     // ⭐ DEBUG: 写入 Outbid 触发记录
    //  file_put_contents("debug_outbid.log",
    // "Outbid triggered: previous bidder $outbid_email | auction $auction_id | new_bid £$bid_amount | time " . date('Y-m-d H:i:s') . "\n",
    // FILE_APPEND);

        // 发邮件
        $subject = "You have been outbid!";
        $message = "Hi,\n\nYour bid for auction #$auction_id has been surpassed.\n\nNew bid amount: £$bid_amount\n\nLog in to place a higher bid.";
        $headers = "From: noreply@auctionsite.com";

        mail($outbid_email, $subject, $message, $headers);
    }
}


// 插入 bid 记录
$sql = "INSERT INTO bid (auction_id, bidder_id, bid_amount, bid_time)
        VALUES (?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iid", $auction_id, $bidder_id, $bid_amount);

if ($stmt->execute()) {
    // echo "<div class='alert alert-success'>Bid placed successfully!</div>";
    // header("refresh:2;url=listing.php?item_id=" . $item_id);
     header("Location: bid_success.php?item_id=" . $item_id);
    exit();
} else {
    echo "<div class='alert alert-danger'>Error placing bid.</div>";
}

$conn->close();
?>
