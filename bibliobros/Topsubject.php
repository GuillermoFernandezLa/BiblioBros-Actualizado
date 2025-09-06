<?php
require_once __DIR__ . '/auth_guard.php';

$subjectId = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? (int)($_POST['subject_id'] ?? 0)
    : (int)($_GET['subject_id'] ?? 0);

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

// Check ALL existing roles for this user and subject
$stmtRoles = $pdo->prepare("
  SELECT role
  FROM user_subject_role
  WHERE user_id = :uid AND subject_id = :sid
  ORDER BY role
");
$stmtRoles->execute(['uid' => $userId, 'sid' => $subjectId]);
$existingRoles = $stmtRoles->fetchAll(PDO::FETCH_COLUMN);

$isMentor = in_array('mentor', $existingRoles);
$isMentee = in_array('mentee', $existingRoles);
$hasBothRoles = $isMentor && $isMentee;

// Handle POST role selection or addition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $role = $_POST['role'] ?? '';
    
    // Adding a new role
    if ($action === 'add_role' && in_array($role, ['mentor', 'mentee'], true)) {
        // Check if they already have this role
        if (!in_array($role, $existingRoles)) {
            // Add the new role
            $insert = $pdo->prepare("
                INSERT IGNORE INTO user_subject_role (user_id, subject_id, role)
                VALUES (:uid, :sid, :role)
            ");
            $insert->execute(['uid' => $userId, 'sid' => $subjectId, 'role' => $role]);
            
            // Set as active role
            $setActive = $pdo->prepare("
                INSERT INTO user_active_role (user_id, subject_id, active_role)
                VALUES (:uid, :sid, :role)
                ON DUPLICATE KEY UPDATE active_role = :role, updated_at = NOW()
            ");
            $setActive->execute(['uid' => $userId, 'sid' => $subjectId, 'role' => $role]);
        }
        
        // Redirect based on the role
        if ($role === 'mentor') {
            $chk = $pdo->prepare("
                SELECT intro FROM mentor_subject
                WHERE user_id = :uid AND subject_id = :sid
                LIMIT 1
            ");
            $chk->execute(['uid' => $userId, 'sid' => $subjectId]);
            $intro = $chk->fetchColumn();
            
            header("Location: " . ($intro
                ? "Topsubject_mentor.php?subject_id=$subjectId"
                : "Topsubject_mentor_intro.php?subject_id=$subjectId"
            ));
        } else {
            header("Location: Topsubject_mentee.php?subject_id=$subjectId");
        }
        exit;
    }
    
    // Switching between existing roles
    if ($action === 'switch_role' && in_array($role, ['mentor', 'mentee'], true)) {
        // Update active role
        $setActive = $pdo->prepare("
            INSERT INTO user_active_role (user_id, subject_id, active_role)
            VALUES (:uid, :sid, :role)
            ON DUPLICATE KEY UPDATE active_role = :role, updated_at = NOW()
        ");
        $setActive->execute(['uid' => $userId, 'sid' => $subjectId, 'role' => $role]);
        
        // Redirect based on the role
        if ($role === 'mentor') {
            $chk = $pdo->prepare("
                SELECT intro FROM mentor_subject
                WHERE user_id = :uid AND subject_id = :sid
                LIMIT 1
            ");
            $chk->execute(['uid' => $userId, 'sid' => $subjectId]);
            $intro = $chk->fetchColumn();
            
            header("Location: " . ($intro
                ? "Topsubject_mentor.php?subject_id=$subjectId"
                : "Topsubject_mentor_intro.php?subject_id=$subjectId"
            ));
        } else {
            header("Location: Topsubject_mentee.php?subject_id=$subjectId");
        }
        exit;
    }
}

// If user has exactly one role, redirect directly
if (count($existingRoles) === 1 && !isset($_GET['change_role'])) {
    $role = $existingRoles[0];
    if ($role === 'mentor') {
        $chk = $pdo->prepare("
            SELECT intro FROM mentor_subject
            WHERE user_id = :uid AND subject_id = :sid
            LIMIT 1
        ");
        $chk->execute(['uid' => $userId, 'sid' => $subjectId]);
        $intro = $chk->fetchColumn();
        
        header("Location: " . ($intro
            ? "Topsubject_mentor.php?subject_id=$subjectId"
            : "Topsubject_mentor_intro.php?subject_id=$subjectId"
        ));
    } else {
        header("Location: Topsubject_mentee.php?subject_id=$subjectId");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>BiblioBros – <?= $subjectName ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />
  
  <style>
    .role-card {
      border: 2px solid #dee2e6;
      border-radius: 10px;
      padding: 2rem;
      transition: all 0.3s ease;
      position: relative;
      background: white;
    }
    
    .role-card:hover {
      border-color: #ffc107;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .role-card.active {
      border-color: #28a745;
      background: #f0fff4;
    }
    
    .role-badge {
      position: absolute;
      top: 10px;
      right: 10px;
      background: #28a745;
      color: white;
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-size: 0.875rem;
      font-weight: 600;
    }
    
    .role-icon {
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1rem;
    }
    
    .role-icon i {
      color: white;
      font-size: 1.5rem;
    }
    
    .both-roles-notice {
      background: #fff3cd;
      border: 1px solid #ffc107;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 2rem;
    }
    
    .switch-role-btn {
      background: white;
      border: 2px solid #ffc107;
      color: #333;
      padding: 0.5rem 1rem;
      border-radius: 8px;
      transition: all 0.3s ease;
    }
    
    .switch-role-btn:hover {
      background: #ffc107;
      color: white;
    }
  </style>
</head>

<body class="d-flex flex-column min-vh-100">

  <!-- Navbar -->
  <div id="navbar-placeholder"></div>

  <!-- MAIN CONTENT: Role chooser -->
  <main class="container py-5 flex-grow-1">
    <section class="text-center mb-5">
      <h2 class="section-title"><?= $subjectName ?></h2>
      
      <?php if ($hasBothRoles): ?>
        <!-- User has both roles - show switcher -->
        <div class="both-roles-notice">
          <i class="fas fa-info-circle me-2"></i>
          <strong>You have both roles in this subject!</strong>
          <p class="mb-0 mt-2">Choose which role you want to use right now:</p>
        </div>
        
        <div class="row justify-content-center g-4">
          <div class="col-md-5">
            <div class="role-card <?= $isMentor ? 'active' : '' ?>">
              <div class="role-icon">
                <i class="fas fa-user-graduate"></i>
              </div>
              <h4>Mentor Mode</h4>
              <p class="text-muted mb-3">Help other students and answer questions</p>
              <form method="post" class="d-inline">
                <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
                <input type="hidden" name="action" value="switch_role">
                <button type="submit" name="role" value="mentor" class="btn btn-warning">
                  <i class="fas fa-arrow-right me-2"></i>Continue as Mentor
                </button>
              </form>
            </div>
          </div>
          
          <div class="col-md-5">
            <div class="role-card <?= $isMentee ? 'active' : '' ?>">
              <div class="role-icon" style="background: linear-gradient(135deg, #56CCF2 0%, #2F80ED 100%);">
                <i class="fas fa-user"></i>
              </div>
              <h4>Mentee Mode</h4>
              <p class="text-muted mb-3">Ask questions and get help from mentors</p>
              <form method="post" class="d-inline">
                <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
                <input type="hidden" name="action" value="switch_role">
                <button type="submit" name="role" value="mentee" class="btn btn-primary">
                  <i class="fas fa-arrow-right me-2"></i>Continue as Mentee
                </button>
              </form>
            </div>
          </div>
        </div>
        
      <?php elseif (count($existingRoles) === 1): ?>
        <!-- User has one role - offer to add the other -->
        <p class="lead">You are currently a <strong><?= ucfirst($existingRoles[0]) ?></strong> in this subject.</p>
        
        <div class="alert alert-info">
          <i class="fas fa-lightbulb me-2"></i>
          <strong>Did you know?</strong> You can be both a mentor and mentee in the same subject!
        </div>
        
        <div class="row justify-content-center g-4">
          <div class="col-md-5">
            <div class="role-card">
              <?php if ($existingRoles[0] === 'mentee'): ?>
                <div class="role-icon">
                  <i class="fas fa-user-graduate"></i>
                </div>
                <h4>Become a Mentor Too!</h4>
                <p class="text-muted mb-3">Share your knowledge with others</p>
                <form method="post">
                  <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
                  <input type="hidden" name="action" value="add_role">
                  <button type="submit" name="role" value="mentor" class="btn btn-warning">
                    <i class="fas fa-plus me-2"></i>Add Mentor Role
                  </button>
                </form>
              <?php else: ?>
                <div class="role-icon" style="background: linear-gradient(135deg, #56CCF2 0%, #2F80ED 100%);">
                  <i class="fas fa-user"></i>
                </div>
                <h4>Become a Mentee Too!</h4>
                <p class="text-muted mb-3">Get help when you need it</p>
                <form method="post">
                  <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
                  <input type="hidden" name="action" value="add_role">
                  <button type="submit" name="role" value="mentee" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add Mentee Role
                  </button>
                </form>
              <?php endif; ?>
            </div>
          </div>
          
          <div class="col-md-5">
            <div class="role-card active">
              <span class="role-badge">Current Role</span>
              <?php if ($existingRoles[0] === 'mentor'): ?>
                <div class="role-icon">
                  <i class="fas fa-user-graduate"></i>
                </div>
                <h4>Continue as Mentor</h4>
                <p class="text-muted mb-3">Keep helping students</p>
                <form method="post">
                  <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
                  <input type="hidden" name="action" value="switch_role">
                  <button type="submit" name="role" value="mentor" class="btn btn-warning">
                    <i class="fas fa-arrow-right me-2"></i>Go to Mentor Dashboard
                  </button>
                </form>
              <?php else: ?>
                <div class="role-icon" style="background: linear-gradient(135deg, #56CCF2 0%, #2F80ED 100%);">
                  <i class="fas fa-user"></i>
                </div>
                <h4>Continue as Mentee</h4>
                <p class="text-muted mb-3">Keep learning</p>
                <form method="post">
                  <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
                  <input type="hidden" name="action" value="switch_role">
                  <button type="submit" name="role" value="mentee" class="btn btn-primary">
                    <i class="fas fa-arrow-right me-2"></i>Go to Mentee Dashboard
                  </button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
        
      <?php else: ?>
        <!-- User has no roles - first time selection -->
        <p class="lead">Please choose your role for this subject:</p>
        <p class="text-muted">Don't worry, you can add another role later!</p>
        
        <div class="row justify-content-center g-4 mt-4">
          <div class="col-md-5">
            <div class="role-card">
              <div class="role-icon">
                <i class="fas fa-user-graduate"></i>
              </div>
              <h4>I want to be a Mentor</h4>
              <p class="text-muted mb-3">I can help other students with this subject</p>
              <form method="post">
                <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
                <input type="hidden" name="action" value="add_role">
                <button type="submit" name="role" value="mentor" class="btn btn-warning btn-lg">
                  <i class="fas fa-user-graduate me-2"></i>Become a Mentor
                </button>
              </form>
            </div>
          </div>
          
          <div class="col-md-5">
            <div class="role-card">
              <div class="role-icon" style="background: linear-gradient(135deg, #56CCF2 0%, #2F80ED 100%);">
                <i class="fas fa-user"></i>
              </div>
              <h4>I need help (Mentee)</h4>
              <p class="text-muted mb-3">I want to ask questions and learn</p>
              <form method="post">
                <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
                <input type="hidden" name="action" value="add_role">
                <button type="submit" name="role" value="mentee" class="btn btn-primary btn-lg">
                  <i class="fas fa-user me-2"></i>Become a Mentee
                </button>
              </form>
            </div>
          </div>
        </div>
        
        <div class="alert alert-light mt-5">
          <i class="fas fa-info-circle me-2"></i>
          <strong>Tip:</strong> You can be both a mentor and mentee! Many students help others while also asking for help when needed.
        </div>
      <?php endif; ?>
    </section>
  </main>

  <!-- Modals and Footer -->
  <div id="modal-container"></div>
  <div id="footer-placeholder"></div>

  <!-- Bootstrap JS and shared logic -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/main.js"></script>
</body>

</html>