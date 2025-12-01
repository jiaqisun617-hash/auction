<?php require("utilities.php")?>
<?php require_once("database.php"); ?>
<?php include_once("header.php")?>

<?php
  // Get info from the URL:
  $item_id = $_GET['item_id'];
  $conn = connectDB();

  // TODO: Use item_id to make a query to the database.

  // DELETEME: For now, using placeholder data.
  $sql = "
    SELECT 
        Item.title,
        Item.description,
        Item.seller_id,
        Item.condition,
        Auction.start_price,
        Auction.reserve_price,
        Auction.end_time,
        Auction.auction_id
    FROM Item
    JOIN Auction ON Item.item_id = Auction.item_id
    WHERE Item.item_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "<div class='alert alert-danger'>Auction not found.</div>";
    include_once("footer.php");
    exit();
}

// 读取图片
$sql_img = "SELECT path FROM image WHERE item_id = ? ORDER BY sort_order ASC LIMIT 1";
$stmt_img = $conn->prepare($sql_img);
$stmt_img->bind_param("i", $item_id);
$stmt_img->execute();
$res_img = $stmt_img->get_result();
$img_path = $res_img->fetch_assoc()['path'] ?? 'uploads/default.jpg';




$row = $result->fetch_assoc();

  $title = $row['title'];
  $description = $row['description'];
  $end_time       = new DateTime($row['end_time']);
  $seller_id      = $row['seller_id'];
  $auction_id = $row['auction_id'];

  // <!-- 加了一个watchlist检查 -->

  $watching = false;

if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];

    $sql_watch = "SELECT 1 FROM watchlist WHERE user_id=? AND auction_id=?";
    $stmt_watch = $conn->prepare($sql_watch);
    $stmt_watch->bind_param("ii", $uid, $auction_id);
    $stmt_watch->execute();
    $res_watch = $stmt_watch->get_result();

    if ($res_watch->num_rows > 0) {
        $watching = true;
    }
}


 $sql2 = "
    SELECT MAX(bid_amount) AS max_bid 
    FROM Bid 
    WHERE auction_id = ?
";

$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $auction_id);
$stmt2->execute();
$result2 = $stmt2->get_result();
$max_bid_row = $result2->fetch_assoc();

$current_price = $max_bid_row['max_bid'] ?? $row['start_price'];

$sql3 = "SELECT COUNT(*) AS count_bids FROM Bid WHERE auction_id = ?";
$stmt3 = $conn->prepare($sql3);
$stmt3->bind_param("i", $auction_id);
$stmt3->execute();
$result3 = $stmt3->get_result();
$num_bids = $result3->fetch_assoc()['count_bids'];


  // TODO: Note: Auctions that have ended may pull a different set of data,
  //       like whether the auction ended in a sale or was cancelled due
  //       to lack of high-enough bids. Or maybe not.
  
  // Calculate time to auction end:
  $now = new DateTime();
  
  if ($now < $end_time) {
    $time_to_end = date_diff($now, $end_time);
    $time_remaining = ' (in ' . display_time_remaining($time_to_end) . ')';
  }
  
  // TODO: If the user has a session, use it to make a query to the database
  //       to determine if the user is already watching this item.
  //       For now, this is hardcoded.
  $has_session = true;
  // $watching = false; hardcode删掉了，跟我逻辑相悖
?>


<div class="container">

<div class="row"> <!-- Row #1 with auction title + watch button -->
  <div class="col-sm-8"> <!-- Left col -->
    <h2 class="my-3"><?php echo($title); ?></h2>
  </div>
  <div class="col-sm-4 align-self-center"> <!-- Right col -->
<?php
  /* The following watchlist functionality uses JavaScript, but could
     just as easily use PHP as in other places in the code */
  if ($now < $end_time):
?>
    <div id="watch_nowatch" <?php if ($has_session && $watching) echo('style="display: none"');?> >
      <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addToWatchlist()">+ Add to watchlist</button>
    </div>
    <div id="watch_watching" <?php if (!$has_session || !$watching) echo('style="display: none"');?> >
      <button type="button" class="btn btn-success btn-sm" disabled>Watching</button>
      <button type="button" class="btn btn-danger btn-sm" onclick="removeFromWatchlist()">Remove watch</button>
    </div>
<?php endif /* Print nothing otherwise */ ?>
  </div>
</div>

<div class="row"> <!-- Row #2 with auction description + bidding info -->
  <div class="col-sm-8"> <!-- Left col with item info -->

    <div class="itemDescription">
    <?php echo($description); ?>
    <br>
    <br>
    <br>
    
    </div>
    <?php if (!empty($img_path)) : ?>
    <img src="<?php echo $img_path; ?>"
         style="width:300px;height:300px;object-fit:cover;border-radius:8px;margin-bottom:20px;">
