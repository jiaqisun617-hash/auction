<?php

// display_time_remaining:
// Helper function to help figure out what time to display
function display_time_remaining($interval) {

    if ($interval->days == 0 && $interval->h == 0) {
      // Less than one hour remaining: print mins + seconds:
      $time_remaining = $interval->format('%im %Ss');
    }
    else if ($interval->days == 0) {
      // Less than one day remaining: print hrs + mins:
      $time_remaining = $interval->format('%hh %im');
    }
    else {
      // At least one day remaining: print days + hrs:
      $time_remaining = $interval->format('%ad %hh');
    }

  return $time_remaining;

}

// print_listing_li:
// This function prints an HTML <li> element containing an auction listing
function print_listing_li($item_id, $title, $desc, $price, $num_bids, $end_time, $img_path)
{
  if (strlen($desc) > 250) {
    $desc_shortened = substr($desc, 0, 250) . '...';
  } else {
    $desc_shortened = $desc;
  }

  if ($num_bids == 1) $bid = " bid";
  else $bid = " bids";

  $now = new DateTime();
  if ($now > $end_time) {
    $time_remaining = 'Auction ended';
  } else {
    $interval = date_diff($now, $end_time);
    $time_remaining = display_time_remaining($interval);
  }

echo('
<div class="col-md-4 mb-4">
  <div class="card auction-card shadow-sm" style="border-radius: 16px; overflow: hidden; min-height: 100px;">

    <a href="listing.php?item_id=' . $item_id . '">
      <img src="' . $img_path . '" class="card-img-top"
           style="height: 260px; object-fit: cover;">
    </a>

    <div class="card-body">

     <h5 class="card-title">
  <a href="listing.php?item_id=' . $item_id . '" style="color: #2d3748; text-decoration: none;">
    ' . $title . '
  </a>
</h5>


      <p class="card-text text-muted" style="min-height: 40px;">
        ' . $desc_shortened . '
      </p>

      <!-- 黑色分隔线 -->
      <hr style="margin: 0 0 12px 0; border-top: 1px solid #ddd;">

      <div class="d-flex justify-content-between align-items-center">

        <!-- 左边：大号蓝色价格 -->
        <span style="font-size: 1.6em; font-weight: 700; color: #0d6efd;">
          £' . number_format($price, 2) . '
        </span>

        <!-- 右边：bid 数量 + 时间（纵向排列） -->
        <div class="text-end" style="line-height: 1.3;">

          <div class="text-success">
            <i class="fa-solid fa-gavel"></i>
            ' . $num_bids . $bid . '
          </div>

          <div class="text-danger">
            <i class="fa-regular fa-clock"></i>
            ' . $time_remaining . '
          </div>

        </div>

      </div>

    </div>

  </div>
</div>
');

}
