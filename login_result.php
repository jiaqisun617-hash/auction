    <?php
session_start();

$email    = $_POST['email']    ?? '';
$password = $_POST['password'] ?? '';

// Where to go after successful login (default: index.php)
$redirect = $_POST['redirect_url'] ?? 'index.php';
// Safety: do not allow full external URLs
if (strpos($redirect, 'http') === 0) {
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

        // Read roles from userrole
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

        // No text output, just redirect back to the previous page
        header("Location: " . $redirect);
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
