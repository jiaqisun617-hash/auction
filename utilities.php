<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function hasRole($role) {
    return isset($_SESSION['roles']) && in_array($role, $_SESSION['roles']);
}

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



function print_listing_li($item_id, $title, $desc, $price, $num_bids, $end_time, $img_path)
{
    // Shorten description
    $desc_shortened = (strlen($desc) > 250) ? substr($desc, 0, 250) . '...' : $desc;

    // Bid label
    $bid_label = ($num_bids == 1) ? " bid" : " bids";

    // Time remaining
    $now = new DateTime();
    if ($now > $end_time) {
        $time_remaining = "Auction ended";
    } else {
        $interval = $now->diff($end_time);
        $time_remaining = display_time_remaining($interval);
    }

    // Auction status
    if ($now > $end_time) {
        $status = "ENDED";
    } else {
        $interval = $now->diff($end_time);
        if ($interval->days == 0 && $interval->h < 24) {
            $status = "ENDING SOON";
        } elseif ($num_bids >= 5) {
            $status = "HOT";
        } else {
            $status = "LIVE";
        }
    }

    // Status colors
    $label_color = [
        "HOT" => "black",
        "LIVE" => "#357edd",
        "ENDING SOON" => "#d9534f",
        "ENDED" => "grey"
    ];

    $label_bg = [
        "HOT" => "white",
        "LIVE" => "white",
        "ENDING SOON" => "white",
        "ENDED" => "#f2f2f2"
    ];

    // FINAL UI echo
    echo '
    <div class="col-lg-3 col-md-4 col-sm-6 mb-5">
      <a href="listing.php?item_id=' . $item_id . '" style="text-decoration:none; color:inherit;">
      
      <div class="lux-card"
           style="
             background:#f5f5f5;
             border-radius:12px;
             padding:18px;
             transition:all .25s ease;
             position:relative;
           "
           onmouseover="this.style.transform=\'translateY(-6px)\'; this.style.boxShadow=\'0 14px 28px rgba(0,0,0,0.08)\';"
           onmouseout="this.style.transform=\'none\'; this.style.boxShadow=\'none\';"
      >

        <!-- Status Tag -->
    <div style="
  position:absolute;
  top:12px;
  left:12px;
  padding:3px 10px;
  font-size:11px;
  font-weight:600;
  letter-spacing:1.2px;
  text-transform:uppercase;
  background:rgba(255,255,255,0.7);
  color:' . $label_color[$status] . ';
  border:1px solid rgba(0, 0, 0, 0.15);
  border-radius:6px;
  backdrop-filter:blur(6px);
">

          ' . $status . '
        </div>

        <!-- Image -->
        <div style="
            background:white;
            border-radius:8px;
            padding:26px;
            height:220px;
            display:flex;
            justify-content:center;
            align-items:center;
            overflow:hidden;
        ">
          <img src="' . $img_path . '"
               style="max-height:100%; max-width:100%; object-fit:contain;">
        </div>

        <!-- Title -->
        <h6 style="margin-top:14px; font-weight:600; font-size:15px; color:#111;">
          ' . htmlspecialchars($title) . '
        </h6>

        <!-- Auction Info -->
        <div style="margin-top:8px;">

          <!-- Price -->
          <div style="font-size:16px; font-weight:700; color:#111;">
            £' . number_format($price, 2) . '
          </div>

          <!-- Bids + Time -->
          <div style="margin-top:4px; color:#555; font-size:14px;">
            ' . $num_bids . $bid_label . ' · ' . $time_remaining . '
          </div>

        </div>

      </div>
      </a>
    </div>
    ';
}
