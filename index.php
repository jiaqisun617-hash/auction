<?php
include_once("header.php");
?>

<!-- Hero Banner -->
<div class="hero-section" 
     style="
       background: url('https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?auto=format&w=1600') 
       center/cover no-repeat;
       height: 420px;
       display: flex;
       align-items: center;
       padding: 40px;
       color: white;
     ">
  <div>
    <h1 style="font-size: 40px; font-weight: 700; line-height: 1.2;">
      Start Your Auction
    </h1>
    <p style="font-size: 18px; margin: 10px 0 24px;">
      Find unique items with best value
    </p>

    <!-- Shop Now 按钮 → 跳转到 browse.php -->
    <a href="browse.php" 
       style="
         background: white;
         padding: 12px 24px;
         border-radius: 8px;
         color: #333;
         font-weight: 600;
         text-decoration: none;
       ">
       Shop Now
    </a>
  </div>
</div>

<?php
include_once("footer.php");
?>
