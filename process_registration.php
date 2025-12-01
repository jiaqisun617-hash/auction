<?php
include_once("database.php");

/*
 // TODO: Extract $_POST variables, check they're OK, and attempt to create
 // an account. Notify user of success/failure and redirect/give navigation 
 // options.
*/

// --- Step 1: Connect to database ---
$connection = connectDB();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // --- Step 2: Extract form data ---
    $accountType = $_POST['accountType'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_repeat = $_POST['password_repeat'] ?? '';
    $username = trim($_POST['username'] ?? '');


    $errors = [];

    // --- Step 3: Validate inputs ---
    if (empty($email) || empty($password) || empty($password_repeat)) {
        $errors[] = "Email and both password fields are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if ($password !== $password_repeat) {
        $errors[] = "Passwords do not match.";
        
    if (empty($username)) {
    $errors[] = "Username is required.";
}

    }

    // --- Step 4: If there are validation errors, show message ---
    if (!empty($errors)) {
        include_once("header.php");
        echo '<div class="container my-5">';
        echo '<div class="alert alert-danger"><h5>Registration failed:</h5><ul>';
        foreach ($errors as $e) {
            echo "<li>$e</li>";
        }
        echo '</ul>';
        echo '<a href="register.php" class="btn btn-secondary mt-3">Go Back</a>';
        echo '</div></div>';
        include_once("footer.php");
        exit;
    }

    // --- Step 5: Hash password securely ---
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // --- Step 6: Check if email already exists ---
$check_query = "SELECT * FROM User WHERE email = ?";
$stmt = $connection->prepare($check_query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// 如果邮箱存在，不创建新 user，而是进入“追加角色”逻辑
if ($result->num_rows > 0) {
    $existing_user = $result->fetch_assoc();
    $user_id = $existing_user['user_id'];
    
    // 查 role_id
    $role_query = "SELECT role_id FROM Role WHERE role_name = ?";
    $stmt_role = $connection->prepare($role_query);
    $stmt_role->bind_param("s", $accountType);
    $stmt_role->execute();
    $role_row = $stmt_role->get_result()->fetch_assoc();
    $role_id = $role_row['role_id'];

    // 尝试添加角色
    $insert_user_role = "INSERT IGNORE INTO UserRole (user_id, role_id) VALUES (?, ?)";
    $stmt_ur = $connection->prepare($insert_user_role);
    $stmt_ur->bind_param("ii", $user_id, $role_id);
    $stmt_ur->execute();

    include_once("header.php");
    echo '<div class="container my-5">';

    if ($stmt_ur->affected_rows === 0) {
        echo "<div class='alert alert-warning text-center'>
                You already have the <strong>$accountType</strong> role.
              </div>";
    } else {
        echo "<div class='alert alert-success text-center'>
                Added new role <strong>$accountType</strong> to your account!
              </div>";
    }

    echo '<a href="#" data-toggle="modal" data-target="#loginModal" class="btn btn-primary mt-3">Login</a>';
    echo '</div>';
    include_once("footer.php");
    exit;
}



    // --- Step 7: Insert new user into database ---
    $insert_user = "INSERT INTO User (username, email, password_hash, account_type, created_at) VALUES (?,?,?,?, NOW())";
    $stmt = $connection->prepare($insert_user);
    $stmt->bind_param("ssss", $username, $email, $password_hash,$accountType);

    if ($stmt->execute()) {
        // --- Step 8: Assign role (buyer or seller) ---
        $user_id = $connection->insert_id;

        // 查 role_id
        $role_query = "SELECT role_id FROM Role WHERE role_name = ?";
        $stmt_role = $connection->prepare($role_query);
        $stmt_role->bind_param("s", $accountType);
        $stmt_role->execute();
        $role_result = $stmt_role->get_result();

        if ($role_result->num_rows > 0) {
            $role_row = $role_result->fetch_assoc();
            $role_id = $role_row['role_id'];

            // 使用 INSERT IGNORE 防止重复添加同一角色
            $insert_user_role = "INSERT IGNORE INTO UserRole (user_id, role_id) VALUES (?, ?)";
            $stmt_ur = $connection->prepare($insert_user_role);
            $stmt_ur->bind_param("ii", $user_id, $role_id);
            $stmt_ur->execute();

            // 如果没有新增任何 row，说明之前已经有这个角色
            if ($stmt_ur->affected_rows === 0) {
                echo "<div class='alert alert-warning text-center mt-4'>
                        You have already registered as a $accountType.
                    </div>";
            } else {
                echo "<div class='alert alert-success text-center mt-4'>
                        Successfully registered as a $accountType!
                    </div>";
            }
        }



        // --- Step 9: Success message ---
        include_once("header.php");
        echo '<div class="container my-5">';
        echo '<div class="alert alert-success">';
        echo '<h5>Account created successfully!</h5>';
        echo '<p>You can now <a href="#" data-toggle="modal" data-target="#loginModal">log in</a>.</p>';
        echo '</div></div>';
        include_once("footer.php");

    } else {
        include_once("header.php");
        echo '<div class="container my-5">';
        echo '<div class="alert alert-danger">Database error: Failed to register user.<br>';
        echo '<a href="register.php" class="btn btn-secondary mt-3">Go Back</a></div></div>';
        include_once("footer.php");
    }

    $stmt->close();
    $connection->close();
}
?>
