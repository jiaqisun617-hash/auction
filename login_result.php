<?php
session_start();

$email    = $_POST['email']    ?? '';
$password = $_POST['password'] ?? '';

// 1. Decide where to go after successful login
$redirect = $_POST['redirect_url'] ?? 'index.php';


if (strpos($redirect, 'http') === 0) {
    $redirect = 'index.php';
}

if (strpos($redirect, 'process_registration.php') !== false) {
    $redirect = 'index.php';
}

require_once('database.php'); 
$conn = connectDB();

$query = "SELECT * FROM user WHERE email = ?";
$stmt  = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

    if (password_verify($password, $row['password_hash'])) {

        $_SESSION['logged_in']    = true;
        $_SESSION['username']     = $row['username'];
        $_SESSION['account_type'] = $row['account_type'];
        $_SESSION['user_id']      = $row['user_id']; 

        // Fetch user roles
        $role_sql = "
            SELECT role.role_name 
            FROM role
            JOIN userrole ON role.role_id = userrole.role_id
            WHERE userrole.user_id = ?
        ";
        $stmt2 = $conn->prepare($role_sql);
        $stmt2->bind_param("i", $row['user_id']);
        $stmt2->execute();
        $role_result = $stmt2->get_result();

        $roles = [];
        while ($r = $role_result->fetch_assoc()) {
            $roles[] = $r['role_name'];
        }
        $_SESSION['roles'] = $roles;

        $safe_username = htmlspecialchars($row['username'], ENT_QUOTES);
        $safe_redirect = htmlspecialchars($redirect, ENT_QUOTES);

        echo '<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Login successful</title>
  <meta http-equiv="refresh" content="3;url=' . $safe_redirect . '">
  <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>
  <div class="container">
    <div class="text-center mt-5" style="font-size:20px;">
      Welcome, ' . $safe_username . '! You are now logged in. Redirecting...
    </div>
    <div class="text-center mt-3">
      If you are not redirected automatically, <a href="' . $safe_redirect . '">click here</a>.
    </div>
  </div>
</body>
</html>';
        exit();

    } else {
        echo "Invalid password.";
        exit();
    }

} else {
    echo "Email does not exist.";
    exit();
}
