<?php include_once("header.php"); ?>

<div class="hero-section">
    <video autoplay muted loop playsinline class="hero-video">
        <source src="uploads/hero-banner.mp4" type="video/mp4">
    </video>

    <div class="hero-content">
        <h1>Start Your Auction</h1>
        <p>Find unique items with best value</p>

        <!-- ★ 把按钮放在 hero-content 内部 ★ -->
        <a href="browse.php" class="hero-btn">Shop Now</a>
    </div>
</div>

<style>
.hero-section {
    position: relative;
    width: 100vw;
    height: 100vh; /* 全屏 */
    display: flex;
    align-items: center;
    padding-left: 80px;
    color: white;
    overflow: hidden;
}

.hero-video {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    object-fit: cover;
    z-index: -1;
    filter: brightness(60%);
}

.hero-content {
    position: relative;
    z-index: 10;
    max-width: 600px;
}

.hero-content h1 {
    font-size: 60px;
    font-weight: 700;
    margin-bottom: 16px;
}

.hero-content p {
    font-size: 22px;
    margin-bottom: 30px; /* ★ 给按钮预留空间 */
}

.hero-btn {
    background: white;
    padding: 14px 30px;
    border-radius: 10px;
    color: #333;
    font-weight: 600;
    font-size: 18px;
    text-decoration: none;
    display: inline-block;
    transition: 0.25s;
}

.hero-btn:hover {
    transform: translateY(-2px);
    background: #f2f2f2;
}
</style>

<?php include_once("footer.php"); ?>
