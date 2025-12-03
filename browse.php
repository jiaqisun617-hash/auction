<?php
require("utilities.php");
require_once("database.php");

$conn = connectDB();
include_once("header.php");

// Current selected category from URL (for highlighting in the grid)
$selected_category = $_GET['cat'] ?? 'all';
?>

<?php if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] != true) { ?>

</div>

      </a>
  </div>

</div>

<?php } ?>

<!-- Category icons above replace the category dropdown in the search bar -->
<!-- Include Font Awesome (if your header already has it, you can remove this) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
  .category-card {
    margin-bottom: 20px; /* Add spacing between top and bottom rows */
  }

  .category-card {
    background: #fafafa;
    border: 1px solid #eee;
    border-radius: 18px;
    height: 100px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 10px;
    transition: 0.25s;
    cursor: pointer;
  }

  .category-card:hover {
    background: #ffffff;
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  }

  .category-icon {
    font-size: 30px;
    color: #444;
  }

  .category-name {
    font-size: 18px;
    font-weight: 600;
    color: #222;
  }

  /* Highlight the currently selected category */
  .category-card-active {
    background: #222;
    border-color: #222;
  }

  .category-card-active .category-icon,
  .category-card-active .category-name {
    color: #fff;
  }
</style>

<div class="container my-5">
  <div class="row gy-4 gx-4">
    <?php
      require_once("database.php");
      $conn = connectDB();

      // Category icon mapping (you can change it anytime)
      $icons = [
        "Electronics"    => "fa-solid fa-tv",
        "Fashion"        => "fa-solid fa-shirt",
        "Home & Kitchen" => "fa-solid fa-couch",
        "Books"          => "fa-solid fa-book",
        "Toys"           => "fa-solid fa-puzzle-piece",
        "Sports"         => "fa-solid fa-basketball",
        "Collectibles"   => "fa-solid fa-gem",
        "Instruments"    => "fa-solid fa-guitar",  // NEW: dedicated icon
        "Others"         => "fa-solid fa-box"
      ];

      $sql    = "SELECT category_id, category_name FROM category";
      $result = $conn->query($sql);

      while ($row = $result->fetch_assoc()) {
          $cat_id   = $row['category_id'];
          $cat_name = htmlspecialchars($row['category_name']);

          // If the category name exists in the icon mapping → use the mapped icon
          // Otherwise use the default icon
          $icon_class = isset($icons[$cat_name]) ? $icons[$cat_name] : "fa-solid fa-tags";

          // Check if this card is the currently selected category
          $is_active    = ($selected_category !== 'all' && (string)$selected_category === (string)$cat_id);
          $active_class = $is_active ? ' category-card-active' : '';

          // Toggle behavior:
          // - If this category is already selected, clicking it again goes back to all listings (no cat parameter).
          // - Otherwise, clicking it filters by this category.
          $href = $is_active ? 'browse.php' : ('browse.php?cat=' . $cat_id);

          echo '
          <div class="col-6 col-md-3">
            <a href="' . $href . '" style="text-decoration:none;">
              <div class="category-card' . $active_class . '">
                <i class="category-icon ' . $icon_class . '"></i>
                <div class="category-name">' . $cat_name . '</div>
              </div>
            </a>
          </div>';
      }
    ?>
  </div>
</div>

<?php
// Read current filters from URL so we can reuse them
$keyword   = $_GET['keyword']  ?? '';
$category  = $_GET['cat']      ?? 'all';
$ordering  = $_GET['order_by'] ?? 'date'; // default: Ending time
$curr_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
?>

