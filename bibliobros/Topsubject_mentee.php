<?php
/*
 * Topsubject_mentee.php - Enhanced with FAQs and role switcher
 */
require_once __DIR__ . '/auth_guard.php'; 
require_once 'role_switcher.php';

// Protect route
if (!isset($_SESSION['user_id'])) {
  header('Location: Toplogin.php');
  exit;
}

// Get & validate subject_id
$subjectId = isset($_GET['subject_id']) ? (int) $_GET['subject_id'] : 0;
if ($subjectId <= 0) {
  header('Location: Topdashboard.php');
  exit;
}

// Fetch subject name
$stmt = $pdo->prepare("
    SELECT name
      FROM subjects
     WHERE id = :sid
     LIMIT 1
");
$stmt->execute(['sid' => $subjectId]);
$subject = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$subject) {
  header('Location: Topdashboard.php');
  exit;
}
$subjectName = htmlspecialchars($subject['name']);

// ENHANCED FAQs QUERY - Gets questions and their best-rated answers
$stmtFAQ = $pdo->prepare("
    SELECT 
        r.message as question,
        m.content as answer,
        u.fullname as answered_by,
        rat.score as rating,
        r.created_at
    FROM requests r
    INNER JOIN chats c ON c.request_id = r.id
    INNER JOIN (
        -- Get first helpful message from mentor in each chat
        SELECT 
            m1.chat_id,
            m1.content,
            m1.sender_id
        FROM messages m1
        WHERE m1.sender_id IN (
            SELECT r2.mentor_id 
            FROM requests r2 
            WHERE r2.id = (SELECT request_id FROM chats WHERE id = m1.chat_id)
        )
        AND m1.content != ''
        GROUP BY m1.chat_id
        HAVING MIN(m1.timestamp)
    ) m ON m.chat_id = c.id
    INNER JOIN users u ON u.id = r.mentor_id
    LEFT JOIN ratings rat ON rat.chat_id = c.id
    WHERE r.subject_id = :sid
    AND c.active = 0  -- Only closed chats
    AND (rat.score >= 4 OR rat.score IS NULL)  -- Only good ratings or unrated
    GROUP BY r.message
    ORDER BY 
        CASE WHEN rat.score IS NOT NULL THEN rat.score ELSE 3 END DESC,
        r.created_at DESC
    LIMIT 5
");
$stmtFAQ->execute(['sid' => $subjectId]);
$faqs = $stmtFAQ->fetchAll();

// Alternative: Check if there's a dedicated FAQs table
$hasStaticFAQs = false;
try {
    $stmtStatic = $pdo->prepare("
        SELECT question, answer 
        FROM subject_faqs 
        WHERE subject_id = :sid 
        AND is_active = 1
        ORDER BY order_position, id
    ");
    $stmtStatic->execute(['sid' => $subjectId]);
    $staticFAQs = $stmtStatic->fetchAll();
    if (!empty($staticFAQs)) {
        $faqs = $staticFAQs;
        $hasStaticFAQs = true;
    }
} catch (PDOException $e) {
    // Table doesn't exist, use dynamic FAQs
}

// Check tutorial status
$showSubjectMenteeTutorial = false;
$stmt = $pdo->prepare("SELECT has_seen_subject_mentee FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$hasSeen = $stmt->fetchColumn();

if (!$hasSeen) {
  $showSubjectMenteeTutorial = true;
  $updateStmt = $pdo->prepare("UPDATE users SET has_seen_subject_mentee = TRUE WHERE id = ?");
  $updateStmt->execute([$_SESSION['user_id']]);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>BiblioBros – <?= $subjectName ?> (Mentee)</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />
  
  <style>
    .faq-section {
      background-color: #f8f9fa;
      border-radius: 10px;
      padding: 1.5rem;
      margin-bottom: 2rem;
    }
    
    .faq-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
    }
    
    .faq-title {
      color: #333;
      font-size: 1.25rem;
      font-weight: 600;
      margin: 0;
    }
    
    .faq-item {
      background: white;
      border-radius: 8px;
      margin-bottom: 1rem;
      border: 1px solid #e9ecef;
      overflow: hidden;
      transition: box-shadow 0.3s;
    }
    
    .faq-item:hover {
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .faq-question-header {
      padding: 1rem;
      background: linear-gradient(135deg, #fff8e1 0%, #ffffff 100%);
      cursor: pointer;
      border-left: 3px solid #ffc107;
      position: relative;
    }
    
    .faq-question-header:hover {
      background: linear-gradient(135deg, #fff3cd 0%, #ffffff 100%);
    }
    
    .faq-question {
      color: #495057;
      font-weight: 500;
      margin-bottom: 0;
      padding-right: 2rem;
    }
    
    .faq-question::before {
      content: "Q: ";
      color: #ffc107;
      font-weight: bold;
    }
    
    .faq-toggle-icon {
      position: absolute;
      right: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: #6c757d;
      transition: transform 0.3s;
    }
    
    .faq-item.expanded .faq-toggle-icon {
      transform: translateY(-50%) rotate(180deg);
    }
    
    .faq-answer-wrapper {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease-out;
    }
    
    .faq-item.expanded .faq-answer-wrapper {
      max-height: 500px;
    }
    
    .faq-answer {
      padding: 1rem;
      background: #ffffff;
      border-top: 1px solid #e9ecef;
    }
    
    .faq-answer-text {
      color: #666;
      margin-bottom: 0.5rem;
      line-height: 1.6;
    }
    
    .faq-answer-text::before {
      content: "A: ";
      color: #28a745;
      font-weight: bold;
    }
    
    .faq-meta {
      font-size: 0.85rem;
      color: #6c757d;
      margin-top: 0.5rem;
      padding-top: 0.5rem;
      border-top: 1px dashed #e9ecef;
    }
    
    .faq-meta i {
      margin-right: 0.25rem;
    }
    
    .no-faqs {
      text-align: center;
      color: #6c757d;
      padding: 2rem;
      background: white;
      border-radius: 8px;
    }
    
    .publish-btn-container {
      text-align: center;
      margin-bottom: 2rem;
      padding: 2rem;
      background: linear-gradient(135deg, #fff8e1 0%, #fffdf7 100%);
      border-radius: 10px;
      border: 2px dashed #ffc107;
    }
    
    .rating-stars {
      color: #ffc107;
    }
    
    .faq-badge {
      display: inline-block;
      padding: 0.25rem 0.5rem;
      background: #28a745;
      color: white;
      border-radius: 4px;
      font-size: 0.75rem;
      margin-left: 0.5rem;
    }
  </style>
</head>

<body class="d-flex flex-column min-vh-100">

  <!-- Navbar -->
  <div id="navbar-placeholder"></div>

  <!-- MAIN CONTENT -->
  <main class="container py-5 flex-grow-1">

    <!-- Subject Title -->
    <section class="text-center mb-5">
      <h2 class="section-title">
        <?= $subjectName ?>
        <?= renderCurrentRoleBadge('mentee') ?>
      </h2>
    </section>

    <?php 
    // Mostrar prompt para añadir rol de mentor si no lo tiene
    if (canAddOppositeRole($pdo, $_SESSION['user_id'], $subjectId, 'mentee')) {
        echo renderAddRolePrompt($subjectId, 'mentee');
    }
    ?>

    <!-- Publish Question Button -->
    <section class="publish-btn-container">
      <p><i class="fas fa-question-circle me-2"></i>Need help with this subject?</p>
      <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#requestModal">
        <i class="fas fa-plus-circle me-2"></i>Publish a Question
      </button>
    </section>

    <!-- FAQs Section -->
    <section class="faq-section">
      <div class="faq-header">
        <h3 class="faq-title">
          <i class="fas fa-lightbulb me-2 text-warning"></i>
          Frequently Asked Questions
          <?php if ($hasStaticFAQs): ?>
            <span class="faq-badge">Official</span>
          <?php else: ?>
            <span class="faq-badge" style="background: #6c757d;">Community</span>
          <?php endif; ?>
        </h3>
      </div>
      
      <div id="faq-list">
        <?php if (!empty($faqs)): ?>
          <?php foreach ($faqs as $index => $faq): ?>
            <div class="faq-item" id="faq-<?= $index ?>">
              <div class="faq-question-header" onclick="toggleFAQ(<?= $index ?>)">
                <div class="faq-question">
                  <?= htmlspecialchars($faq['question']) ?>
                </div>
                <i class="fas fa-chevron-down faq-toggle-icon"></i>
              </div>
              <div class="faq-answer-wrapper">
                <div class="faq-answer">
                  <div class="faq-answer-text">
                    <?php 
                    // Limit answer to 200 characters for preview
                    $answer = htmlspecialchars($faq['answer']);
                    if (strlen($answer) > 200) {
                        $answer = substr($answer, 0, 200) . '...';
                    }
                    echo $answer;
                    ?>
                  </div>
                  <?php if (!$hasStaticFAQs && isset($faq['answered_by'])): ?>
                    <div class="faq-meta">
                      <span>
                        <i class="fas fa-user-graduate"></i>
                        Answered by: <?= htmlspecialchars($faq['answered_by']) ?>
                      </span>
                      <?php if (isset($faq['rating']) && $faq['rating']): ?>
                        <span class="ms-3 rating-stars">
                          <?php for($i = 0; $i < round($faq['rating']); $i++): ?>
                            <i class="fas fa-star"></i>
                          <?php endfor; ?>
                          <?php for($i = round($faq['rating']); $i < 5; $i++): ?>
                            <i class="far fa-star"></i>
                          <?php endfor; ?>
                        </span>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
          <div class="text-center mt-3">
            <small class="text-muted">
              <i class="fas fa-info-circle me-1"></i>
              <?php if ($hasStaticFAQs): ?>
                These are official FAQs provided by the course coordinators.
              <?php else: ?>
                These are answers from highly-rated mentoring sessions. Click on a question to see the answer.
              <?php endif; ?>
            </small>
          </div>
        <?php else: ?>
          <div class="no-faqs">
            <i class="fas fa-inbox fa-3x mb-3 text-muted"></i>
            <p>No frequent questions yet for this subject.</p>
            <p class="mb-0">Be the first to ask a question!</p>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <!-- Active Chat Previews -->
    <section class="mb-5">
      <h3 class="section-title">
        <i class="fas fa-comments me-2 text-success"></i>Active Conversations
      </h3>
      <ul id="active-chats" class="list-group">
        <li class="list-group-item">Loading chats...</li>
      </ul>
    </section>

    <!-- Closed Chat Previews -->
    <section class="mb-5">
      <h3 class="section-title">
        <i class="fas fa-archive me-2 text-secondary"></i>Closed Conversations
      </h3>
      <ul id="closed-chats" class="list-group">
        <li class="list-group-item">Loading closed chats...</li>
      </ul>
    </section>

  </main>
  
  <?php 
  // Mostrar el role switcher si el usuario tiene ambos roles
  echo renderRoleSwitcher($pdo, $_SESSION['user_id'], $subjectId, 'mentee');
  ?>
  
  <!-- Publish Question Modal -->
  <div class="modal fade" id="requestModal" tabindex="-1" aria-labelledby="requestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form action="php/request_help.php" method="post" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="requestModalLabel">Publish a Question</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
          <div class="mb-3 text-start">
            <label for="mentorMessage" class="form-label">Your question</label>
            <textarea id="mentorMessage" name="message" class="form-control" rows="4" 
                      placeholder="Be specific about what you need help with..." required></textarea>
            <small class="text-muted">
              <i class="fas fa-info-circle me-1"></i>
              Tip: Check the FAQs first to see if your question has already been answered.
            </small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Send Request</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Success Modal -->
  <div class="modal fade" id="requestSuccessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Thank you!</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Your question has been submitted successfully. A mentor will respond soon!
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Footer -->
  <div id="footer-placeholder"></div>
  <div id="modal-container"></div>

  <script>
    window.__SUBJECT_ID__ = <?= json_encode($subjectId) ?>;
    
    // Toggle individual FAQ
    function toggleFAQ(index) {
      const faqItem = document.getElementById(`faq-${index}`);
      faqItem.classList.toggle('expanded');
      
      // Close other FAQs
      document.querySelectorAll('.faq-item').forEach((item, i) => {
        if (i !== index && item.classList.contains('expanded')) {
          item.classList.remove('expanded');
        }
      });
    }
    
    // Auto-expand first FAQ if exists
    document.addEventListener('DOMContentLoaded', function() {
      const firstFAQ = document.querySelector('.faq-item');
      if (firstFAQ) {
        setTimeout(() => {
          firstFAQ.classList.add('expanded');
        }, 500);
      }
    });
  </script>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
  <script src="assets/js/main.js" defer></script>
  
  <?php if (isset($_GET['submitted']) && $_GET['submitted'] === '1'): ?>
    <script>
      document.addEventListener("DOMContentLoaded", () => {
        const successModal = new bootstrap.Modal(document.getElementById('requestSuccessModal'));
        successModal.show();
      });
    </script>
  <?php endif; ?>

</body>
</html>