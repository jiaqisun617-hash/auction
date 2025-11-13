<?php
$pageTitle = "Test Connection";
require_once('pageheader.php');
require_once('database.php');

$conn = connectDB();

echo "<p>✅ Connected successfully!</p>";
echo "<p>User: <b>jiaqi_user</b><br>Database: <b>auctiondb</b></p>";

mysqli_close($conn);
?>
</body>
</html>