<div class="container">

  <div style="
    width:100%;
    height:1px;
    background:#e6e6e6;
    margin-bottom:25px;
  "></div>

  <h2 style="
      font-family: 'Playfair Display', serif;
      text-align:center;
      font-size: 48px;
      font-weight: 500;
      letter-spacing: 1px;
      margin: 50px 0 35px 0;
      color:#111;
  ">
    Browse Listings
  </h2>

  <div id="searchSpecs" class="mt-4 mb-4">
    <div style="
        background:white;
        border:1px solid #e6e6e6;
        border-radius:14px;
        padding:18px 22px;
        max-width:900px;
        margin:0 auto;
        box-shadow:0 2px 6px rgba(0,0,0,0.03);
    ">

      <form method="get" action="browse.php">
        <?php
          // Preserve selected category when searching / sorting
          if (isset($_GET['cat'])) {
              echo '<input type="hidden" name="cat" value="' . htmlspecialchars($_GET['cat']) . '">';
          }
        ?>

        <div class="search-bar-wrapper">

          <!-- Search box -->
          <div class="flex-grow-1">
            <div class="input-group search-input-group">
              <span class="input-group-text">
                <i class="fa fa-search text-muted"></i>
              </span>
              <input type="text"
                     class="form-control"
                     name="keyword"
                     placeholder="Search listings..."
                     value="<?php echo htmlspecialchars($keyword); ?>">
            </div>
          </div>

          <!-- Sort -->
          <select name="order_by" class="search-select">
            <option value="pricelow" <?php if ($ordering === 'pricelow') echo 'selected'; ?>>
              Price: Low → High
            </option>
            <option value="pricehigh" <?php if ($ordering === 'pricehigh') echo 'selected'; ?>>
              Price: High → Low
            </option>
            <option value="date" <?php if ($ordering === 'date') echo 'selected'; ?>>
              Ending time (soonest first)
            </option>
            <option value="bids" <?php if ($ordering === 'bids') echo 'selected'; ?>>
              Number of bids
            </option>
          </select>

          <!-- Button -->
          <button class="search-btn">Search</button>

        </div>
      </form>

    </div>
  </div>

</div>

<?php
  // Retrieve again for use in query (kept same structure as your original)
  $keyword   = $_GET['keyword']  ?? '';
  $category  = $_GET['cat']      ?? 'all';
  $ordering  = $_GET['order_by'] ?? 'date';
  $curr_page = isset($_GET['page']) ? intval($_GET['page']) : 1;

  /* Use above values to construct a query.
     Use this query to retrieve data from the database.
     (If there is no form data entered, decide on appropriate default query.) */

  $connection = connectDB();

  // Base query: join Item + Auction
  $query = "
    SELECT i.item_id, i.title, i.description, a.start_price, a.reserve_price, a.end_time, a.auction_id
    FROM Item i
    JOIN Auction a ON i.item_id = a.item_id
    WHERE 1 = 1
  ";

  // Filter: keyword search
  if (!empty($keyword)) {
      $safe_keyword = mysqli_real_escape_string($connection, $keyword);
      $query .= " AND (i.title LIKE '%$safe_keyword%' OR i.description LIKE '%$safe_keyword%')";
  }

  // Category filter (if applicable)
  if ($category !== 'all') {
      $safe_cat = mysqli_real_escape_string($connection, $category);
      $query .= " AND i.category_id = '$safe_cat'";
  }

  // Sorting logic
  switch ($ordering) {
      case 'pricehigh':
          $query .= " ORDER BY a.start_price DESC";
          break;

      case 'date':   // Ending time (soonest first)
          $query .= " ORDER BY a.end_time ASC";
          break;

      case 'bids':   // Sort by number of bids (highest first, then ending soonest)
          $query .= "
            ORDER BY
              (SELECT COUNT(*) FROM bid b WHERE b.auction_id = a.auction_id) DESC,
              a.end_time ASC
          ";
          break;

      default:       // pricelow
          $query .= " ORDER BY a.start_price ASC";
  }

  $result = mysqli_query($connection, $query);

  /* For the purposes of pagination, it is helpful to know the
     total number of results that satisfy the above query. */
  $num_results      = mysqli_num_rows($result);
  $results_per_page = 8;
  $max_page         = ceil($num_results / $results_per_page);