<?php endif; ?>

    

  </div>

   <div class="col-sm-4"> <!-- Right col with bidding info -->

    <?php
    ?>
    <p>
      <?php if ($now > $end_time): ?>
        This auction ended <?php echo date_format($end_time, 'j M H:i'); ?>
      <?php else: ?>
        Auction ends <?php echo date_format($end_time, 'j M H:i') . $time_remaining; ?>
      <?php endif; ?>
    </p>

    <p class="lead">
      Current bid: £<?php echo number_format($current_price, 2); ?>
    </p>

    <?php
      $can_bid      = false;
      $is_seller    = false;
      $not_logged_in = false;

      if (!isset($_SESSION['user_id'])) {
          $not_logged_in = true;
      } else {
          $uid       = $_SESSION['user_id'];
          $user_type = $_SESSION['account_type']; // buyer / seller / admin

          if ($uid == $seller_id) {
              $is_seller = true;
          }

          if ($user_type === "buyer" && !$is_seller) {
              $can_bid = true;
          }
      }
    ?>

    <?php if ($now <= $end_time):  ?>

      <?php if ($not_logged_in): ?>

        <div class="alert alert-warning">
          Please <a href="#" data-toggle="modal" data-target="#loginModal">log in</a>
 to place a bid.
        </div>

      <?php elseif ($is_seller): ?>

        <div class="alert alert-info">
          Sellers cannot bid on their own items.
        </div>

      <?php elseif ($_SESSION['account_type'] !== "buyer"): ?>

        <div class="alert alert-info">
          Only buyers can place bids.
        </div>

      <?php elseif ($can_bid): ?>

        <form method="POST" action="place_bid.php">
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text">£</span>
            </div>
            <input
              type="number"
              class="form-control"
              id="bid"
              name="bid_amount"
              min="<?php echo $current_price; ?>"
              step="1"
              required
            >
            <input type="hidden" name="auction_id" value="<?php echo $auction_id; ?>">
            <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
          </div>

          <button type="submit" class="btn btn-primary form-control mt-2">
            Place bid
          </button>
        </form>

      <?php endif;  ?>

    <?php endif;  ?>

  </div> <!-- End of right col with bidding info -->


</div> <!-- End of row #2 -->




<?php include_once("footer.php")?>

<script> 
// JavaScript functions: addToWatchlist and removeFromWatchlist.
var isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

function addToWatchlist(button) {
  if (!isLoggedIn) {
    alert("Please log in to add items to your watchlist.");
    $('#loginModal').modal('show');
    return;
  }
  console.log("These print statements are helpful for debugging btw");

  // This performs an asynchronous call to a PHP function using POST method.
  // Sends item ID as an argument to that function.
  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: {functionname: 'add_to_watchlist', arguments: [<?php echo($auction_id);?>]},

    success: 
      function (obj, textstatus) {
        // Callback function for when call is successful and returns obj
        console.log("Success");
        var objT = obj.trim();

        if (objT === "not_logged_in") {
                $('#loginModal').modal('show');
                return;
        }
 
        if (objT == "success") {
          $("#watch_nowatch").hide();
          $("#watch_watching").show();
        }
        else {
          var mydiv = document.getElementById("watch_nowatch");
          mydiv.appendChild(document.createElement("br"));
          mydiv.appendChild(document.createTextNode("Add to watch failed. Try again later."));
        }
      },

    error:
      function (obj, textstatus) {
        console.log("Error");
      }
  }); // End of AJAX call

} // End of addToWatchlist func

function removeFromWatchlist(button) {
  // This performs an asynchronous call to a PHP function using POST method.
  // Sends item ID as an argument to that function.
  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: {functionname: 'remove_from_watchlist', arguments: [<?php echo($auction_id);?>]},

    success: 
      function (obj, textstatus) {
        // Callback function for when call is successful and returns obj
        console.log("Success");
        var objT = obj.trim();
 
        if (objT == "success") {
          $("#watch_watching").hide();
          $("#watch_nowatch").show();
        }
        else {
          var mydiv = document.getElementById("watch_watching");
          mydiv.appendChild(document.createElement("br"));
          mydiv.appendChild(document.createTextNode("Watch removal failed. Try again later."));
        }
      },

    error:
      function (obj, textstatus) {
        console.log("Error");
      }
  }); // End of AJAX call

} // End of addToWatchlist func
</script>