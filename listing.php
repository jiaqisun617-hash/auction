<?php require("utilities.php"); ?>
<?php require_once("database.php"); ?>
<?php include_once("header.php"); ?>

<?php
// Get item id from URL
if (!isset($_GET['item_id']) || !is_numeric($_GET['item_id'])) {
    echo "<div class='alert alert-danger'>Invalid item.</div>";
    include_once("footer.php");
    exit();
}

$item_id = (int)$_GET['item_id'];
$conn    = connectDB();

// Read any bid error message from session (set by place_bid.php)
$bid_error_message = null;
if (isset($_SESSION['bid_error'])) {
    $bid_error_message = $_SESSION['bid_error'];
    unset($_SESSION['bid_error']); // show once only
}

/*
 * Step 1: Load item + auction info
 */
$sql = "
    SELECT 
        Item.title,
        Item.description,
        Item.seller_id,
        Item.condition,
        Auction.start_price,
        Auction.reserve_price AS deposit_required,
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
$deposit_required = (float)$row['deposit_required'];

$now = new DateTime();

/*
 * Step 2: Highest bid / number of bids / current price
 */
$sql2 = "
    SELECT MAX(bid_amount) AS max_bid 
    FROM Bid 
    WHERE auction_id = ?
";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $auction_id);
$stmt2->execute();
$result2     = $stmt2->get_result();
$max_bid_row = $result2->fetch_assoc();

$current_price = $max_bid_row['max_bid'] ?? $row['start_price'];

$sql3 = "SELECT COUNT(*) AS count_bids FROM Bid WHERE auction_id = ?";
$stmt3 = $conn->prepare($sql3);
$stmt3->bind_param("i", $auction_id);
$stmt3->execute();
$result3 = $stmt3->get_result();
$num_bids = (int)$result3->fetch_assoc()['count_bids'];

/*
 * Step 3: Winner info if auction ended and there were bids
 */
if ($now > $end_time && $num_bids > 0) {
    $sql_winner = "
        SELECT u.username, b.bid_amount
        FROM Bid b
        JOIN User u ON b.bidder_id = u.user_id
        WHERE b.auction_id = ?
        ORDER BY b.bid_amount DESC, b.bid_time ASC
        LIMIT 1
    ";
    $stmt_winner = $conn->prepare($sql_winner);
    $stmt_winner->bind_param("i", $auction_id);
    $stmt_winner->execute();
    $res_winner = $stmt_winner->get_result();

    if ($res_winner && $res_winner->num_rows > 0) {
        $winner_row    = $res_winner->fetch_assoc();
        $winner_name   = $winner_row['username'];
        $current_price = $winner_row['bid_amount'];  // final price
    }
}

/*
 * Step 4: Is current user highest bidder? And watchlist status.
 */
$is_highest_bidder = false;
$watching          = false;

if (isset($_SESSION['user_id'])) {
    $current_user_id = (int)$_SESSION['user_id'];

    // Highest bidder check
    $sql_topbid = "
        SELECT bidder_id, bid_amount
        FROM Bid
        WHERE auction_id = ?
        ORDER BY bid_amount DESC, bid_time ASC
        LIMIT 1
    ";
    $stmt_topbid = $conn->prepare($sql_topbid);
    $stmt_topbid->bind_param("i", $auction_id);
    $stmt_topbid->execute();
    $res_topbid = $stmt_topbid->get_result();

    if ($res_topbid && $res_topbid->num_rows > 0) {
        $top = $res_topbid->fetch_assoc();
        if ((int)$top['bidder_id'] === $current_user_id) {
            $is_highest_bidder = true;
        }
    }

    // Watchlist check
    $sql_watch = "SELECT 1 FROM watchlist WHERE user_id=? AND auction_id=?";
    $stmt_watch = $conn->prepare($sql_watch);
    $stmt_watch->bind_param("ii", $current_user_id, $auction_id);
    $stmt_watch->execute();
    $res_watch = $stmt_watch->get_result();

    if ($res_watch->num_rows > 0) {
        $watching = true;
    }
}

/*
 * Step 5: Time remaining string
 */
if ($now < $end_time) {
    $time_to_end    = date_diff($now, $end_time);
    $time_remaining = ' (in ' . display_time_remaining($time_to_end) . ')';
} else {
    $time_remaining = '';
}

/*
 * Step 6: Load images for carousel
 */
$sql_img = "SELECT path FROM image WHERE item_id = ? ORDER BY sort_order ASC";
$stmt_img = $conn->prepare($sql_img);
$stmt_img->bind_param("i", $item_id);
$stmt_img->execute();
$res_img = $stmt_img->get_result();

$images = [];
while ($row_img = $res_img->fetch_assoc()) {
    $images[] = $row_img['path'];
}

// For watchlist JS
$has_session = isset($_SESSION['user_id']);

?>

<div class="container">

