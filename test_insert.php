<?php
$pageTitle = "Insert Test Record";
require_once('pageheader.php');
require_once('database.php');

$conn = connectDB();

// 这里假设表名叫 auctions
$query = "INSERT INTO auctions (title, description, start_price, current_price, end_time)
          VALUES ('Test Item', 'This is a test record.', 10.00, 10.00, '2025-12-31 23:59:59')";

if (mysqli_query($conn, $query)) {
    echo "<p>✅ A new record has been inserted successfully!</p>";
} else {
    echo "<p>❌ Error inserting record: " . mysqli_error($conn) . "</p>";
}

mysqli_close($conn);
?>
</body>
</html>


<!-- 是实话实说 -->