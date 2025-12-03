<?php require("utilities.php"); ?>
<?php include_once("header.php"); ?>


<div class="container">

<h2 class="my-3">My bids</h2>

<?php
  // This page shows all auctions the user has placed bids on.
  // Similar to browse.php but without a search bar.

  // Check user's credentials (session).
  if (!isset($_SESSION['user_id'])) {
      echo "<div class='alert alert-danger'>You must log in to view your bids.</div>";
      include_once("footer.php");
      exit();
  }

  $user_id = $_SESSION['user_id'];

  // Query the database for auctions the user has bid on.
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

  // Current time, used to determine whether auctions have ended.
  $now = new DateTime();

  // Loop through results and print them as list items.
  if ($result->num_rows === 0) {
      echo "<div class='alert alert-info'>You have not placed any bids yet.</div>";
  } else {

      echo '<ul class="list-group">';

      while ($row = $result->fetch_assoc()) {
          $item_id     = $row['item_id'];
          $title       = htmlspecialchars($row['title']);
          $description = htmlspecialchars($row['description']);
          $end_time    = new DateTime($row['end_time']);

          $my_bid      = (float)$row['my_bid'];
          $highest_bid = (float)$row['highest_bid'];

          // Has the auction ended?
          $isEnded = $now > $end_time;

          if ($isEnded) {
              // Auction ended
              if ($my_bid > 0 && $my_bid >= $highest_bid) {
                  // User is the final highest bidder => Winner
                  $status = "<span class='badge badge-success'>Winner</span>";
              } else {
                  // Auction ended but user is not the highest bidder
                  $status = "<span class='badge badge-secondary'>Lost</span>";
              }
          } else {
              // Auction still ongoing
              if ($my_bid >= $highest_bid) {
                  $status = "<span class='text-success'>You are currently the highest bidder</span>";
              } else {
                  $status = "<span class='text-danger'>Outbid</span>";
              }
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

</div>

<?php include_once("footer.php"); ?>
x