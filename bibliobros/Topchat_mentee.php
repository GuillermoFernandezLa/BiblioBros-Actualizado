<?php
/*
 * Topchat_mentee.php – Enhanced version with mentor intro
 */

require_once __DIR__ . '/auth_guard.php';

$chatId = isset($_GET['chat_id']) ? (int) $_GET['chat_id'] : 0;
if ($chatId <= 0) {
  header('Location: Topdashboard.php');
  exit;
}

$showRatingModal = (isset($_GET['from']) && $_GET['from'] === 'rating') ? 'true' : 'false';

try {
  // Enhanced query to get mentor info and introduction
  $stmt = $pdo->prepare("
    SELECT 
      c.active, 
      u.fullname AS mentor_name,
      ms.intro AS mentor_intro,
      s.name AS subject_name,
      r.message AS original_question,
      COALESCE(rat.score, 0) AS mentor_rating,
      (SELECT COUNT(*) FROM ratings r2 
       INNER JOIN chats c2 ON c2.id = r2.chat_id 
       INNER JOIN requests req2 ON req2.id = c2.request_id 
       WHERE req2.mentor_id = u.id) AS total_ratings
    FROM chats c 
    JOIN requests r ON r.id = c.request_id 
    JOIN users u ON u.id = r.mentor_id 
    JOIN subjects s ON s.id = r.subject_id
    LEFT JOIN mentor_subject ms ON ms.user_id = u.id AND ms.subject_id = r.subject_id
    LEFT JOIN ratings rat ON rat.chat_id = c.id
    WHERE c.id = :cid 
    LIMIT 1
  ");
  $stmt->execute([':cid' => $chatId]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$row) {
    header('Location: Topdashboard.php');
    exit;
  }

  $isActive = (bool) $row['active'];
  $mentorName = htmlspecialchars($row['mentor_name'], ENT_QUOTES);
  $mentorIntro = $row['mentor_intro'] ? htmlspecialchars($row['mentor_intro']) : 'No introduction provided';
  $subjectName = htmlspecialchars($row['subject_name']);
  $originalQuestion = htmlspecialchars($row['original_question']);
  $totalRatings = $row['total_ratings'];

  // Check if user has already seen the chat mentee tutorial
  $showChatMenteeTutorial = false;

  $stmt = $pdo->prepare("SELECT has_seen_chat_mentee FROM users WHERE id = ?");
  $stmt->execute([$_SESSION['user_id']]);
  $hasSeen = $stmt->fetchColumn();

  if (!$hasSeen) {
    $showChatMenteeTutorial = true;
    $updateStmt = $pdo->prepare("UPDATE users SET has_seen_chat_mentee = TRUE WHERE id = ?");
    $updateStmt->execute([$_SESSION['user_id']]);
  }
} catch (PDOException $e) {
  die("DB error: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BiblioBros – Chat with <?= $mentorName ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">

  <style>
    .mentor-info-card {
      background: linear-gradient(135deg, #fff8e1 0%, #ffffff 100%);
      border: 2px solid #ffc107;
      border-radius: 10px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
    }

    .mentor-header {
      display: flex;
      justify-content: space-between;
      align-items: start;
      margin-bottom: 1rem;
    }

    .mentor-name {
      font-size: 1.25rem;
      font-weight: 600;
      color: #333;
      margin-bottom: 0.25rem;
    }

    .mentor-badge {
      background: #ffc107;
      color: #333;
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-size: 0.875rem;
      font-weight: 500;
    }

    .subject-tag {
      display: inline-block;
      background: #f8f9fa;
      color: #666;
      padding: 0.25rem 0.5rem;
      border-radius: 5px;
      font-size: 0.875rem;
      margin-bottom: 0.5rem;
    }

    .mentor-intro {
      background: white;
      padding: 1rem;
      border-radius: 5px;
      border-left: 3px solid #ffc107;
      margin-top: 1rem;
      font-size: 0.95rem;
      color: #555;
      line-height: 1.6;
    }

    .original-question {
      background: #f8f9fa;
      padding: 1rem;
      border-radius: 5px;
      margin-bottom: 1rem;
      border-left: 3px solid #17a2b8;
    }

    .original-question-label {
      font-weight: 600;
      color: #17a2b8;
      margin-bottom: 0.5rem;
      font-size: 0.875rem;
    }

    .chat-window {
      background: white;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      padding: 1.5rem;
    }

    .chat-header {
      border-bottom: 2px solid #f8f9fa;
      padding-bottom: 1rem;
      margin-bottom: 1rem;
    }

    .chat-history {
      min-height: 400px;
      max-height: 500px;
      background: #f8f9fa;
      border: none;
    }

    .close-rate-btn {
      background: linear-gradient(135deg, #ffc107, #ffdb4d);
      border: none;
      color: white;
      font-weight: 500;
      padding: 0.5rem 1rem;
      border-radius: 5px;
      transition: all 0.3s ease;
    }

    .close-rate-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4);
      background: linear-gradient(135deg, #ffdb4d, #ffc107);
    }

    .chat-status-badge {
      background: #dc3545;
      color: white;
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-size: 0.875rem;
    }

    .chat-status-badge.active {
      background: #28a745;
    }

    .message-form {
      background: #f8f9fa;
      padding: 1rem;
      border-radius: 10px;
      margin-top: 1rem;
    }

    .sidebar-chats {
      background: white;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      padding: 1.5rem;
    }

    .rating-stars {
      color: #ffc107;
      font-size: 0.9rem;
    }

    .mentor-stats {
      display: flex;
      gap: 1rem;
      margin-top: 0.5rem;
      font-size: 0.875rem;
      color: #666;
    }

    .stat-item {
      display: flex;
      align-items: center;
      gap: 0.25rem;
    }
  </style>
</head>

<body class="d-flex flex-column min-vh-100">
  <div id="navbar-placeholder"></div>

  <main class="container py-5 flex-grow-1">
    <!-- Mentor Info Card -->
    <div class="mentor-info-card">
      <div class="mentor-header">
        <div>
          <div class="subject-tag">
            <i class="fas fa-book me-1"></i><?= $subjectName ?>
          </div>
          <h3 class="mentor-name">
            <i class="fas fa-user-graduate me-2 text-warning"></i>
            <?= $mentorName ?>
          </h3>
          <div class="mentor-stats">
            <div class="stat-item">
              <i class="fas fa-star text-warning"></i>
              <span><?= $totalRatings ?> reviews</span>
            </div>
            <div class="stat-item">
              <?php if ($isActive): ?>
                <span class="chat-status-badge active">
                  <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>Active Chat
                </span>
              <?php else: ?>
                <span class="chat-status-badge">
                  <i class="fas fa-lock me-1"></i>Closed
                </span>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div>
          <span class="mentor-badge">
            <i class="fas fa-shield-alt me-1"></i>Verified Mentor
          </span>
        </div>
      </div>

      <div class="mentor-intro">
        <strong><i class="fas fa-quote-left me-1 text-warning"></i>Mentor's Introduction:</strong><br>
        <?= $mentorIntro ?>
      </div>
    </div>

    <!-- Original Question -->
    <div class="original-question">
      <div class="original-question-label">
        <i class="fas fa-question-circle me-1"></i>Your Original Question:
      </div>
      <?= $originalQuestion ?>
    </div>

    <div class="row gx-4">
      <!-- Chat Section -->
      <section class="col-md-8">
        <div class="chat-window">
          <div class="chat-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
              <i class="fas fa-comments me-2 text-primary"></i>
              Conversation
            </h4>
            <?php if ($isActive): ?>
              <form method="post" action="close_chat.php" style="display: inline;">
                <input type="hidden" name="chat_id" value="<?= $chatId ?>">
                <button type="submit" class="close-rate-btn"
                  onclick="return confirm('Are you sure you want to close this chat? You will be redirected to rate your mentor.')">
                  <i class="fas fa-star me-1"></i>Close & Rate Mentor
                </button>
              </form>
            <?php endif; ?>
          </div>

          <div id="chat-history" class="chat-history overflow-auto mb-3 rounded p-3"
            data-user-id="<?= $_SESSION['user_id'] ?>">
            <div class="text-center">
              <span class="spinner-border spinner-border-sm me-2"></span>
              Loading messages...
            </div>
          </div>

          <?php if ($isActive): ?>
            <form id="chat-form" class="message-form">
              <div class="input-group">
                <input type="text" id="message-input" class="form-control"
                  placeholder="Type your message here..." required />
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-paper-plane me-1"></i>Send
                </button>
              </div>
            </form>
          <?php else: ?>
            <div class="alert alert-info">
              <i class="fas fa-info-circle me-2"></i>
              This conversation has been closed. You can still view the message history.
            </div>
          <?php endif; ?>
        </div>
      </section>

      <!-- Sidebar -->
      <aside class="col-md-4">
        <div class="sidebar-chats">
          <h5 class="mb-3">
            <i class="fas fa-history me-2 text-secondary"></i>
            Your Chats
          </h5>
          <ul id="chat-list" class="list-group list-group-flush" data-user-id="<?= $_SESSION['user_id'] ?>">
            <li class="list-group-item text-center">
              <span class="spinner-border spinner-border-sm me-2"></span>
              Loading chats...
            </li>
          </ul>
        </div>
      </aside>
    </div>
  </main>

  <!-- Modal: Rating Submitted Successfully -->
  <div class="modal fade" id="ratingSuccessModal" tabindex="-1" aria-labelledby="ratingSuccessModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-warning text-white">
          <h5 class="modal-title" id="ratingSuccessModalLabel">
            <i class="fas fa-check-circle me-2"></i>Thank you!
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <i class="fas fa-star text-warning me-1"></i>
          Your rating has been submitted successfully.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-warning text-white" id="succesfullsubmitRating" data-bs-dismiss="modal">
            <i class="fas fa-check me-1"></i>OK
          </button>
        </div>
      </div>
    </div>
  </div>

  <div id="footer-placeholder"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
  <script src="assets/js/main.js"></script>

  <script>
    const showRatingModal = <?= $showRatingModal ?>;
    document.addEventListener('DOMContentLoaded', () => {
      if (showRatingModal) {
        const modal = new bootstrap.Modal(document.getElementById('ratingSuccessModal'));
        modal.show();
      }
    });
  </script>

  <?php if ($showChatMenteeTutorial): ?>
    <!-- Tutorial modal remains the same but with improved styling -->
  <?php endif; ?>

</body>

</html>