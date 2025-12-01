<?php require("utilities.php")?>
<?php include_once("header.php")?>

<div class="container">

<h2 class="my-3">Recommendations for you</h2>

<?php
  // This page is for showing a buyer recommended items based on their bid 
  // history. It will be pretty similar to browse.php, except there is no 
  // search bar. This can be started after browse.php is working with a database.
  // Feel free to extract out useful functions from browse.php and put them in
  // the shared "utilities.php" where they can be shared by multiple files.
  
  
  // TODO: Check user's credentials (cookie/session).


if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-warning'>Please log in to see recommendations.</div>";
    include_once("footer.php");
    exit();
}

$user_id = $_SESSION['user_id'];
?>

   <!-- TODO: Perform a query to pull up auctions they might be interested in. -->
<?php
require_once("database.php");
$conn = connectDB();
// 整体逻辑：按照用户bid最多的category推荐相同的category
$sql_bid_cat = "
    SELECT i.category_id, COUNT(*) AS freq
    FROM bid b
    JOIN auction a ON b.auction_id = a.auction_id
    JOIN item i ON i.item_id = a.item_id
    WHERE b.bidder_id = ?
    GROUP BY i.category_id
    ORDER BY freq DESC
    LIMIT 1
";

$stmt_bid_cat = $conn->prepare($sql_bid_cat);
$stmt_bid_cat->bind_param("i", $user_id);
$stmt_bid_cat->execute();
$res_bid_cat = $stmt_bid_cat->get_result();
// 如果没有bid历史，就没有推荐，return提示词
if ($res_bid_cat->num_rows === 0) {
    echo "<div class='alert alert-info'>You have not bid on anything yet — no recommendations available.</div>";
    include_once("footer.php");
    exit();
}

  $fav_bid_category = $res_bid_cat->fetch_assoc()['category_id'];

    // 推荐同 category，用户没 bid 过的商品
    $sql_bid_rec = "
        SELECT a.auction_id, i.item_id, i.title, i.description, a.end_time
        FROM auction a
        JOIN item i ON a.item_id = i.item_id
        WHERE i.category_id = ?
          AND a.end_time > NOW()
          AND a.auction_id NOT IN (
              SELECT auction_id FROM bid WHERE bidder_id = ?
          )
        LIMIT 10
    ";

    $stmt_bid_rec = $conn->prepare($sql_bid_rec);
    $stmt_bid_rec->bind_param("ii", $fav_bid_category, $user_id);
    $stmt_bid_rec->execute();
    $res_bid_rec = $stmt_bid_rec->get_result();

if ($res_bid_rec->num_rows === 0) {
    echo "<div class='alert alert-info'>
            No items found matching your bidding interests.
          </div>";
    include_once("footer.php");
    exit();
}

  
  // TODO: Loop through results and print them out as list items.
 echo '<ul class="list-group">';

while ($row = $res_bid_rec->fetch_assoc()) {
    echo '
      <li class="list-group-item">
        <a href="listing.php?item_id='.$row['item_id'].'">
          <strong>'.$row['title'].'</strong>
        </a><br>
        '.$row['description'].'<br>
        Ends: '.(new DateTime($row['end_time']))->format('j M H:i').'
      </li>';
}

echo '</ul>';

?>

</div>

<?php include_once("footer.php")?>




