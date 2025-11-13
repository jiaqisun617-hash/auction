<?php
$pageTitle = "Delete Record";
require_once('pageheader.php');
require_once('database.php');

$conn = connectDB();

// Example: delete the record with auction_id = 1
$id_to_delete = 4;
$query = "DELETE FROM auctions WHERE auction_id = $id_to_delete";

if (mysqli_query($conn, $query)) {
    echo "<p>✅ Record with ID = $id_to_delete has been deleted successfully.</p>";
} else {
    echo "<p>❌ Error deleting record: " . mysqli_error($conn) . "</p>";
}

mysqli_close($conn);
?>
</body>
</html>
