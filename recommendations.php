<?php require("utilities.php")?>
<?php include_once("header.php")?>

<div class="container">

<h2 class="my-3">Recommendations for you</h2>

<?php
// Check login
if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-warning'>Please log in to see recommendations.</div>";
    include_once("footer.php");
    exit();
}

$user_id = $_SESSION['user_id'];

require_once("database.php");
$conn = connectDB();

/*
 * We only use CATEGORIES from:
 *   1) Auctions you have a bid on AND are still active (end_time > NOW())
 *   2) Auctions in your watchlist AND are still active
 *
 * Then we recommend other ACTIVE auctions in those categories,
 * excluding any auctions you already bid on or already watchlisted.
 */

// Main recommendations query
$sql_rec = "
    SELECT DISTINCT a.auction_id, i.item_id, i.title, i.description, a.end_time
    FROM auction a
    JOIN item i ON a.item_id = i.item_id
    WHERE a.end_time > NOW()
      AND i.category_id IN (
          SELECT DISTINCT i2.category_id
          FROM (
              -- Active auctions that you have bid on
              SELECT b.auction_id
              FROM bid b
              JOIN auction a1 ON b.auction_id = a1.auction_id
              WHERE b.bidder_id = ?
                AND a1.end_time > NOW()

              UNION

              -- Active auctions in your watchlist
              SELECT w.auction_id
              FROM watchlist w
              JOIN auction a2 ON w.auction_id = a2.auction_id
              WHERE w.user_id = ?
                AND a2.end_time > NOW()
          ) uw
          JOIN auction a3 ON uw.auction_id = a3.auction_id
          JOIN item    i2 ON a3.item_id    = i2.item_id
      )
      -- Do not recommend auctions you already bid on
      AND a.auction_id NOT IN (
          SELECT auction_id FROM bid WHERE bidder_id = ?
      )
      -- Do not recommend auctions already in your watchlist
      AND a.auction_id NOT IN (
          SELECT auction_id FROM watchlist WHERE user_id = ?
      )
    ORDER BY a.end_time ASC
    LIMIT 20
";

$stmt_rec = $conn->prepare($sql_rec);
$stmt_rec->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
$stmt_rec->execute();
$res_rec = $stmt_rec->get_result();

if ($res_rec->num_rows === 0) {
    echo "<div class='alert alert-info'>
            No items found matching your bidding and watchlist interests.
          </div>";
    include_once("footer.php");
    exit();
}

// Output recommendation list
echo '<ul class="list-group">';

while ($row = $res_rec->fetch_assoc()) {
    $end = new DateTime($row['end_time']);
    echo '
      <li class="list-group-item">
        <a href="listing.php?item_id=' . $row['item_id'] . '">
          <strong>' . htmlspecialchars($row['title']) . '</strong>
        </a><br>
        ' . htmlspecialchars($row['description']) . '<br>
        Ends: ' . $end->format('j M H:i') . '
      </li>';
}

echo '</ul>';

$stmt_rec->close();
$conn->close();
?>

</div>

<?php include_once("footer.php")?>
