<?php include_once("header.php")?>
<?php require("utilities.php")?>
<?php require_once("database.php")?>

<div class="container">

<h2 class="my-3">Browse listings</h2>

<div id="searchSpecs">
<!-- When this form is submitted, this PHP page is what processes it.
     Search/sort specs are passed to this page through parameters in the URL
     (GET method of passing data to a page). -->
<form method="get" action="browse.php">
  <div class="row">
    <div class="col-md-5 pr-0">
      <div class="form-group">
        <label for="keyword" class="sr-only">Search keyword:</label>
	    <div class="input-group">
          <div class="input-group-prepend">
            <span class="input-group-text bg-transparent pr-0 text-muted">
              <i class="fa fa-search"></i>
            </span>
          </div>
          <input type="text" class="form-control border-left-0" id="keyword" name="keyword" placeholder="Search for anything">
        </div>
      </div>
    </div>
    <div class="col-md-3 pr-0">
      <div class="form-group">
        <label for="cat" class="sr-only">Search within:</label>
        <select class="form-control" id="cat" name="cat">
          <option selected value="all">All categories</option>
          <option value="fill">Fill me in</option>
          <option value="with">with options</option>
          <option value="populated">populated from a database?</option>
        </select>
      </div>
    </div>
    <div class="col-md-3 pr-0">
      <div class="form-inline">
        <label class="mx-2" for="order_by">Sort by:</label>
        <select class="form-control" id="order_by" name="order_by">
          <option selected value="pricelow">Price (low to high)</option>
          <option value="pricehigh">Price (high to low)</option>
          <option value="date">Soonest expiry</option>
        </select>
      </div>
    </div>
    <div class="col-md-1 px-0">
      <button type="submit" class="btn btn-primary">Search</button>
    </div>
  </div>
</form>
</div> <!-- end search specs bar -->


</div>

<?php
  // Retrieve these from the URL
  $keyword = $_GET['keyword'] ?? '';
  $category = $_GET['cat'] ?? 'all';
  $ordering = $_GET['order_by'] ?? 'pricelow';
  $curr_page = isset($_GET['page']) ? intval($_GET['page']) : 1;

  /* TODO: Use above values to construct a query. Use this query to 
     retrieve data from the database. (If there is no form data entered,
     decide on appropriate default value/default query to make. */

  $connection = connectDB();

  // Base query: join Item + Auction
  $query = "
    SELECT i.item_id, i.title, i.description, a.start_price, a.reserve_price, a.end_time
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
      case 'date':
          $query .= " ORDER BY a.end_time ASC";
          break;
      default:
          $query .= " ORDER BY a.start_price ASC";
  }

  $result = mysqli_query($connection, $query);

  /* For the purposes of pagination, it would also be helpful to know the
     total number of results that satisfy the above query */
  $num_results = mysqli_num_rows($result);
  $results_per_page = 10;
  $max_page = ceil($num_results / $results_per_page);
?>

<div class="container mt-5">

<!-- TODO: If result set is empty, print an informative message. Otherwise... -->

<ul class="list-group">

<?php
  // If no results found
  if ($num_results == 0) {
      echo '<div class="alert alert-info">No auctions found.</div>';
  } else {
      $count = 0;
      while ($row = mysqli_fetch_assoc($result)) {
          $count++;
          if ($count <= ($curr_page - 1) * $results_per_page) continue;
          if ($count > $curr_page * $results_per_page) break;

          $item_id = $row['item_id'];
          $title = $row['title'];
          $description = $row['description'];
          $current_price = $row['start_price']; // You can later replace with max(bid)
          $num_bids = 0; // Placeholder: can count from Bid table later
          $end_date = new DateTime($row['end_time']);

          // This uses a function defined in utilities.php
          print_listing_li($item_id, $title, $description, $current_price, $num_bids, $end_date);
      }
  }

  mysqli_close($connection);
?>

</ul>

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
  $low_page_boost = max(2 - ($max_page - $curr_page), 0);
  $low_page = max(1, $curr_page - 2 - $low_page_boost);
  $high_page = min($max_page, $curr_page + 2 + $high_page_boost);
  
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
      // Highlight the link
      echo('
    <li class="page-item active">');
    }
    else {
      // Non-highlighted link
      echo('
    <li class="page-item">');
    }
    
    // Do this in any case
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
