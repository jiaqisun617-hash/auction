<?php

include_once("header.php");
require_once("utilities.php");
require_once("database.php");



if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-danger'>Please log in first.</div>";
    include_once("footer.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = connectDB();

$sql = "
    SELECT
        i.item_id,
        i.title,
        i.description,
        a.auction_id,
        a.end_time,
        (SELECT MAX(bid_amount) 
         FROM bid 
         WHERE bid.auction_id = a.auction_id) AS current_price,
        (SELECT COUNT(*) 
         FROM bid 
         WHERE bid.auction_id = a.auction_id) AS bid_count
    FROM watchlist w
    JOIN auction a ON w.auction_id = a.auction_id
    JOIN item i ON a.item_id = i.item_id
    WHERE w.user_id = ?
    ORDER BY a.end_time ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container">
<h2 class="my-3">My Watchlist</h2>

<?php
if ($result->num_rows === 0) {
    echo "<div class='alert alert-info'>You are not watching any items.</div>";
} else {
    echo '<ul class="list-group">';

    while ($row = $result->fetch_assoc()) {
        $item_id = $row['item_id'];
        $title = $row['title'];
        $description = $row['description'];
        $current_price = $row['current_price'] ?? "No bids";
        $bid_count = $row['bid_count'];
        $end_time = new DateTime($row['end_time']);

        echo '
        <li class="list-group-item">
            <a href="listing.php?item_id=' . $item_id . '"><strong>' . $title . '</strong></a><br>
            ' . $description . '<br>
            Current price: £' . (is_numeric($current_price) ? number_format($current_price, 2) : $current_price) . '<br>
            Bids: ' . $bid_count . '<br>
            Ends: ' . $end_time->format("j M H:i") . '
        </li>';
    }

    echo '</ul>';
}

$conn->close();
?>

<?php include_once("footer.php") ?>
