<?php
require_once __DIR__ . '/auth_guard.php';
$userId = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $public_description = trim($_POST['public_description'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $errors = [];

    if (!$fullname || !$email) $errors[] = 'Full name and email are required.';
    if ($newPassword && $newPassword !== $confirmPassword) $errors[] = 'Passwords do not match.';
    if ($errors) {
        header("Location: Topprofile.php?error=" . urlencode(implode(' ', $errors)));
        exit;
    }

    $params = ['fullname'=>$fullname, 'email'=>$email, 'desc'=>$public_description, 'uid'=>$userId];
    $set = 'fullname = :fullname, email = :email, public_description = :desc';
    if ($newPassword) {
        $params['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
        $set .= ', password_hash = :password_hash';
    }
    $stmt = $pdo->prepare("UPDATE users SET $set WHERE id = :uid");
    $stmt->execute($params);
    header('Location: Topprofile.php?updated=1');
    exit;
}

// Fetch user data
$stmt = $pdo->prepare("
    SELECT u.fullname, u.email, u.public_description, uni.name AS university_name, u.created_at
    FROM users u
    JOIN universities uni ON uni.id = u.university_id
    WHERE u.id = :uid LIMIT 1
");
$stmt->execute(['uid' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    session_destroy();
    header('Location: Toplogin.php');
    exit;
}

// Fetch user statistics
$statsStmt = $pdo->prepare("
    SELECT 
        (SELECT COUNT(*) FROM user_subject_role WHERE user_id = :uid AND role = 'mentor') as mentor_subjects,
        (SELECT COUNT(*) FROM user_subject_role WHERE user_id = :uid AND role = 'mentee') as mentee_subjects,
        (SELECT COUNT(*) FROM chats c JOIN requests r ON r.id = c.request_id WHERE r.mentee_id = :uid) as total_chats_mentee,
        (SELECT COUNT(*) FROM chats c JOIN requests r ON r.id = c.request_id WHERE r.mentor_id = :uid) as total_chats_mentor,
        (SELECT AVG(score) FROM ratings ra JOIN chats c ON c.id = ra.chat_id JOIN requests r ON r.id = c.request_id WHERE r.mentor_id = :uid) as avg_rating,
        (SELECT COUNT(*) FROM ratings ra JOIN chats c ON c.id = ra.chat_id JOIN requests r ON r.id = c.request_id WHERE r.mentor_id = :uid) as total_ratings
");
$statsStmt->execute(['uid' => $userId]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Rest of the queries remain the same...
$stmt2 = $pdo->prepare("
    SELECT usr.role, s.name AS subject_name, f.name AS faculty_name, uni.name AS university_name
    FROM user_subject_role usr
    JOIN subjects s ON s.id = usr.subject_id
    JOIN faculties f ON f.id = s.faculty_id
    JOIN universities uni ON uni.id = f.university_id
    WHERE usr.user_id = :uid
    ORDER BY uni.name, f.name, s.name
");
$stmt2->execute(['uid' => $userId]);
$assocs = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Open and closed chats queries...
$stmt5 = $pdo->prepare("
    SELECT c.id AS chat_id, u.fullname AS mentor_name, s.name AS subject_name,
           DATE_FORMAT(c.created_at, '%d %b %Y %H:%i') AS opened_at
    FROM chats c
    JOIN requests r ON r.id = c.request_id
    JOIN users u ON u.id = r.mentor_id
    JOIN subjects s ON s.id = r.subject_id
    WHERE r.mentee_id = :uid AND c.active = TRUE
    ORDER BY c.created_at DESC
");
$stmt5->execute(['uid' => $userId]);
$openAsMentee = $stmt5->fetchAll(PDO::FETCH_ASSOC);

$stmt6 = $pdo->prepare("
    SELECT c.id AS chat_id, u.fullname AS mentee_name, s.name AS subject_name,
           DATE_FORMAT(c.created_at, '%d %b %Y %H:%i') AS opened_at
    FROM chats c
    JOIN requests r ON r.id = c.request_id
    JOIN users u ON u.id = r.mentee_id
    JOIN subjects s ON s.id = r.subject_id
    WHERE r.mentor_id = :uid AND c.active = TRUE
    ORDER BY c.created_at DESC
");
$stmt6->execute(['uid' => $userId]);
$openAsMentor = $stmt6->fetchAll(PDO::FETCH_ASSOC);

// Closed chats...
$stmt3 = $pdo->prepare("
    SELECT c.id AS chat_id, u.fullname AS mentor_name, s.name AS subject_name,
           DATE_FORMAT(c.created_at, '%d %b %Y') AS closed_at
    FROM chats c
    JOIN requests r ON r.id = c.request_id
    JOIN users u ON u.id = r.mentor_id
    JOIN subjects s ON s.id = r.subject_id
    WHERE r.mentee_id = :uid AND c.active = FALSE
    ORDER BY c.created_at DESC LIMIT 5
");
$stmt3->execute(['uid' => $userId]);
$closedAsMentee = $stmt3->fetchAll(PDO::FETCH_ASSOC);

$stmt4 = $pdo->prepare("
    SELECT c.id AS chat_id, u.fullname AS mentee_name, s.name AS subject_name,
           DATE_FORMAT(c.created_at, '%d %b %Y') AS closed_at
    FROM chats c
    JOIN requests r ON r.id = c.request_id
    JOIN users u ON u.id = r.mentee_id
    JOIN subjects s ON s.id = r.subject_id
    WHERE r.mentor_id = :uid AND c.active = FALSE
    ORDER BY c.created_at DESC LIMIT 5
");
$stmt4->execute(['uid' => $userId]);
$closedAsMentor = $stmt4->fetchAll(PDO::FETCH_ASSOC);

// Ratings
$ratingsStmt = $pdo->prepare("
    SELECT r.score, r.comment, u.fullname AS mentee_name, s.name AS subject_name,
           DATE_FORMAT(r.created_at, '%d %b %Y') as rating_date
    FROM ratings r
    JOIN chats c ON c.id = r.chat_id
    JOIN requests req ON req.id = c.request_id
    JOIN users u ON u.id = req.mentee_id
    JOIN subjects s ON s.id = req.subject_id
    WHERE req.mentor_id = :uid
    ORDER BY r.created_at DESC LIMIT 5
");
$ratingsStmt->execute(['uid' => $userId]);
$ratings = $ratingsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>BiblioBros – Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="assets/css/style.css"/>
  
  <style>
    .profile-header {
      background: linear-gradient(135deg, #ffc107 0%, #ffdb4d 100%);
      color: white;
      padding: 2rem 0;
      border-radius: 10px;
      margin-bottom: 2rem;
    }
    
    .profile-avatar {
      width: 100px;
      height: 100px;
      background: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 3rem;
      color: #ffc107;
      margin: 0 auto 1rem;
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 1rem;
      margin-top: 1.5rem;
    }
    
    .stat-card {
      background: white;
      padding: 1rem;
      border-radius: 10px;
      text-align: center;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .stat-value {
      font-size: 2rem;
      font-weight: bold;
      color: #ffc107;
    }
    
    .stat-label {
      font-size: 0.875rem;
      color: #6c757d;
      margin-top: 0.25rem;
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
    
    .form-card {
      background: white;
      border-radius: 10px;
      padding: 2rem;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .role-badge {
      display: inline-block;
      padding: 0.25rem 0.5rem;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 500;
    }
    
    .role-badge.mentor {
      background: #ffc107;
      color: #333;
    }
    
    .role-badge.mentee {
      background: #17a2b8;
      color: white;
    }
    
    .rating-stars {
      color: #ffc107;
    }
    
    .chat-item {
      transition: all 0.3s ease;
    }
    
    .chat-item:hover {
      transform: translateX(5px);
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .nav-tabs .nav-link {
      color: #6c757d;
      border: none;
      border-bottom: 3px solid transparent;
      margin-bottom: -1px;
    }
    
    .nav-tabs .nav-link.active {
      color: #ffc107;
      border-bottom-color: #ffc107;
      background: transparent;
    }
    
    .nav-tabs .nav-link:hover {
      color: #ffc107;
      border-color: transparent;
    }
    
    .password-toggle {
      position: absolute;
      right: 10px;
      top: 38px;
      cursor: pointer;
      z-index: 10;
      background: transparent;
      border: none;
      color: #6c757d;
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
  </style>
</head>
<body class="d-flex flex-column min-vh-100">
<div id="navbar-placeholder"></div>

<main class="container flex-grow-1 py-5">
  <!-- Profile Header -->
  <div class="profile-header text-center">
    <div class="profile-avatar">
      <i class="fas fa-user"></i>
    </div>
    <h2><?= htmlspecialchars($user['fullname']) ?></h2>
    <p class="mb-1"><i class="fas fa-university me-2"></i><?= htmlspecialchars($user['university_name']) ?></p>
    <p class="mb-0"><i class="fas fa-calendar me-2"></i>Member since <?= date('F Y', strtotime($user['created_at'])) ?></p>
    
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-value"><?= $stats['mentor_subjects'] ?></div>
        <div class="stat-label">Mentor Subjects</div>
      </div>
      <div class="stat-card">
        <div class="stat-value"><?= $stats['mentee_subjects'] ?></div>
        <div class="stat-label">Learning Subjects</div>
      </div>
      <div class="stat-card">
        <div class="stat-value"><?= $stats['total_chats_mentor'] + $stats['total_chats_mentee'] ?></div>
        <div class="stat-label">Total Chats</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">
          <?= $stats['avg_rating'] ? number_format($stats['avg_rating'], 1) : '—' ?>
        </div>
        <div class="stat-label">Avg Rating</div>
      </div>
    </div>
  </div>

  <!-- Navigation Tabs -->
  <ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#edit-profile">
        <i class="fas fa-edit me-2"></i>Edit Profile
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#subjects-roles">
        <i class="fas fa-book me-2"></i>Subjects & Roles
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#conversations">
        <i class="fas fa-comments me-2"></i>Conversations
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#ratings">
        <i class="fas fa-star me-2"></i>Ratings
      </a>
    </li>
  </ul>

  <!-- Tab Content -->
  <div class="tab-content">
    <!-- Edit Profile Tab -->
    <div class="tab-pane fade show active" id="edit-profile">
      <div class="form-card mx-auto" style="max-width: 700px;">
        <h4 class="mb-4"><i class="fas fa-user-edit me-2 text-warning"></i>Edit Your Information</h4>
        
        <?php if (isset($_GET['error'])): ?>
          <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php elseif (isset($_GET['updated'])): ?>
          <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            Profile updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <form method="post" novalidate>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="fas fa-user me-1"></i>Full Name</label>
              <input type="text" name="fullname" class="form-control" 
                     value="<?= htmlspecialchars($user['fullname']) ?>" required/>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="fas fa-envelope me-1"></i>Email</label>
              <input type="email" name="email" class="form-control" 
                     value="<?= htmlspecialchars($user['email']) ?>" required/>
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label"><i class="fas fa-university me-1"></i>University</label>
            <input type="text" class="form-control" 
                   value="<?= htmlspecialchars($user['university_name']) ?>" readonly/>
          </div>
          
          <div class="mb-3">
            <label class="form-label"><i class="fas fa-align-left me-1"></i>Bio / Description</label>
            <textarea name="public_description" class="form-control" rows="4" 
                      placeholder="Tell others about yourself..."><?= htmlspecialchars($user['public_description'] ?? '') ?></textarea>
          </div>
          
          <hr class="my-4">
          <h5 class="mb-3"><i class="fas fa-lock me-2 text-warning"></i>Change Password</h5>
          
          <div class="row">
            <div class="col-md-6 mb-3 position-relative">
              <label class="form-label">New Password</label>
              <input type="password" id="new_password" name="new_password" class="form-control"/>
              <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                <i class="fas fa-eye"></i>
              </button>
            </div>
            <div class="col-md-6 mb-3 position-relative">
              <label class="form-label">Confirm Password</label>
              <input type="password" id="confirm_password" name="confirm_password" class="form-control"/>
              <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                <i class="fas fa-eye"></i>
              </button>
            </div>
          </div>
          
          <div class="d-flex justify-content-between mt-4">
            <button type="reset" class="btn btn-light">
              <i class="fas fa-undo me-2"></i>Reset
            </button>
            <button type="submit" class="btn btn-warning">
              <i class="fas fa-save me-2"></i>Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Subjects & Roles Tab -->
    <div class="tab-pane fade" id="subjects-roles">
      <div class="section-card">
        <h4><i class="fas fa-graduation-cap me-2 text-warning"></i>Your Subjects & Roles</h4>
        <?php if ($assocs): ?>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Faculty</th>
                  <th>Subject</th>
                  <th>Role</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($assocs as $a): ?>
                  <tr>
                    <td><?= htmlspecialchars($a['faculty_name']) ?></td>
                    <td><?= htmlspecialchars($a['subject_name']) ?></td>
                    <td>
                      <span class="role-badge <?= $a['role'] ?>">
                        <i class="fas fa-<?= $a['role'] === 'mentor' ? 'user-graduate' : 'user' ?> me-1"></i>
                        <?= ucfirst(htmlspecialchars($a['role'])) ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="empty-state">
            <i class="fas fa-book"></i>
            <p>You're not associated with any subjects yet.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Conversations Tab -->
    <div class="tab-pane fade" id="conversations">
      <!-- Active Conversations -->
      <?php if ($openAsMentee || $openAsMentor): ?>
        <div class="section-card">
          <h4><i class="fas fa-bolt me-2 text-success"></i>Active Conversations</h4>
          
          <?php if ($openAsMentee): ?>
            <h5 class="mt-3 mb-2">As Mentee</h5>
            <div class="list-group mb-3">
              <?php foreach ($openAsMentee as $c): ?>
                <a href="Topchat_mentee.php?chat_id=<?= $c['chat_id'] ?>" 
                   class="list-group-item list-group-item-action chat-item">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <strong><?= htmlspecialchars($c['subject_name']) ?></strong>
                      <span class="text-muted">with <?= htmlspecialchars($c['mentor_name']) ?></span>
                      <br>
                      <small class="text-muted">Started: <?= $c['opened_at'] ?></small>
                    </div>
                    <i class="fas fa-chevron-right text-warning"></i>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          
          <?php if ($openAsMentor): ?>
            <h5 class="mt-3 mb-2">As Mentor</h5>
            <div class="list-group">
              <?php foreach ($openAsMentor as $c): ?>
                <a href="Topchat_mentor.php?chat_id=<?= $c['chat_id'] ?>" 
                   class="list-group-item list-group-item-action chat-item">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <strong><?= htmlspecialchars($c['subject_name']) ?></strong>
                      <span class="text-muted">with <?= htmlspecialchars($c['mentee_name']) ?></span>
                      <br>
                      <small class="text-muted">Started: <?= $c['opened_at'] ?></small>
                    </div>
                    <i class="fas fa-chevron-right text-warning"></i>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
      
      <!-- Closed Conversations -->
      <div class="section-card">
        <h4><i class="fas fa-archive me-2 text-secondary"></i>Closed Conversations</h4>
        <?php if ($closedAsMentee || $closedAsMentor): ?>
          <p class="text-muted small">Showing last 5 conversations in each category</p>
          
          <?php if ($closedAsMentee): ?>
            <h5 class="mt-3 mb-2">As Mentee</h5>
            <div class="list-group mb-3">
              <?php foreach ($closedAsMentee as $c): ?>
                <a href="Topchat_mentee.php?chat_id=<?= $c['chat_id'] ?>" 
                   class="list-group-item list-group-item-action chat-item">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <strong><?= htmlspecialchars($c['subject_name']) ?></strong>
                      <span class="text-muted">with <?= htmlspecialchars($c['mentor_name']) ?></span>
                      <br>
                      <small class="text-muted">Closed: <?= $c['closed_at'] ?></small>
                    </div>
                    <i class="fas fa-eye text-muted"></i>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          
          <?php if ($closedAsMentor): ?>
            <h5 class="mt-3 mb-2">As Mentor</h5>
            <div class="list-group">
              <?php foreach ($closedAsMentor as $c): ?>
                <a href="Topchat_mentor.php?chat_id=<?= $c['chat_id'] ?>" 
                   class="list-group-item list-group-item-action chat-item">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <strong><?= htmlspecialchars($c['subject_name']) ?></strong>
                      <span class="text-muted">with <?= htmlspecialchars($c['mentee_name']) ?></span>
                      <br>
                      <small class="text-muted">Closed: <?= $c['closed_at'] ?></small>
                    </div>
                    <i class="fas fa-eye text-muted"></i>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <div class="empty-state">
            <i class="fas fa-archive"></i>
            <p>No closed conversations yet.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Ratings Tab -->
    <div class="tab-pane fade" id="ratings">
      <div class="section-card">
        <h4><i class="fas fa-star me-2 text-warning"></i>Your Ratings as Mentor</h4>
        
        <?php if ($ratings): ?>
          <div class="mb-4 p-3 bg-light rounded">
            <div class="row text-center">
              <div class="col-md-6">
                <h3 class="text-warning"><?= number_format($stats['avg_rating'], 1) ?>/5</h3>
                <div class="rating-stars mb-2">
                  <?php for($i = 1; $i <= 5; $i++): ?>
                    <?php if($i <= round($stats['avg_rating'])): ?>
                      <i class="fas fa-star"></i>
                    <?php else: ?>
                      <i class="far fa-star"></i>
                    <?php endif; ?>
                  <?php endfor; ?>
                </div>
                <p class="text-muted">Average Rating</p>
              </div>
              <div class="col-md-6">
                <h3 class="text-warning"><?= $stats['total_ratings'] ?></h3>
                <p class="text-muted">Total Reviews</p>
              </div>
            </div>
          </div>
          
          <h5 class="mb-3">Recent Reviews</h5>
          <?php foreach ($ratings as $r): ?>
            <div class="card mb-3">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <div class="rating-stars mb-2">
                      <?php for($i = 1; $i <= 5; $i++): ?>
                        <?php if($i <= $r['score']): ?>
                          <i class="fas fa-star"></i>
                        <?php else: ?>
                          <i class="far fa-star"></i>
                        <?php endif; ?>
                      <?php endfor; ?>
                      <span class="ms-2 text-muted"><?= $r['score'] ?>/5</span>
                    </div>
                    <?php if($r['comment']): ?>
                      <p class="mb-2">"<?= htmlspecialchars($r['comment']) ?>"</p>
                    <?php endif; ?>
                    <small class="text-muted">
                      <i class="fas fa-user me-1"></i><?= htmlspecialchars($r['mentee_name']) ?>
                      • <i class="fas fa-book me-1"></i><?= htmlspecialchars($r['subject_name']) ?>
                      • <?= $r['rating_date'] ?>
                    </small>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state">
            <i class="fas fa-star"></i>
            <p>You haven't received any ratings yet.</p>
            <small>Start mentoring to receive feedback!</small>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>

<div id="modal-container"></div>
<div id="footer-placeholder"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
<script>
  function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = event.target.querySelector('i') || event.target;
    
    if (field.type === 'password') {
      field.type = 'text';
      icon.classList.remove('fa-eye');
      icon.classList.add('fa-eye-slash');
    } else {
      field.type = 'password';
      icon.classList.remove('fa-eye-slash');
      icon.classList.add('fa-eye');
    }
  }
</script>