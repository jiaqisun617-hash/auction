<?php
require_once('database.php');
$connection = connectDB(); // ✅ 调用函数获得连接

echo "<h3>Testing Item + Auction Insertion</h3>";

// Step 1: Insert a sample user (seller)
$seller_email = "seller1@example.com";
$seller_password = password_hash("password123", PASSWORD_DEFAULT);
$seller_name = "Test Seller";

// Check if user already exists
$user_check = "SELECT user_id FROM User WHERE email='$seller_email'";
$result = mysqli_query($connection, $user_check);
if (!$result) {
    die('❌ Query error: ' . mysqli_error($connection));
}

if (mysqli_num_rows($result) == 0) {
    $insert_user = "INSERT INTO User (email, password_hash, name)
                    VALUES ('$seller_email', '$seller_password', '$seller_name')";
    if (mysqli_query($connection, $insert_user)) {
        echo "✅ User inserted.<br>";
    } else {
        die('❌ Error inserting user: ' . mysqli_error($connection));
    }
} else {
    echo "ℹ️ User already exists.<br>";
}

// Get seller_id
$user_result = mysqli_query($connection, "SELECT user_id FROM User WHERE email='$seller_email'");
$seller = mysqli_fetch_assoc($user_result);
$seller_id = $seller['user_id'];

// Step 2: Insert an Item
$title = "Vintage Camera";
$desc = "A working 1960s film camera with lens.";
$condition = "Used";
$insert_item = "INSERT INTO Item (title, description, `condition`, seller_id)
                VALUES ('$title', '$desc', '$condition', $seller_id)";
if (mysqli_query($connection, $insert_item)) {
    $item_id = mysqli_insert_id($connection);
    echo "✅ Item inserted with ID: $item_id<br>";
} else {
    die('❌ Error inserting item: ' . mysqli_error($connection));
}

// Step 3: Insert Auction for this Item
$start_price = 100.00;
$reserve_price = 150.00;
$end_time = date('Y-m-d H:i:s', strtotime('+3 days'));
$insert_auction = "INSERT INTO Auction (item_id, start_price, reserve_price, end_time)
                   VALUES ($item_id, $start_price, $reserve_price, '$end_time')";
if (mysqli_query($connection, $insert_auction)) {
    $auction_id = mysqli_insert_id($connection);
    echo "✅ Auction inserted with ID: $auction_id<br>";
} else {
    die('❌ Error inserting auction: ' . mysqli_error($connection));
}

// Step 4: Verify
echo "<p><strong>All insertions completed successfully!</strong></p>";

mysqli_close($connection);
?>
