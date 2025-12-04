<?php
               
require_once("database.php");
$connection = connectDB();
include_once("header.php");
?>

<div class="container my-5">


<?php
/*
    TODO #1: Connect to MySQL database (perhaps by requiring a file that
             already does this).
*/


/*
    TODO #2: Extract form data into variables. Because the form was a 'post'
             form, its data can be accessed via $_POST['auctionTitle'], 
             $_POST['auctionDetails'], etc. Perform checking on the data to
             make sure it can be inserted into the database. If there is an
             issue, give some semi-helpful feedback to user.
*/
$title          = trim($_POST['auctionTitle'] ?? '');
$description    = trim($_POST['auctionDetails'] ?? '');
$condition      = trim($_POST['itemCondition'] ?? '');
$start_price_in = $_POST['startPrice'] ?? '';
$end_time       = $_POST['endTime'] ?? '';
$category_id = $_POST['auctionCategory'];

// $seller_id      = 1; // Demo only: normally from authenticated user session
$seller_id = $_SESSION['user_id'];

$errors = [];

// basic validation
if ($title === '' || $description === '' || $condition === '' || $start_price_in === '' || $end_time === '') {
    $errors[] = "All required fields must be filled in.";
}

if (!is_numeric($start_price_in) || floatval($start_price_in) <= 0) {
    $errors[] = "Starting price must be a positive number.";
}

if (strtotime($end_time) <= time()) {
    $errors[] = "Auction end time must be in the future.";
}

// compute prices
$start_price   = is_numeric($start_price_in) ? floatval($start_price_in) : 0.0;
/* Reserve price is auto-calculated as 20% of the starting price (not 120%). */
$reserve_price = round($start_price * 0.20, 2);

// if errors, display message and stop
if (!empty($errors)) {
    echo '<div class="alert alert-danger"><h4>Cannot create auction:</h4><ul>';
    foreach ($errors as $e) {
        echo "<li>" . htmlspecialchars($e) . "</li>";
    }
    echo '</ul><a href="create_auction.php" class="btn btn-secondary mt-3">Go back</a></div>';
    include_once("footer.php");
    exit;
}

/*
    TODO #3: If everything looks good, make the appropriate call to insert
             data into the database.
*/


// Insert Item
$item_sql  = "INSERT INTO Item (title, description, category_id, `condition`, seller_id) VALUES (?,?, ?, ?, ?)";
$item_stmt = mysqli_prepare($connection, $item_sql);
mysqli_stmt_bind_param($item_stmt, "ssssi", $title, $description,$category_id, $condition, $seller_id);

if (!mysqli_stmt_execute($item_stmt)) {
    echo '<div class="alert alert-danger">Error inserting item into database.</div>';
    mysqli_stmt_close($item_stmt);
    include_once("footer.php");
    exit;
}
$item_id = mysqli_insert_id($connection);
mysqli_stmt_close($item_stmt);

// Handle Image Uploads
if (isset($_FILES['item_images']) && !empty($_FILES['item_images']['name'][0])) {

    $files = $_FILES['item_images'];

    for ($i = 0; $i < count($files['name']); $i++) {

        if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

        $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
        if ($ext == '') $ext = 'jpg';

        $filename = 'item_' . $item_id . '_' . time() . '_' . $i . '.' . $ext;
        $dest = 'uploads/' . $filename;

        move_uploaded_file($files['tmp_name'][$i], $dest);

        $sort_order = $i + 1;

        $sql_img = "INSERT INTO image (item_id, path, sort_order)
                    VALUES (?, ?, ?)";
        $stmt_img = $connection->prepare($sql_img);
        $stmt_img->bind_param("isi", $item_id, $dest, $sort_order);
        $stmt_img->execute();
    }
}



// Insert Auction
$auction_sql  = "INSERT INTO Auction (item_id, start_price, reserve_price, end_time) VALUES (?, ?, ?, ?)";
$auction_stmt = mysqli_prepare($connection, $auction_sql);
mysqli_stmt_bind_param($auction_stmt, "idds", $item_id, $start_price, $reserve_price, $end_time);

if (!mysqli_stmt_execute($auction_stmt)) {
    echo '<div class="alert alert-danger">Error inserting auction into database.</div>';
    mysqli_stmt_close($auction_stmt);
    include_once("footer.php");
    exit;
}

mysqli_stmt_close($auction_stmt);

// Success
echo '<div class="alert alert-success text-center p-4">
        <h4>✅ Auction successfully created!</h4>
        <p>Start price: £' . number_format($start_price, 2) . 
        ' — Reserve price (20%): £' . number_format($reserve_price, 2) . '</p>
        <p><a href="browse.php">View listings</a> or <a href="listing.php?item_id=' . intval($item_id) . '">open this item</a>.</p>
      </div>';

mysqli_close($connection);
?>

</div>

<?php include_once("footer.php"); ?>
