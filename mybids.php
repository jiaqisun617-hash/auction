<?php include_once("header.php")?>
<?php require("utilities.php")?>

<div class="container">

<h2 class="my-3">My bids</h2>

<?php
  // This page is for showing a user the auctions they've bid on.
  // It will be pretty similar to browse.php, except there is no search bar.
  // This can be started after browse.php is working with a database.
  // Feel free to extract out useful functions from browse.php and put them in
  // the shared "utilities.php" where they can be shared by multiple files.
  
  
  // TODO: Check user's credentials (cookie/session).
  

if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-danger'>You must log in to view your bids.</div>";
    include_once("footer.php");
    exit();
}

$user_id = $_SESSION['user_id'];

  
  // TODO: Perform a query to pull up the auctions they've bidded on.
  require_once("database.php");
$conn = connectDB();

$sql = "
    SELECT 
        i.item_id,
        i.title,
        i.description,
        a.end_time,
        MAX(b.bid_amount) AS my_bid,
        (SELECT MAX(b2.bid_amount) 
         FROM bid b2 
         WHERE b2.auction_id = a.auction_id) AS highest_bid
    FROM bid b
    JOIN auction a ON b.auction_id = a.auction_id
    JOIN item i ON i.item_id = a.item_id
    WHERE b.bidder_id = ?
    GROUP BY a.auction_id
    ORDER BY a.end_time ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

  
  // TODO: Loop through results and print them out as list items.
  if ($result->num_rows === 0) {
    echo "<div class='alert alert-info'>You have not placed any bids yet.</div>";
} else {

    echo '<ul class="list-group">';

    while ($row = $result->fetch_assoc()) {
        $item_id = $row['item_id'];
        $title = $row['title'];
        $description = $row['description'];
        $end_time = new DateTime($row['end_time']);

        $my_bid = $row['my_bid'];
        $highest_bid = $row['highest_bid'];

        
        if ($my_bid >= $highest_bid) {
            $status = "<span class='text-success'>Winning</span>";
        } else {
            $status = "<span class='text-danger'>Outbid</span>";
        }

        echo '
        <li class="list-group-item">
            <a href="listing.php?item_id=' . $item_id . '"><strong>' . $title . '</strong></a><br>
            ' . $description . '<br>
            Your bid: £' . number_format($my_bid, 2) . '  
            — Highest bid: £' . number_format($highest_bid, 2) . '  
            — ' . $status . '<br>
            Ends: ' . $end_time->format('j M H:i') . '
        </li>';
    }

    echo '</ul>';
}

$conn->close();

  
?>

<?php include_once("footer.php")?>