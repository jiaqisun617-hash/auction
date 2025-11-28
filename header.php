<?php
  // FIXME: At the moment, I've allowed these values to be set manually.
  // But eventually, with a database, these should be set automatically
  // ONLY after the user's login credentials have been verified via a 
  // database query.
  session_start();

?>


<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  
  <!-- 引入Bootstrap和icon和字体 -->
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
  .design {
      font-family: 'Playfair Display', serif !important;
      letter-spacing: 0.5px;  
  }
</style>




  <!-- Custom CSS file -->
  <link rel="stylesheet" href="css/custom.css">

  <title>Princess Auction <!--CHANGEME!--></title>
  <style>
/* 整个卡片 hover 上浮 */
.auction-card {
    transition: all 0.25s ease;
}

.auction-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 10px 22px rgba(0, 0, 0, 0.15);
}

/* 图片变暗 */
.auction-card img {
    transition: all 0.25s ease;
}

.auction-card:hover img {
    filter: brightness(70%);
}

.search-bar-wrapper {
    display: flex;
    align-items: center;
    gap: 16px;
}

.search-input-group {
    height: 44px;
}

.search-input-group .input-group-text {
    background: #fff;
    border: 1px solid #dcdcdc;
    border-right: none;
    border-radius: 10px 0 0 10px;
}

.search-input-group input {
    height: 44px;
    border: 1px solid #dcdcdc;
    border-left: none;
    border-radius: 0 10px 10px 0;
    font-size: 15px;
}

.search-select {
    height: 44px;
    border-radius: 10px;
    border: 1px solid #dcdcdc;
    background: #fafafa;
    font-size: 14px;       
    color: #555;           
    padding: 0 14px;
    width: auto;      
   min-width: 150px;       
    cursor: pointer;
}

.search-select:hover {
    background: #f2f2f2;
}


.search-btn {
    height: 44px;
    width: 110px;
    background: #333;
    color: white;
    border-radius: 10px;
    font-weight: 600;
    border: none;
    transition: 0.25s;
}

.search-btn:hover {
    background: #111;
    transform: translateY(-2px);
}

</style>

</head>


<body>

<!-- Modern Premium Navbar -->
<nav class="navbar navbar-expand-lg" 
     style="background:#ffffff; border-bottom:1px solid #eaeaea; padding:14px 32px;">

  <!-- Left: Logo -->
  <a class="navbar-brand design" href="browse.php" 
     style="font-size:24px; font-weight:700; color:#333; margin-right:40px">
     
     Princess Auction
  </a>

  <!-- Center: Menu -->
  <div class="collapse navbar-collapse" id="mainNavbar">
    <ul class="navbar-nav" style="gap:24px; font-size:16px;">

      <li class="nav-item">
        <a class="nav-link" href="browse.php" style="color:#333;">Browse</a>
      </li>

      <?php
      if (isset($_SESSION['account_type']) && $_SESSION['account_type'] == 'buyer') {
          echo '
            <li class="nav-item"><a class="nav-link" href="mybids.php" style="color:#333;">My Bids</a></li>
            <li class="nav-item"><a class="nav-link" href="mywatchlist.php" style="color:#333;">My Watchlist</a></li>
            <li class="nav-item"><a class="nav-link" href="recommendations.php" style="color:#333;">Recommended</a></li>
          ';
      }

      if (isset($_SESSION['account_type']) && $_SESSION['account_type'] == 'seller') {
          echo '
            <li class="nav-item"><a class="nav-link" href="mylistings.php" style="color:#333;">My Listings</a></li>
            <li class="nav-item">
                <a class="nav-link" href="create_auction.php"
                   style="padding:6px 14px; border:2px solid #333; border-radius:6px; font-weight:600; color:#333;">
                    + Create Auction
                </a>
            </li>
          ';
      }
      ?>
    </ul>
  </div>

  <!-- Right: User / Login -->
  <ul class="navbar-nav ml-auto" style="font-size:16px; gap:16px;">
    <?php
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
        echo '
        <li class="nav-item">
            <span class="nav-link" style="color:#555;">Welcome, <b>' . htmlspecialchars($_SESSION['username']) . '</b></span>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="logout.php" style="color:#333;">Logout</a>
        </li>';
    } else {
        echo '
        <li class="nav-item">
            <button type="button" class="btn btn-outline-dark"
                    data-toggle="modal" data-target="#loginModal"
                    style="padding:6px 14px; border-radius:6px;">
              Login
            </button>
        </li>';
    }
    ?>
  </ul>

</nav>



<!-- Login modal -->
<div class="modal fade" id="loginModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Login</h4>
      </div>

      <!-- Modal body -->
      <div class="modal-body">
        <form method="POST" action="login_result.php">
          <div class="form-group">
            <label for="email">Email</label>
            <input type="text" class="form-control" id="email" name="email" placeholder="Email">
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Password">
          </div>
          <button type="submit" class="btn btn-primary form-control">Sign in</button>
        </form>
        <div class="text-center">or <a href="register.php">create an account</a></div>
      </div>

    </div>
  </div>
</div> <!-- End modal -->