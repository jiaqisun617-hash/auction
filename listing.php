<?php require("utilities.php") ?>
<?php require_once("database.php"); ?>
<?php include_once("header.php") ?>

<?php
  // Get item_id from URL
  $item_id = (int)$_GET['item_id'];
  $conn = connectDB();

  // Query auction + item details for this item
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

  $row = $result->fetch_assoc();

  $title            = $row['title'];
  $description      = $row['description'];
  $end_time         = new DateTime($row['end_time']);
  $seller_id        = (int)$row['seller_id'];
  $auction_id       = (int)$row['auction_id'];
  $start_price      = (float)$row['start_price'];
  $deposit_required = (float)$row['reserve_price']; // we treat reserve_price as required deposit

  // Check if current user is already watching this auction
  $watching = false;
  $has_session = isset($_SESSION['user_id']);

  if ($has_session) {
      $uid = (int)$_SESSION['user_id'];

      $sql_watch = "SELECT 1 FROM watchlist WHERE user_id = ? AND auction_id = ?";
      $stmt_watch = $conn->prepare($sql_watch);
      $stmt_watch->bind_param("ii", $uid, $auction_id);
      $stmt_watch->execute();
      $res_watch = $stmt_watch->get_result();

      if ($res_watch->num_rows > 0) {
          $watching = true;
      }
  }

  // Get current highest bid for this auction
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

  $current_price = $max_bid_row['max_bid'] ?? $start_price;

  // Get number of bids
  $sql3 = "SELECT COUNT(*) AS count_bids FROM Bid WHERE auction_id = ?";
  $stmt3 = $conn->prepare($sql3);
  $stmt3->bind_param("i", $auction_id);
  $stmt3->execute();
  $result3 = $stmt3->get_result();
  $num_bids = $result3->fetch_assoc()['count_bids'];

  // Calculate time remaining
  $now = new DateTime();
  $time_remaining = '';

  if ($now < $end_time) {
      $time_to_end = date_diff($now, $end_time);
      $time_remaining = ' (in ' . display_time_remaining($time_to_end) . ')';
  }

  // Fetch all images for this item (for the carousel)
  $sql_img = "SELECT path FROM image WHERE item_id = ? ORDER BY sort_order ASC";
  $stmt_img = $conn->prepare($sql_img);
  $stmt_img->bind_param("i", $item_id);
  $stmt_img->execute();
  $res_img = $stmt_img->get_result();

  $images = [];
  while ($row_img = $res_img->fetch_assoc()) {
      $images[] = $row_img['path'];
  }
?>

<div class="container">

  <div class="row"><!-- Row #1 with auction title + watch button -->
    <div class="col-sm-8"><!-- Left col -->
      <h2 class="my-3"><?php echo htmlspecialchars($title); ?></h2>
    </div>

    <div class="col-sm-4 align-self-center"><!-- Right col -->
      <?php
        // Watchlist buttons (only while auction is running)
        if ($now < $end_time):
      ?>
        <div id="watch_nowatch" <?php if ($has_session && $watching) echo('style="display: none"');?> >
          <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addToWatchlist()">+ Add to watchlist</button>
        </div>
        <div id="watch_watching" <?php if (!$has_session || !$watching) echo('style="display: none"');?> >
          <button type="button" class="btn btn-success btn-sm" disabled>Watching</button>
          <button type="button" class="btn btn-danger btn-sm" onclick="removeFromWatchlist()">Remove watch</button>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="row"><!-- Row #2 with auction description + bidding info -->

    <!-- Left column: item description + image carousel -->
    <div class="col-sm-8">
      <div class="itemDescription">
        <?php echo nl2br(htmlspecialchars($description)); ?>
        <br><br><br>
      </div>

      <?php if (count($images) > 0): ?>
        <div id="itemCarousel" class="carousel slide mb-3" data-ride="carousel">

          <!-- Indicators -->
          <ol class="carousel-indicators">
            <?php foreach ($images as $idx => $img): ?>
              <li data-target="#itemCarousel"
                  data-slide-to="<?php echo $idx; ?>"
                  class="<?php echo ($idx == 0 ? 'active' : ''); ?>"></li>
            <?php endforeach; ?>
          </ol>

          <!-- Slides -->
          <div class="carousel-inner">
            <?php foreach ($images as $idx => $img): ?>
              <div class="carousel-item <?php echo ($idx == 0 ? 'active' : ''); ?>">
                <img src="<?php echo htmlspecialchars($img); ?>"
                     class="d-block"
                     style="width:600px;height:600px;object-fit:contain;border-radius:8px;margin:auto;">
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Controls -->
          <a class="carousel-control-prev" href="#itemCarousel" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          </a>
          <a class="carousel-control-next" href="#itemCarousel" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
          </a>

        </div>
      <?php endif; ?>
    </div>

    <!-- Right column: bidding info -->
    <div class="col-sm-4">

      <p>
        <?php if ($now > $end_time): ?>
          This auction ended <?php echo date_format($end_time, 'j M H:i'); ?>
        <?php else: ?>
          Auction ends <?php echo date_format($end_time, 'j M H:i') . $time_remaining; ?>
        <?php endif; ?>
      </p>

      <p class="lead mb-1">
        Current bid: £<?php echo number_format($current_price, 2); ?>
      </p>

      <!-- Show required deposit (using reserve_price) -->
      <p class="mb-3">
        Required deposit: £<?php echo number_format($deposit_required, 2); ?>
      </p>

      <?php
        // Determine if user can bid, is seller, or is not logged in
        $can_bid       = false;
        $is_seller     = false;
        $not_logged_in = false;

        if (!isset($_SESSION['user_id'])) {
            $not_logged_in = true;
        } else {
            $uid       = (int)$_SESSION['user_id'];
            $user_type = $_SESSION['account_type']; // buyer / seller / admin

            if ($uid == $seller_id) {
                $is_seller = true;
            }

            if ($user_type === "buyer" && !$is_seller) {
                $can_bid = true;
            }
        }
      ?>

      <?php if ($now <= $end_time): ?>

        <?php if ($not_logged_in): ?>

          <div class="alert alert-warning">
            Please <a href="#" data-toggle="modal" data-target="#loginModal">log in</a> to place a bid.
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

            <?php if (isset($_GET['error']) && $_GET['error'] === 'lowbalance'): ?>
              <div class="alert alert-danger mt-2">
                Your balance is lower than the required deposit for this auction. Please top up before bidding.
              </div>
            <?php endif; ?>
          </form>

        <?php endif; ?>

      <?php endif; ?>

    </div><!-- End of right col with bidding info -->

  </div><!-- End of row #2 -->

</div><!-- End of container -->

<?php include_once("footer.php") ?>

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
  // Sends auction ID as an argument to that function.
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
} // End of addToWatchlist

function removeFromWatchlist(button) {
  // This performs an asynchronous call to a PHP function using POST method.
  // Sends auction ID as an argument to that function.
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
} // End of removeFromWatchlist
</script>

<style>
.carousel-control-prev-icon,
.carousel-control-next-icon {
    background-size: 40px 40px;
    width: 40px;
    height: 40px;
    filter: invert(100%);
}

.carousel-control-prev,
.carousel-control-next {
    width: 8%;
}

.carousel-control-prev:hover,
.carousel-control-next:hover {
    opacity: 0.8;
}

.carousel {
    text-align: center;
}
</style>
