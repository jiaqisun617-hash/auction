<?php require("utilities.php")?>
<?php include_once("header.php")?>

<div class="container">

<h2 class="my-3">My listings</h2>

<?php
  // This page is for showing a user the auction listings they've made.
  // It will be pretty similar to browse.php, except there is no search bar.
  // This can be started after browse.php is working with a database.
  // Feel free to extract out useful functions from browse.php and put them in
  // the shared "utilities.php" where they can be shared by multiple files.
  
  
  // TODO: Check user's credentials (cookie/session).
  if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo '<div class="alert alert-warning">You must log in to view your listings.</div>';
    include_once("footer.php");
    exit();
  }
    if (!hasRole('seller')) {
    echo '<div class="alert alert-danger">Only sellers can view My Listings.</div>';
    include_once("footer.php");
    exit();
}

$seller_id = $_SESSION['user_id'];  
?>

  
 <!-- TODO: Perform a query to pull up their auctions. -->

<?php
require_once("database.php");
$conn = connectDB();

$sql = "
    SELECT 
        Item.item_id,
        Item.title,
        Item.description,
        Auction.start_price,
        Auction.end_time
    FROM Item
    JOIN Auction ON Item.item_id = Auction.item_id
    WHERE Item.seller_id = ?
    ORDER BY Auction.end_time DESC;
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
?>

  
<!-- TODO: Loop through results and print them out as list items. -->
  <?php

if ($result->num_rows === 0) {
    echo '<div class="alert alert-info">You have not created any listings yet.</div>';
} else {

    echo '<div class="list-group">';

    while ($row = $result->fetch_assoc()) {
        $item_id = $row['item_id'];
        $title = htmlspecialchars($row['title']);
        $price = number_format($row['start_price'], 2);
        $end_time = date("Y-m-d H:i", strtotime($row['end_time']));

        echo '
        <a href="listing.php?item_id=' . $item_id . '" class="list-group-item list-group-item-action">
            <h5 class="mb-1">' . $title . '</h5>
            <p class="mb-1">Start Price: £' . $price . '</p>
            <small>Ends: ' . $end_time . '</small>
        </a>';
    }

    echo '</div>';
}

$stmt->close();
$conn->close();
?>

  


<?php include_once("footer.php"); ?>