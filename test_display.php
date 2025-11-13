<?php
$pageTitle = "Display All Auctions";
require_once('pageheader.php');
require_once('database.php');

$conn = connectDB();

$sql = "SELECT * FROM auctions";
$result = mysqli_query($conn, $sql)
    or die('❌ Error making SELECT query: ' . mysqli_error($conn));

echo "<h2>All Auction Items</h2>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Title</th><th>Description</th><th>Price (£)</th><th>End Time</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>
            <td>{$row['auction_id']}</td>
            <td>{$row['title']}</td>
            <td>{$row['description']}</td>
            <td>{$row['current_price']}</td>
            <td>{$row['end_time']}</td>
          </tr>";
}

echo "</table>";

mysqli_close($conn);
?>
</body>
</html#111111
