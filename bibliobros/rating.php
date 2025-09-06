<?php
/*
 * rating.php
 *
 * Allows mentees to rate their mentors after a chat session.
 */

// Fix the path - auth_guard.php should handle the config include
session_start();
require_once __DIR__ . '/config.php';

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    // Debug: ver si hay pending_rating
    if (isset($_SESSION['pending_rating'])) {
        $pending = $_SESSION['pending_rating'];
        error_log("Rating.php: Session lost but pending_rating exists: " . $pending);
    }
    header('Location: Toplogin.php');
    exit;
}

$mentee_id = (int) $_SESSION['user_id'];

$chatId = ($_SERVER['REQUEST_METHOD'] === 'POST')
  ? (int) ($_POST['chat_id'] ?? 0)
  : (int) ($_GET['chat_id'] ?? 0);

// Si no hay chat_id pero hay pending_rating, usarlo
if ($chatId <= 0 && isset($_SESSION['pending_rating'])) {
    $chatId = (int) $_SESSION['pending_rating'];
    unset($_SESSION['pending_rating']); // Limpiar después de usar
}

if ($chatId <= 0) {
  header('Location: Topdashboard.php');
  exit;
}

// Si se ha enviado el formulario de valoración
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $score = isset($_POST['score']) ? (int) $_POST['score'] : 0;
  $comment = trim($_POST['comment'] ?? '');

  if ($score < 1 || $score > 5) {
    $error = "Please select a rating between 1 and 5 stars.";
  } else {
    // Verificar que el usuario es el mentee y obtener subject_id
    $chk = $pdo->prepare("
      SELECT r.subject_id
        FROM chats c
        JOIN requests r ON r.id = c.request_id
       WHERE c.id = :cid AND r.mentee_id = :mid
       LIMIT 1
    ");
    $chk->execute([
      ':cid' => $chatId,
      ':mid' => $mentee_id
    ]);
    $row = $chk->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
      $error = "You are not authorized to rate this conversation.";
    } else {
      $subjectId = (int) $row['subject_id'];

      // Guardar valoración
      $ins = $pdo->prepare("
        INSERT INTO ratings (chat_id, score, comment)
        VALUES (:cid, :score, :comment)
      ");
      $ins->execute([
        ':cid' => $chatId,
        ':score' => $score,
        ':comment' => $comment
      ]);

      // Actualizar estado de la solicitud
      $updReq = $pdo->prepare("
        UPDATE requests
           SET status = 'closed'
         WHERE id = (SELECT request_id FROM chats WHERE id = :cid)
      ");
      $updReq->execute([':cid' => $chatId]);

      // Marcar chat como inactivo
      $updChat = $pdo->prepare("
        UPDATE chats SET active = FALSE WHERE id = :cid
      ");
      $updChat->execute([':cid' => $chatId]);

      // Set success flag in session
      $_SESSION['rating_success'] = true;
      
      header("Location: Topchat_mentee.php?chat_id={$chatId}&from=rating");
      exit;
    }
  }
}

// GET: obtener datos del chat
$stmt = $pdo->prepare("
  SELECT 
    u.fullname AS mentor_name,
    s.name     AS subject_name,
    s.id       AS subject_id,
    r.message  AS original_question
  FROM chats c
  JOIN requests r  ON r.id = c.request_id
  JOIN users u     ON u.id = r.mentor_id
  JOIN subjects s  ON s.id = r.subject_id
  WHERE c.id = :cid
  LIMIT 1
");
$stmt->execute([':cid' => $chatId]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
  header('Location: Topdashboard.php');
  exit;
}

$mentorName  = htmlspecialchars($data['mentor_name'], ENT_QUOTES);
$subjectName = htmlspecialchars($data['subject_name'], ENT_QUOTES);
$subjectId   = (int) $data['subject_id'];
$originalQuestion = htmlspecialchars($data['original_question'], ENT_QUOTES);

// Check if already rated
$checkRating = $pdo->prepare("SELECT id FROM ratings WHERE chat_id = :cid LIMIT 1");
$checkRating->execute([':cid' => $chatId]);
$alreadyRated = $checkRating->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>BiblioBros – Rate Your Mentor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />
  
  <style>
    .rating-container {
      max-width: 600px;
      margin: 0 auto;
    }
    
    .rating-card {
      background: white;
      border-radius: 15px;
      padding: 2rem;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      border: 2px solid #f8f9fa;
    }
    
    .rating-header {
      background: linear-gradient(135deg, #ffc107 0%, #ffdb4d 100%);
      color: white;
      padding: 2rem;
      border-radius: 15px 15px 0 0;
      margin: -2rem -2rem 2rem -2rem;
      text-align: center;
    }
    
    .rating-header h2 {
      margin: 0;
      font-weight: 600;
    }
    
    .rating-header .subtitle {
      margin-top: 0.5rem;
      opacity: 0.9;
    }
    
    .mentor-info {
      background: #f8f9fa;
      border-radius: 10px;
      padding: 1.5rem;
      margin-bottom: 2rem;
      border-left: 4px solid #ffc107;
    }
    
    .mentor-info .info-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 0.75rem;
    }
    
    .mentor-info .info-row:last-child {
      margin-bottom: 0;
    }
    
    .mentor-info .label {
      color: #6c757d;
      font-weight: 500;
    }
    
    .mentor-info .value {
      color: #333;
      font-weight: 600;
    }
    
    .question-box {
      background: #fff8e1;
      border-radius: 10px;
      padding: 1rem;
      margin-bottom: 2rem;
      border: 1px solid #ffc107;
    }
    
    .question-box .question-label {
      color: #856404;
      font-size: 0.875rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
    }
    
    .question-box .question-text {
      color: #333;
      font-style: italic;
    }
    
    /* Star Rating Styles */
    .star-rating-container {
      text-align: center;
      margin: 2rem 0;
      padding: 1.5rem;
      background: linear-gradient(135deg, #fff8e1 0%, #ffffff 100%);
      border-radius: 10px;
    }
    
    .star-rating-label {
      font-weight: 600;
      color: #333;
      margin-bottom: 1rem;
      display: block;
    }
    
    .star-rating {
      display: flex;
      justify-content: center;
      flex-direction: row-reverse;
      gap: 0.5rem;
    }
    
    .star-rating input[type="radio"] {
      display: none;
    }
    
    .star-rating label {
      font-size: 3rem;
      color: #ddd;
      cursor: pointer;
      transition: all 0.3s ease;
      display: block;
      line-height: 1;
    }
    
    .star-rating label:hover,
    .star-rating label:hover ~ label,
    .star-rating input[type="radio"]:checked ~ label {
      color: #ffc107;
      transform: scale(1.1);
    }
    
    .star-rating label:hover {
      animation: pulse 0.5s ease;
    }
    
    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.2); }
      100% { transform: scale(1.1); }
    }
    
    .rating-helper-text {
      margin-top: 1rem;
      font-size: 0.9rem;
      color: #6c757d;
      text-align: center;
      transition: all 0.3s ease;
    }
    
    .rating-helper-text.selected {
      color: #ffc107;
      font-weight: 500;
    }
    
    /* Comment Section */
    .comment-section {
      margin-top: 2rem;
    }
    
    .comment-section label {
      font-weight: 600;
      color: #333;
      margin-bottom: 0.5rem;
    }
    
    .comment-section textarea {
      border: 2px solid #e9ecef;
      border-radius: 10px;
      padding: 0.75rem;
      font-size: 0.95rem;
      transition: all 0.3s ease;
    }
    
    .comment-section textarea:focus {
      border-color: #ffc107;
      box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.15);
      outline: none;
    }
    
    .character-count {
      text-align: right;
      font-size: 0.875rem;
      color: #6c757d;
      margin-top: 0.25rem;
    }
    
    /* Action Buttons */
    .action-buttons {
      display: flex;
      gap: 1rem;
      margin-top: 2rem;
    }
    
    .action-buttons .btn {
      flex: 1;
      padding: 0.75rem;
      font-weight: 500;
      border-radius: 10px;
      transition: all 0.3s ease;
    }
    
    .btn-submit {
      background: #ffc107;
      border: none;
      color: #333;
    }
    
    .btn-submit:hover:not(:disabled) {
      background: #ffdb4d;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
    }
    
    .btn-submit:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
    
    .btn-cancel {
      background: white;
      border: 2px solid #dee2e6;
      color: #6c757d;
    }
    
    .btn-cancel:hover {
      background: #f8f9fa;
      border-color: #adb5bd;
    }
    
    /* Already Rated State */
    .already-rated-card {
      text-align: center;
      padding: 3rem;
    }
    
    .already-rated-icon {
      font-size: 4rem;
      color: #28a745;
      margin-bottom: 1rem;
    }
    
    .already-rated-message {
      font-size: 1.25rem;
      color: #333;
      margin-bottom: 0.5rem;
    }
    
    .already-rated-submessage {
      color: #6c757d;
      margin-bottom: 2rem;
    }
    
    /* Error Alert */
    .alert-custom {
      border-radius: 10px;
      border: none;
      padding: 1rem 1.25rem;
      margin-bottom: 1.5rem;
    }
    
    .alert-danger-custom {
      background: #f8d7da;
      color: #721c24;
      border-left: 4px solid #dc3545;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      .rating-card {
        padding: 1.5rem;
      }
      
      .rating-header {
        padding: 1.5rem;
        margin: -1.5rem -1.5rem 1.5rem -1.5rem;
      }
      
      .star-rating label {
        font-size: 2.5rem;
      }
      
      .action-buttons {
        flex-direction: column;
      }
    }
  </style>
