

<!-- // // TODO: Extract $_POST variables, check they're OK, and attempt to login.
// // Notify user of success/failure and redirect/give navigation options.

// // For now, I will just set session variables and redirect.

// session_start();
// $_SESSION['logged_in'] = true;
// $_SESSION['username'] = "test";
// $_SESSION['account_type'] = "buyer";

// echo('<div class="text-center">You are now logged in! You will be redirected shortly.</div>');

// // Redirect to index after 5 seconds
// header("refresh:5;url=index.php"); -->

<?php
session_start();

$email = $_POST['email'];
$password = $_POST['password'];

require_once('database.php'); 
$conn = connectDB();

$query = "SELECT * FROM user WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

    
    if (password_verify($password, $row['password_hash'])) {


        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $row['username'];
        $_SESSION['account_type'] = $row['account_type'];

    
        echo('<div class="text-center mt-5" style="font-size:20px;">Welcome, ' 
              . htmlspecialchars($row['username']) . 
              '! You are now logged in. Redirecting...</div>');

        header("refresh:3;url=index.php");
        exit();
    } 
    else {
        echo "Invalid password.";
        exit();
    }

} else {
    echo "Email does not exist.";
    exit();
}

?>

