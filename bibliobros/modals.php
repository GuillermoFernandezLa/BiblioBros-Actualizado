<?php
/*
 * modals.php
 *
 * This file defines shared UI modals used across the site, such as logout confirmation,
 * profile update confirmation, cancel actions, and rating confirmations.
 * No session logic or redirects are handled here.
 */
// UI components only; no session_start or redirects here.
?>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header">
        <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to log out?
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, stay</button>
        <button type="button" id="confirmLogout" class="btn btn-danger">
          Yes, log out
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Profile Save Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header">
        <h5 class="modal-title" id="successModalLabel">Profile Saved</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Your profile has been successfully updated.
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<!-- Cancel Edit Confirmation -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header">
        <h5 class="modal-title" id="cancelModalLabel">Cancel Changes</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to cancel? Unsaved changes will be lost.
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, stay</button>
        <button type="button" id="confirmCancel" class="btn btn-danger" data-bs-dismiss="modal">
          Yes, go back
        </button>
      </div>
    </div>
  </div>
</div>


<!-- Confirm Submit Rating Modal -->
<div class="modal fade" id="submitRatingModal" tabindex="-1" aria-labelledby="submitRatingLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header">
        <h5 class="modal-title" id="submitRatingLabel">Submit Rating?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to submit your rating?
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, go back</button>
        <button type="button" id="confirmSubmitRating" class="btn btn-warning">Yes, submit</button>
      </div>
    </div>
  </div>
</div>

<!-- Confirm Cancel Rating Modal -->
<div class="modal fade" id="cancelRatingModal" tabindex="-1" aria-labelledby="cancelRatingLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header">
        <h5 class="modal-title" id="cancelRatingLabel">Cancel Rating?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to cancel? Your rating will not be saved.
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, stay</button>
        <button type="button" id="confirmCancelRating" class="btn btn-danger">Yes, cancel</button>
      </div>
    </div>
  </div>
</div>
