<?php include_once("header.php") ?>

<div class="container mt-5">
  <div class="alert alert-success p-5" style="font-size: 1.2rem;">
    <h3 class="mb-3">
      ✅ Bid successfully placed!
    </h3>

    <p>Your bid has been submitted.</p>

    <a href="browse.php">View listings</a> 
    or 
    <a href="listing.php?item_id=<?php echo $_GET['item_id']; ?>">return to this item</a>.
  </div>
</div>

<?php include_once("footer.php") ?>
