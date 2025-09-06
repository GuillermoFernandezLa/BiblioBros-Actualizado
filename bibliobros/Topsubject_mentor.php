<?php
/*
 * Topsubject_mentor.php
 *
 * This page displays the mentor's view for a specific subject.
 * It shows the mentor's introductory message, mentee requests, active chats, and closed conversations.
 */
require_once __DIR__ . '/auth_guard.php';
require_once 'role_switcher.php'; // AÑADIDO: Incluir el componente role switcher

// Get and validate subject_id
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

// Fetch this mentor's intro for the subject
$stmt2 = $pdo->prepare("
    SELECT intro
    FROM mentor_subject
    WHERE user_id = :uid
      AND subject_id = :sid
    LIMIT 1
");
$stmt2->execute([
  'uid' => $_SESSION['user_id'],
  'sid' => $subjectId
]);
$row = $stmt2->fetch(PDO::FETCH_ASSOC);
$introMessage = $row ? htmlspecialchars($row['intro']) : 'No intro message provided.';
$hasIntro = $row ? true : false;

// Check if mentor has already seen the subject mentor tutorial
$showSubjectMentorTutorial = false;

$stmt = $pdo->prepare("SELECT has_seen_subject_mentor FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$hasSeen = $stmt->fetchColumn();

if (!$hasSeen) {
  $showSubjectMentorTutorial = true;
  $updateStmt = $pdo->prepare("UPDATE users SET has_seen_subject_mentor = TRUE WHERE id = ?");
  $updateStmt->execute([$_SESSION['user_id']]);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>BiblioBros – <?= $subjectName ?> (Mentor)</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />
  
  <style>
    .intro-section {
      background: linear-gradient(135deg, #fff8e1 0%, #ffffff 100%);
      border-radius: 10px;
      padding: 1.5rem;
      margin-bottom: 2rem;
      border: 2px solid #ffc107;
      position: relative;
    }
    
    .intro-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
    }
    
    .intro-title {
      color: #333;
      font-size: 1.25rem;
      font-weight: 600;
      margin: 0;
    }
    
    .edit-intro-btn {
      background: white;
      border: 2px solid #ffc107;
      color: #ffc107;
      padding: 0.375rem 0.75rem;
      border-radius: 5px;
      font-size: 0.875rem;
      transition: all 0.3s ease;
      text-decoration: none;
    }
    
    .edit-intro-btn:hover {
      background: #ffc107;
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
    }
    
    .intro-content {
      background: white;
      padding: 1rem;
      border-radius: 5px;
      border-left: 3px solid #ffc107;
      min-height: 80px;
      white-space: pre-wrap;
      word-wrap: break-word;
    }
    
    .no-intro {
      color: #6c757d;
      font-style: italic;
      text-align: center;
      padding: 2rem;
    }
    
    .section-card {
      background: white;
      border-radius: 10px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
      border: 1px solid #e9ecef;
    }
    
    .section-card h4 {
      color: #333;
      font-size: 1.15rem;
      font-weight: 600;
      margin-bottom: 1rem;
      padding-bottom: 0.5rem;
      border-bottom: 2px solid #f8f9fa;
    }
    
    .section-card h4 i {
      margin-right: 0.5rem;
    }
    
    .requests-section h4 i {
      color: #17a2b8;
    }
    
    .active-chats-section h4 i {
      color: #28a745;
    }
    
    .closed-chats-section h4 i {
      color: #6c757d;
    }
    
    .list-group-item {
      border: 1px solid #e9ecef;
      border-radius: 5px !important;
      margin-bottom: 0.5rem;
      transition: all 0.3s ease;
    }
    
    .list-group-item:hover {
      transform: translateX(5px);
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      border-color: #ffc107;
    }
    
    .badge-mentor {
      background: #ffc107;
      color: #333;
      font-weight: 500;
      padding: 0.35rem 0.65rem;
      font-size: 0.875rem;
    }
    
    .empty-state {
      text-align: center;
      padding: 2rem;
      color: #6c757d;
    }
    
    .empty-state i {
      font-size: 3rem;
      margin-bottom: 1rem;
      opacity: 0.5;
    }
    
    .stats-bar {
      background: #f8f9fa;
      border-radius: 10px;
      padding: 1rem;
      margin-bottom: 2rem;
      display: flex;
      justify-content: space-around;
      text-align: center;
    }
    
    .stat-item {
      flex: 1;
    }
    
    .stat-value {
      font-size: 1.5rem;
      font-weight: bold;
      color: #ffc107;
    }
    
    .stat-label {
      font-size: 0.875rem;
      color: #6c757d;
    }
  </style>
</head>

<body class="d-flex flex-column min-vh-100 mentor-theme">

  <!-- Navbar -->
  <div id="navbar-placeholder"></div>

  <!-- MAIN SUBJECT VIEW -->
  <main class="container py-5">
    <div class="mx-auto" style="max-width: 900px;">
      
      <?php 
      // AÑADIDO: Mostrar prompt para añadir rol de mentee si no lo tiene
      if (canAddOppositeRole($pdo, $_SESSION['user_id'], $subjectId, 'mentor')) {
          echo renderAddRolePrompt($subjectId, 'mentor');
      }
      ?>
      
      <!-- Subject Title -->
      <h2 class="section-title text-center mb-4">
        <i class="fas fa-graduation-cap me-2 text-warning"></i>
        <?= $subjectName ?>
        <?= renderCurrentRoleBadge('mentor') // AÑADIDO: Badge del rol actual ?>
      </h2>

      <!-- Quick Stats Bar -->
      <div class="stats-bar">
        <div class="stat-item">
          <div class="stat-value" id="pending-count">0</div>
          <div class="stat-label">Pending Requests</div>
        </div>
        <div class="stat-item">
          <div class="stat-value" id="active-count">0</div>
          <div class="stat-label">Active Chats</div>
        </div>
        <div class="stat-item">
          <div class="stat-value" id="completed-count">0</div>
          <div class="stat-label">Completed</div>
        </div>
      </div>

      <!-- Intro message -->
      <section class="intro-section">
        <div class="intro-header">
          <h4 class="intro-title">
            <i class="fas fa-bullhorn me-2 text-warning"></i>
            Your Introductory Message
          </h4>
          <a href="Topsubject_mentor_intro.php?subject_id=<?= $subjectId ?>" class="edit-intro-btn">
            <i class="fas fa-edit me-1"></i>
            <?= $hasIntro ? 'Edit' : 'Add Introduction' ?>
          </a>
        </div>
        <div class="intro-content">
          <?php if ($hasIntro): ?>
            <?= $introMessage ?>
          <?php else: ?>
            <div class="no-intro">
              <i class="fas fa-exclamation-circle me-2"></i>
              No introduction message yet. Click "Add Introduction" to create one.
            </div>
          <?php endif; ?>
        </div>
      </section>

      <!-- Requests from mentees -->
      <section class="section-card requests-section">
        <h4>
          <i class="fas fa-inbox"></i>
          Requests Received
        </h4>
        <ul id="pending-requests" class="list-group list-group-flush">
          <li class="list-group-item text-center">
            <span class="spinner-border spinner-border-sm me-2"></span>
            Loading questions...
          </li>
        </ul>
      </section>

      <!-- Active chats -->
      <section class="section-card active-chats-section">
        <h4>
          <i class="fas fa-comments"></i>
          Active Chats
        </h4>
        <ul id="active-chats" class="list-group list-group-flush">
          <li class="list-group-item text-center">
            <span class="spinner-border spinner-border-sm me-2"></span>
            Loading chats...
          </li>
        </ul>
      </section>

      <!-- Closed Chat Previews -->
      <section class="section-card closed-chats-section">
        <h4>
          <i class="fas fa-archive"></i>
          Closed Conversations
        </h4>
        <ul id="closed-chats" class="list-group list-group-flush">
          <li class="list-group-item text-center">
            <span class="spinner-border spinner-border-sm me-2"></span>
            Loading closed chats...
          </li>
        </ul>
      </section>
    </div>
  </main>

  <?php 
  // AÑADIDO: Mostrar el role switcher si el usuario tiene ambos roles
  echo renderRoleSwitcher($pdo, $_SESSION['user_id'], $subjectId, 'mentor');
  ?>

  <!-- Edit Introduction Modal -->
  <div class="modal fade" id="editIntroModal" tabindex="-1" aria-labelledby="editIntroModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-warning">
          <h5 class="modal-title text-white" id="editIntroModalLabel">
            <i class="fas fa-edit me-2"></i>Edit Introduction
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="php/update_intro.php" method="post">
          <div class="modal-body">
            <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
            <div class="mb-3">
              <label for="intro-edit" class="form-label">Your introductory message:</label>
              <textarea 
                id="intro-edit" 
                name="intro_message" 
                class="form-control" 
                rows="5" 
                required
                maxlength="500"><?= $hasIntro ? strip_tags($introMessage) : '' ?></textarea>
              <div class="form-text">Maximum 500 characters</div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-warning">
              <i class="fas fa-save me-1"></i>Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modals & Footer -->
  <div id="modal-container"></div>
  <div id="footer-placeholder"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Set chat loader URL dynamically
    const CHAT_LOADER = 'php/chat_loader_mentor.php?subject_id=<?= $subjectId ?>';
    
    // Update stats counters when data is loaded
    document.addEventListener('DOMContentLoaded', function() {
      // This will be updated by main.js when it loads the data
      setTimeout(() => {
        const pendingRequests = document.querySelectorAll('#pending-requests .list-group-item').length;
        const activeChats = document.querySelectorAll('#active-chats .list-group-item').length;
        const closedChats = document.querySelectorAll('#closed-chats .list-group-item').length;
        
        // Update counters (excluding loading placeholders)
        if (pendingRequests > 0 && !document.querySelector('#pending-requests .spinner-border')) {
          document.getElementById('pending-count').textContent = pendingRequests;
        }
        if (activeChats > 0 && !document.querySelector('#active-chats .spinner-border')) {
          document.getElementById('active-count').textContent = activeChats;
        }
        if (closedChats > 0 && !document.querySelector('#closed-chats .spinner-border')) {
          document.getElementById('completed-count').textContent = closedChats;
        }
      }, 2000);
    });
  </script>
  <script src="assets/js/main.js"></script>

  <?php if ($showSubjectMentorTutorial): ?>
    <div class="modal fade show" id="subjectMentorTutorialModal" tabindex="-1"
      aria-labelledby="subjectMentorTutorialLabel" aria-hidden="true" style="display: block;" data-bs-backdrop="static"
      data-bs-keyboard="false">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-warning">
            <h5 class="modal-title text-white" id="subjectMentorTutorialLabel">
              <i class="fas fa-info-circle me-2"></i>Mentor Dashboard – Tutorial
            </h5>
          </div>
          <div class="modal-body" style="max-height: 300px; overflow-y: auto;">
            <p>Welcome to your mentor view for <strong><?= $subjectName ?></strong>! 🧑‍🏫</p>

            <p><strong>📝 Your Introduction:</strong> This is the first thing mentees see. Make it welcoming and informative! You can edit it anytime.</p>

            <p><strong>📥 Requests Received:</strong> Questions from mentees appear here. Choose which ones to accept based on your expertise and availability.</p>

            <p><strong>💬 Chat Management:</strong></p>
            <ul>
              <li><strong>Active Chats</strong> – Ongoing conversations with mentees</li>
              <li><strong>Closed Conversations</strong> – Completed sessions for reference</li>
            </ul>

            <p><strong>📊 Stats Bar:</strong> Track your mentoring activity at a glance.</p>

            <p>Check back regularly to help new students!</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-warning text-white" onclick="closeSubjectMentorTutorial()">
              <i class="fas fa-check me-1"></i>Got it!
            </button>
          </div>
        </div>
      </div>
    </div>

    <script>
      function closeSubjectMentorTutorial() {
        const modal = document.getElementById('subjectMentorTutorialModal');
        modal.classList.remove('show');
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) backdrop.remove();
      }

      document.addEventListener('DOMContentLoaded', () => {
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
      });
    </script>
  <?php endif; ?>

</body>
</html>