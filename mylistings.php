<?php
// Enable error reporting (useful during development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require("utilities.php");
include_once("header.php");
?>

<div class="container">

  <h2 class="my-3">My listings</h2>

<?php
  // Check user's credentials (session).
  if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo '<div class="alert alert-warning">You must log in to view your listings.</div>';
    include_once("footer.php");
    exit();
  }

  // Only sellers can view My Listings.
  if (!hasRole('seller')) {
    echo '<div class="alert alert-danger">Only sellers can view My Listings.</div>';
    include_once("footer.php");
    exit();
  }

  $seller_id = $_SESSION['user_id'];
?>

<?php
// Connect to database and fetch seller's auctions.
require_once("database.php");
$conn = connectDB();

$sql = "
    SELECT 
        Item.item_id,
        Item.title,
        Item.description,
        Auction.start_price,
        Auction.end_time,
        Auction.auction_id
    FROM Item
    JOIN Auction ON Item.item_id = Auction.item_id
    WHERE Item.seller_id = ?
    ORDER BY Auction.end_time DESC;
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();

$now = new DateTime();
?>

<?php
// Loop through results and print them out as list items.
if ($result->num_rows === 0) {
    echo '<div class="alert alert-info">You have not created any listings yet.</div>';
} else {

    echo '<div class="list-group">';

    while ($row = $result->fetch_assoc()) {
        $item_id     = $row['item_id'];
        $auction_id  = $row['auction_id'];
        $title       = htmlspecialchars($row['title']);
        $start_price = number_format($row['start_price'], 2);

        // End time: one for display, one for comparison
        $end_time_raw  = $row['end_time'];
        $end_time_disp = date("Y-m-d H:i", strtotime($end_time_raw));
        $end_time_dt   = new DateTime($end_time_raw);

        // Determine activity status and final price text
        if ($now < $end_time_dt) {
            $activity         = "Live";
            $activity_class   = "success";   // Bootstrap badge-success
            $final_price_text = "";
        } else {
            $activity       = "Ended";
            $activity_class = "secondary";

            // For ended auctions, fetch final price (highest bid)
            $sql_final = "SELECT MAX(bid_amount) AS final_price FROM Bid WHERE auction_id = ?";
            $stmt_final = $conn->prepare($sql_final);
            $stmt_final->bind_param("i", $auction_id);
            $stmt_final->execute();
            $res_final  = $stmt_final->get_result();
            $final_row  = $res_final->fetch_assoc();
            $final_price = $final_row['final_price'];

            if ($final_price !== null) {
                $final_price_text = "Final price: £" . number_format($final_price, 2);
            } else {
                // No bids were placed on this auction
                $final_price_text = "Final price: No bids";
            }
        }

        // Each list item: left side info, right side activity badge
        echo '
        <a href="listing.php?item_id=' . $item_id . '" 
           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">' . $title . '</h5>
                <p class="mb-1">Start Price: £' . $start_price . '</p>
                <small>Ends: ' . $end_time_disp . '</small>';

        if (!empty($final_price_text)) {
            echo '<br><small>' . $final_price_text . '</small>';
        }

        echo '
            </div>
            <div>
                <span class="badge badge-' . $activity_class . ' px-3 py-2">' . $activity . '</span>
            </div>
        </a>';
    }

    echo '</div>';
}

$stmt->close();
$conn->close();
?>

<?php include_once("footer.php"); ?>
