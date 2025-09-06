<?php
require_once __DIR__ . '/auth_guard.php'; // inicia sesión, PDO y validación de usuario

// Protect route & validate subject_id
$subjectId = isset($_GET['subject_id'])
    ? (int)$_GET['subject_id']
    : (isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : 0);

if ($subjectId <= 0) {
    header('Location: Topdashboard.php');
    exit;
}

// Fetch subject name
$stmt = $pdo->prepare("SELECT name FROM subjects WHERE id = :sid LIMIT 1");
$stmt->execute(['sid' => $subjectId]);
$subject = $stmt->fetch();
if (!$subject) {
    header('Location: Topdashboard.php');
    exit;
}
$subjectName = htmlspecialchars($subject['name']);

$userId = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmed'])) {
    $intro = trim($_POST['intro_message'] ?? '');
    if ($intro !== '') {
        // MySQL upsert syntax
        $upsert = $pdo->prepare("
            INSERT INTO mentor_subject (user_id, subject_id, intro)
            VALUES (:uid, :sid, :intro)
            ON DUPLICATE KEY UPDATE intro = VALUES(intro)
        ");
        $upsert->execute([
            'uid'   => $userId,
            'sid'   => $subjectId,
            'intro' => $intro
        ]);
    }

    // Redirect to mentor view
    header("Location: Topsubject_mentor.php?subject_id={$subjectId}");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>BiblioBros – <?= $subjectName ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="assets/css/style.css"/>
  
  <style>
    .intro-preview {
      background-color: #f8f9fa;
      border-left: 3px solid #ffc107;
      padding: 1rem;
      border-radius: 5px;
      margin-bottom: 1rem;
      max-height: 150px;
      overflow-y: auto;
      white-space: pre-wrap;
      word-wrap: break-word;
    }
    
    .char-counter {
      text-align: right;
      font-size: 0.875rem;
      color: #6c757d;
      margin-top: 0.25rem;
    }
    
    .char-counter.warning {
      color: #ffc107;
    }
    
    .char-counter.danger {
      color: #dc3545;
    }
    
    .modal-header {
      background: linear-gradient(135deg, #ffc107 0%, #ffdb4d 100%);
      color: white;
    }
    
    .modal-body i {
      color: #ffc107;
    }
  </style>
</head>
<body class="d-flex flex-column min-vh-100 mentor-theme">

  <div id="navbar-placeholder"></div>

  <main class="container d-flex flex-column align-items-center justify-content-center flex-fill py-5">
    <div class="form-card" style="max-width:600px;">
      <h2 class="section-title text-center mb-4"><?= $subjectName ?></h2>
      <h3 class="mb-3 text-center">Write Your Introductory Message</h3>
      
      <p class="text-muted text-center mb-4">
        <i class="fas fa-info-circle me-1"></i>
        This message will be displayed to all mentees looking for help in this subject.
        Make it clear and welcoming!
      </p>

      <form id="introForm" action="Topsubject_mentor_intro.php" method="post">
        <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
        <input type="hidden" name="confirmed" id="confirmed" value="0">
        
        <div class="mb-4">
          <label for="intro-message" class="form-label">
            Describe how you will help mentees in this subject:
          </label>
          <textarea 
            id="intro-message" 
            name="intro_message"
            class="form-control" 
            rows="5" 
            required
            placeholder="Example: I have 2 years of experience in this subject and can help with assignments, exam preparation, and understanding complex topics. I'm available most evenings and weekends..."
            maxlength="500"><?= htmlspecialchars($_POST['intro_message'] ?? '') ?></textarea>
          <div class="char-counter" id="charCounter">
            <span id="charCount">0</span> / 500 characters
          </div>
        </div>
        
        <div class="d-grid gap-2">
          <button type="button" id="submitBtn" class="btn btn-secondary btn-xl">
            <i class="fas fa-paper-plane me-2"></i>Submit Introduction
          </button>
          <a href="Topsubject.php?subject_id=<?= $subjectId ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Cancel
          </a>
        </div>
      </form>
    </div>
  </main>

  <!-- Confirmation Modal -->
  <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmModalLabel">
            <i class="fas fa-check-circle me-2"></i>Confirm Your Introduction
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-3">
            <i class="fas fa-eye me-2 text-warning"></i>
            <strong>Please review your introductory message:</strong>
          </p>
          <div class="intro-preview" id="introPreview"></div>
          <div class="alert alert-warning mb-0">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Note:</strong> This message will be visible to all mentees in <?= $subjectName ?>. 
            You can update it later from your mentor dashboard.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="fas fa-edit me-1"></i>Edit Message
          </button>
          <button type="button" class="btn btn-primary" id="confirmSubmit">
            <i class="fas fa-check me-1"></i>Confirm & Submit
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Success Feedback (optional) -->
  <div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header bg-success text-white">
        <i class="fas fa-check-circle me-2"></i>
        <strong class="me-auto">Success!</strong>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body">
        Your introduction has been submitted successfully!
      </div>
    </div>
  </div>

  <div id="modal-container"></div>
  <div id="footer-placeholder"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/main.js"></script>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('introForm');
      const textarea = document.getElementById('intro-message');
      const submitBtn = document.getElementById('submitBtn');
      const confirmSubmitBtn = document.getElementById('confirmSubmit');
      const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
      const charCounter = document.getElementById('charCounter');
      const charCount = document.getElementById('charCount');
      
      // Character counter
      function updateCharCount() {
        const length = textarea.value.length;
        charCount.textContent = length;
        
        // Change color based on length
        charCounter.classList.remove('warning', 'danger');
        if (length > 450) {
          charCounter.classList.add('danger');
        } else if (length > 400) {
          charCounter.classList.add('warning');
        }
      }
      
      textarea.addEventListener('input', updateCharCount);
      updateCharCount(); // Initial count
      
      // Show confirmation modal
      submitBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Validate textarea
        if (!textarea.value.trim()) {
          textarea.classList.add('is-invalid');
          textarea.focus();
          return;
        }
        
        textarea.classList.remove('is-invalid');
        
        // Update preview in modal
        document.getElementById('introPreview').textContent = textarea.value;
        
        // Show modal
        confirmModal.show();
      });
      
      // Confirm and submit
      confirmSubmitBtn.addEventListener('click', function() {
        document.getElementById('confirmed').value = '1';
        confirmModal.hide();
        
        // Show loading state
        confirmSubmitBtn.disabled = true;
        confirmSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
        
        // Submit form
        form.submit();
      });
      
      // Remove validation on input
      textarea.addEventListener('input', function() {
        this.classList.remove('is-invalid');
      });
    });
  </script>
</body>
</html>