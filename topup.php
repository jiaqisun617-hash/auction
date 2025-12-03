<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "database.php";

// Where to go after successful top-up
$return_to = $_GET['return'] ?? 'index.php';
?>

<?php include "header.php"; ?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">

      <!-- Card wrapper for top-up form -->
      <div class="card shadow-sm border-0">
        <div class="card-body p-4">

          <h3 class="mb-3">Add Balance</h3>
          <p class="text-muted mb-4" style="font-size: 0.9rem;">
            This is a demo payment form. Card details are <strong>not</strong> processed
            and will not be stored.
          </p>

          <form action="process_topup.php" method="POST">
            <!-- redirect back to the page we came from -->
            <input type="hidden" name="redirect_url"
                   value="<?php echo htmlspecialchars($return_to); ?>">

            <!-- Top-up amount -->
            <div class="mb-3">
              <label for="amount" class="form-label">Top-up Amount (£)</label>
              <input
                type="number"
                step="0.01"
                min="1"
                class="form-control"
                id="amount"
                name="amount"
                placeholder="Enter amount, e.g. 50.00"
                required
              >
            </div>

            <hr class="my-4">

            <!-- Fake payment card section (front-end only) -->
            <div class="d-flex align-items-center mb-2">
              <h5 class="mb-0">Payment details</h5>
              <span class="ms-2 text-muted" style="font-size: 0.85rem;">
                <i class="fa fa-lock"></i> Secure demo checkout
              </span>
            </div>

            <!-- Card type (just for UI) -->
            <div class="mb-3">
              <label class="form-label">Card type</label>
              <select class="form-select" name="card_type">
                <option value="visa">Visa</option>
                <option value="mastercard">Mastercard</option>
                <option value="amex">American Express</option>
                <option value="other">Other</option>
              </select>
            </div>

            <!-- Card number -->
            <div class="mb-3">
              <label for="card_number" class="form-label">Card number</label>
              <input
                type="text"
                class="form-control"
                id="card_number"
                name="card_number"
                placeholder="1234 5678 9012 3456"
                maxlength="19"
              >
            </div>

            <div class="row">
              <!-- Expiry date -->
              <div class="col-6 mb-3">
                <label for="expiry" class="form-label">Expiry (MM/YY)</label>
                <input
                  type="text"
                  class="form-control"
                  id="expiry"
                  name="expiry"
                  placeholder="08/27"
                  maxlength="5"
                >
              </div>

              <!-- CVV -->
              <div class="col-6 mb-3">
                <label for="cvv" class="form-label">
                  CVV
                  <span class="text-muted" style="font-size:0.8rem;">(back of card)</span>
                </label>
                <input
                  type="password"
                  class="form-control"
                  id="cvv"
                  name="cvv"
                  placeholder="123"
                  maxlength="4"
                >
              </div>
            </div>

            <!-- Name on card -->
            <div class="mb-4">
              <label for="card_name" class="form-label">Name on card</label>
              <input
                type="text"
                class="form-control"
                id="card_name"
                name="card_name"
                placeholder="Full name"
              >
            </div>

            <!-- Submit button -->
            <button type="submit" class="btn btn-primary w-100">
              Add Funds
            </button>

          </form>

        </div>
      </div>

      <!-- Small helper text under the card -->
      <p class="text-muted mt-3" style="font-size:0.85rem;">
        For coursework purposes only: any card number will be accepted and the
        balance will be updated instantly.
      </p>

    </div>
  </div>
</div>

<?php include "footer.php"; ?>