<div class="row"><!-- Row #1 with auction title + watch button -->
  <div class="col-sm-8">
    <h2 class="my-3"><?php echo htmlspecialchars($title); ?></h2>
  </div>
  <div class="col-sm-4 align-self-center">
<?php if ($now < $end_time): ?>
    <div id="watch_nowatch" <?php if ($has_session && $watching) echo 'style="display: none"'; ?>>
      <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addToWatchlist()">+ Add to watchlist</button>
    </div>
    <div id="watch_watching" <?php if (!$has_session || !$watching) echo 'style="display: none"'; ?>>
      <button type="button" class="btn btn-success btn-sm" disabled>Watching</button>
      <button type="button" class="btn btn-danger btn-sm" onclick="removeFromWatchlist()">Remove watch</button>
    </div>
<?php endif; ?>
  </div>
</div>

<div class="row"><!-- Row #2: description + bidding info -->
  <div class="col-sm-8"><!-- Left column: description + images -->

    <div class="itemDescription">
      <?php echo nl2br(htmlspecialchars($description)); ?>
      <br><br><br>
    </div>

    <?php if (count($images) > 0): ?>
    <div id="itemCarousel" class="carousel slide mb-3" data-ride="carousel">

      <!-- Indicators -->
      <ol class="carousel-indicators">
        <?php foreach ($images as $idx => $img): ?>
          <li data-target="#itemCarousel" data-slide-to="<?php echo $idx; ?>"
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

  </div><!-- /left col -->

  <div class="col-sm-4"><!-- Right column: bidding / result info -->

    <?php if ($now > $end_time): ?>
      <!-- Auction ended: show result -->
      <h4 class="my-3">Auction result</h4>
      <p>This auction ended <?php echo $end_time->format('j M H:i'); ?></p>

      <?php if ($num_bids > 0): ?>
        <p>Final price: £<?php echo number_format($current_price, 2); ?></p>
        <p>Total bids: <?php echo $num_bids; ?></p>
        <?php if (isset($winner_name)): ?>
          <p>Winner: <?php echo htmlspecialchars($winner_name); ?></p>
        <?php endif; ?>
      <?php else: ?>
        <p>No bids were placed. This auction ended without a sale.</p>
      <?php endif; ?>

    <?php else: ?>
      <!-- Auction still live -->
      <p>
        Auction ends <?php echo $end_time->format('j M H:i') . $time_remaining; ?>
      </p>

      <p class="lead">
        Current bid: £<?php echo number_format($current_price, 2); ?>
      </p>

      <?php if ($deposit_required > 0): ?>
        <p class="text-muted" style="font-size: 0.9rem;">
          Required deposit: £<?php echo number_format($deposit_required, 2); ?>
        </p>
      <?php endif; ?>

    <?php endif; ?>

    <!-- Highest bidder / winner info for the current user -->
    <?php if ($is_highest_bidder && $now <= $end_time): ?>
      <div class="alert alert-success mt-2">
        You are currently the highest bidder.
      </div>
    <?php elseif ($is_highest_bidder && $now > $end_time): ?>
      <div class="alert alert-info mt-2">
        You won this auction.
      </div>
    <?php endif; ?>

    <?php
      // Decide if the logged-in user can bid
      $can_bid       = false;
      $is_seller     = false;
      $not_logged_in = false;

      if (!isset($_SESSION['user_id'])) {
          $not_logged_in = true;
      } else {
          $uid       = (int)$_SESSION['user_id'];
          $user_type = $_SESSION['account_type'] ?? null; // buyer / seller / admin

          if ($uid === $seller_id) {
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

      <?php elseif (isset($_SESSION['account_type']) && $_SESSION['account_type'] !== "buyer"): ?>

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

          <?php if (!empty($bid_error_message)): ?>
            <div class="alert alert-danger mt-2">
              <?php echo htmlspecialchars($bid_error_message); ?>
            </div>
          <?php endif; ?>
        </form>

      <?php endif; ?>

    <?php endif; ?>

  </div><!-- /right col -->
</div><!-- /row #2 -->

<?php include_once("footer.php"); ?>

<script>
// JavaScript functions: addToWatchlist and removeFromWatchlist.
var isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

function addToWatchlist() {
  if (!isLoggedIn) {
    alert("Please log in to add items to your watchlist.");
    $('#loginModal').modal('show');
    return;
  }

  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: {functionname: 'add_to_watchlist', arguments: [<?php echo $auction_id; ?>]},

    success:
      function (obj, textstatus) {
        var objT = obj.trim();

        if (objT === "not_logged_in") {
          $('#loginModal').modal('show');
          return;
        }

        if (objT === "success") {
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
        console.log("Error in addToWatchlist");
      }
  });
}

function removeFromWatchlist() {
  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: {functionname: 'remove_from_watchlist', arguments: [<?php echo $auction_id; ?>]},

    success:
      function (obj, textstatus) {
        var objT = obj.trim();

        if (objT === "success") {
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
        console.log("Error in removeFromWatchlist");
      }
  });
}
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