?>

<div class="container mt-5">

  <!-- If result set is empty, print an informative message. Otherwise... -->
  <div class="row">

  <?php
    // If no results found
    if ($num_results == 0) {
        echo '<div class="alert alert-info">No auctions found.</div>';
    } else {
        $count = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $count++;
            if ($count <= ($curr_page - 1) * $results_per_page) continue;
            if ($count >  $curr_page      * $results_per_page) break;

            $item_id     = $row['item_id'];
            $title       = $row['title'];
            $description = $row['description'];
            $auction_id  = $row['auction_id'];

            // Query max bid
            $sql_max  = "SELECT MAX(bid_amount) AS max_bid FROM bid WHERE auction_id = ?";
            $stmt_max = $connection->prepare($sql_max);
            $stmt_max->bind_param("i", $auction_id);
            $stmt_max->execute();
            $max_res  = $stmt_max->get_result();
            $max_row  = $max_res->fetch_assoc();

            $current_price = $max_row['max_bid'] ?? $row['start_price'];

            // Query number of bids
            $sql_count  = "SELECT COUNT(*) AS count_bids FROM bid WHERE auction_id = ?";
            $stmt_count = $connection->prepare($sql_count);
            $stmt_count->bind_param("i", $auction_id);
            $stmt_count->execute();
            $count_res  = $stmt_count->get_result();
            $count_row  = $count_res->fetch_assoc();

            $num_bids = $count_row['count_bids'];

            // Load main image
            $sql_img  = "SELECT path FROM image WHERE item_id = ? ORDER BY sort_order ASC LIMIT 1";
            $stmt_img = $connection->prepare($sql_img);
            $stmt_img->bind_param("i", $row['item_id']);
            $stmt_img->execute();
            $res_img  = $stmt_img->get_result();
            $img_path = $res_img->fetch_assoc()['path'] ?? 'uploads/default.jpg';

            if (empty($img_path)) {
                $img_path = 'uploads/default.jpg';
            }

            $end_date = new DateTime($row['end_time']);

            // This uses a function defined in utilities.php
            print_listing_li($item_id, $title, $description, $current_price, $num_bids, $end_date, $img_path);
        }
    }

    mysqli_close($connection);
  ?>

  </div>

  <!-- Pagination for results listings -->
  <nav aria-label="Search results pages" class="mt-5">
    <ul class="pagination justify-content-center">

    <?php
      // Copy any currently-set GET variables to the URL.
      $querystring = "";
      foreach ($_GET as $key => $value) {
        if ($key != "page") {
          $querystring .= "$key=$value&amp;";
        }
      }

      $high_page_boost = max(3 - $curr_page, 0);
      $low_page_boost  = max(2 - ($max_page - $curr_page), 0);
      $low_page        = max(1, $curr_page - 2 - $low_page_boost);
      $high_page       = min($max_page, $curr_page + 2 + $high_page_boost);

      if ($curr_page != 1) {
        echo('
        <li class="page-item">
          <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
            <span aria-hidden="true"><i class="fa fa-arrow-left"></i></span>
            <span class="sr-only">Previous</span>
          </a>
        </li>');
      }

      for ($i = $low_page; $i <= $high_page; $i++) {
        if ($i == $curr_page) {
          // Highlight the current page
          echo('
        <li class="page-item active">');
        } else {
          // Non-highlighted link
          echo('
        <li class="page-item">');
        }

        echo('
          <a class="page-link" href="browse.php?' . $querystring . 'page=' . $i . '">' . $i . '</a>
        </li>');
      }

      if ($curr_page != $max_page) {
        echo('
        <li class="page-item">
          <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
            <span aria-hidden="true"><i class="fa fa-arrow-right"></i></span>
            <span class="sr-only">Next</span>
          </a>
        </li>');
      }
    ?>
    </ul>
  </nav>

</div>

<?php include_once("footer.php")?>