</head>
<body class="d-flex flex-column min-vh-100">
  <div id="navbar-placeholder"></div>

  <main class="container py-5 flex-grow-1">
    <div class="rating-container">
      <?php if ($alreadyRated): ?>
        <!-- Already Rated State -->
        <div class="rating-card">
          <div class="already-rated-card">
            <i class="fas fa-check-circle already-rated-icon"></i>
            <h3 class="already-rated-message">You've Already Rated This Session</h3>
            <p class="already-rated-submessage">Thank you for your feedback!</p>
            <div class="action-buttons">
              <a href="Topsubject_mentee.php?subject_id=<?= $subjectId ?>" class="btn btn-submit">
                <i class="fas fa-arrow-left me-2"></i>Back to Subject
              </a>
              <a href="Topchat_mentee.php?chat_id=<?= $chatId ?>" class="btn btn-cancel">
                <i class="fas fa-comments me-2"></i>View Chat
              </a>
            </div>
          </div>
        </div>
      <?php else: ?>
        <!-- Rating Form -->
        <div class="rating-card">
          <div class="rating-header">
            <i class="fas fa-star mb-3" style="font-size: 2.5rem;"></i>
            <h2>Rate Your Mentor</h2>
            <p class="subtitle mb-0">Your feedback helps improve the mentoring experience</p>
          </div>

          <?php if (!empty($error)): ?>
            <div class="alert alert-custom alert-danger-custom">
              <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>

          <!-- Mentor Information -->
          <div class="mentor-info">
            <div class="info-row">
              <span class="label"><i class="fas fa-book me-2"></i>Subject:</span>
              <span class="value"><?= $subjectName ?></span>
            </div>
            <div class="info-row">
              <span class="label"><i class="fas fa-user-graduate me-2"></i>Mentor:</span>
              <span class="value"><?= $mentorName ?></span>
            </div>
          </div>

          <!-- Original Question -->
          <?php if ($originalQuestion): ?>
            <div class="question-box">
              <div class="question-label">
                <i class="fas fa-question-circle me-1"></i>Your Original Question:
              </div>
              <div class="question-text">"<?= $originalQuestion ?>"</div>
            </div>
          <?php endif; ?>

          <form id="rating-form" method="post" action="rating.php">
            <input type="hidden" name="chat_id" value="<?= $chatId ?>" />

            <!-- Star Rating -->
            <div class="star-rating-container">
              <label class="star-rating-label">How would you rate this mentoring session?</label>
              <div class="star-rating">
                <?php 
                $ratingLabels = [
                  1 => "Poor",
                  2 => "Fair", 
                  3 => "Good",
                  4 => "Very Good",
                  5 => "Excellent"
                ];
                for ($i = 5; $i >= 1; $i--): 
                ?>
                  <input type="radio" id="score<?= $i ?>" name="score" value="<?= $i ?>" 
                         <?= (isset($score) && $score == $i) ? 'checked' : '' ?> required>
                  <label for="score<?= $i ?>" title="<?= $ratingLabels[$i] ?> - <?= $i ?> star<?= $i > 1 ? 's' : '' ?>">
                    <i class="fas fa-star"></i>
                  </label>
                <?php endfor; ?>
              </div>
              <div class="rating-helper-text" id="ratingText">
                Click on the stars to rate
              </div>
            </div>

            <!-- Comment Section -->
            <div class="comment-section">
              <label for="comment">
                <i class="fas fa-comment-alt me-2"></i>Additional Comments (Optional)
              </label>
              <textarea 
                id="comment" 
                name="comment" 
                class="form-control" 
                rows="4"
                maxlength="500"
                placeholder="Share your experience with this mentor. What went well? What could be improved?"><?= htmlspecialchars($comment ?? '') ?></textarea>
              <div class="character-count">
                <span id="charCount">0</span> / 500 characters
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
              <button type="submit" class="btn btn-submit" id="submitBtn" disabled>
                <i class="fas fa-paper-plane me-2"></i>Submit Rating
              </button>
              <a href="Topsubject_mentee.php?subject_id=<?= $subjectId ?>" class="btn btn-cancel">
                <i class="fas fa-times me-2"></i>Cancel
              </a>
            </div>
          </form>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <div id="modal-container"></div>
  <div id="footer-placeholder"></div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/main.js"></script>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('rating-form');
      const submitBtn = document.getElementById('submitBtn');
      const ratingInputs = document.querySelectorAll('input[name="score"]');
      const ratingText = document.getElementById('ratingText');
      const commentTextarea = document.getElementById('comment');
      const charCount = document.getElementById('charCount');
      
      const ratingMessages = {
        1: "Poor - The mentor was not helpful",
        2: "Fair - The mentor provided some help",
        3: "Good - The mentor was helpful",
        4: "Very Good - The mentor was very helpful",
        5: "Excellent - The mentor exceeded expectations!"
      };
      
      // Enable submit button when rating is selected
      ratingInputs.forEach(input => {
        input.addEventListener('change', function() {
          submitBtn.disabled = false;
          ratingText.textContent = ratingMessages[this.value];
          ratingText.classList.add('selected');
        });
      });
      
      // Character counter
      function updateCharCount() {
        const length = commentTextarea.value.length;
        charCount.textContent = length;
        
        if (length > 450) {
          charCount.style.color = '#dc3545';
        } else if (length > 400) {
          charCount.style.color = '#ffc107';
        } else {
          charCount.style.color = '#6c757d';
        }
      }
      
      commentTextarea.addEventListener('input', updateCharCount);
      updateCharCount();
      
      // Form submission animation
      form.addEventListener('submit', function(e) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
      });
    });
  </script>
</body>
</html>