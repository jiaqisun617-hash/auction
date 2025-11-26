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
  <a href="listing.php?item_id=' . $item_id . '" style="text-decoration:none; color:inherit;">
    <div class="card product-card border-0 shadow-sm" 
         style="border-radius:16px; overflow:hidden; transition:transform .25s ease, box-shadow .25s ease;">

      <!-- 图片固定比例：更专业 -->
      <div style="height:220px; overflow:hidden; background:#f8f8f8;">
        <img src="' . $img_path . '" 
             style="width:100%; height:100%; object-fit:cover;">
      </div>

      <div class="card-body" style="padding:18px 20px;">

        <!-- 标题 -->
        <h5 style="font-weight:600; margin-bottom:6px; color:#1a1a1a;">
          ' . htmlspecialchars($title) . '
        </h5>

        <!-- 描述（小号灰色） -->
        <p style="color:#6c757d; font-size:14px; min-height:42px; margin-bottom:10px;">
          ' . $desc_shortened . '
        </p>

        <!-- 分隔线（更轻更现代） -->
        <hr style="border-top:1px solid #eaeaea; margin:0 0 14px 0;">

        <!-- 底部：价格 + bid -->
        <div class="d-flex justify-content-between align-items-center">

          <!-- 蓝色价格 -->
          <span style="
            font-size:1.4rem; 
            font-weight:700; 
            color:#0d6efd;">
            £' . number_format($price, 2) . '
          </span>

          <!-- 右侧 bid 信息 -->
          <div style="text-align:right; font-size:14px; line-height:1.3;">

            <div style="color:#28a745;">
              <i class="fa-solid fa-gavel"></i>
              ' . $num_bids . $bid . '
            </div>

            <div style="color:#dc3545;">
              <i class="fa-regular fa-clock"></i>
              ' . $time_remaining . '
            </div>

          </div>
        </div>

      </div>
    </div>
  </a>
</div>
');




}