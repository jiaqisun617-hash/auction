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


// 如果没有人出价过，就取 start_price（⭐ 修正后的版本）
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
