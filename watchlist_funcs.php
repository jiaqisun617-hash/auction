 <?php
session_start();


if (!isset($_POST['functionname']) || !isset($_POST['arguments'])) {
  return;
}
require_once("database.php");
$conn = connectDB();
// Extract arguments from the POST variables:
$auction_id = $_POST['arguments'][0];
$user_id = $_SESSION['user_id'];

  // TODO: Update database and return success/failure.
$func = $_POST['functionname'];

if ($func == "add_to_watchlist") {
    $sql = "INSERT IGNORE INTO watchlist (user_id, auction_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $auction_id);
    $stmt->execute();
    $res = "success";
}
 
 // TODO: Update database and return success/failure.
else if ($func == "remove_from_watchlist") {
    $sql = "DELETE FROM watchlist WHERE user_id = ? AND auction_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $auction_id);
    $stmt->execute();
    $res = "success";
}

// Note: Echoing from this PHP function will return the value as a string.
// If multiple echo's in this file exist, they will concatenate together,
// so be careful. You can also return JSON objects (in string form) using
// echo json_encode($res).
echo $res;

?>