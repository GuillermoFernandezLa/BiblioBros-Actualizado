<?php
/*
 * navbar.php - Enhanced with full navigation
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is authenticated
$isAuthenticated = isset($_SESSION['user_id']);

// detect current script
$page = basename($_SERVER['SCRIPT_NAME']);

// Pages that don't require authentication
$publicPages = [
  'Topindex.php',
  'Toplogin.php',
  'Topregister.php'
];

$isPublicPage = in_array($page, $publicPages, true);

$darkPages = [
  'Topsubject_mentor.php',
  'Topsubject_mentor_intro.php',
  'Topchat_mentor.php',
];

$isDark = in_array($page, $darkPages, true);

$navClass = 'navbar navbar-expand-lg sticky-top ' 
  . ($isDark ? 'navbar-dark bg-dark' : 'navbar-light bg-light');

$logoutBtnClass = $isDark
  ? 'btn btn-primary'
  : 'btn btn-secondary';

// If authenticated, fetch user's faculties for quick access
$userFaculties = [];
if ($isAuthenticated && isset($_SESSION['university_id'])) {
    require_once __DIR__ . '/config.php';
    $stmt = $pdo->prepare("
        SELECT id, name 
        FROM faculties 
        WHERE university_id = :uni_id 
        ORDER BY name 
        LIMIT 10
    ");
    $stmt->execute(['uni_id' => $_SESSION['university_id']]);
    $userFaculties = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get user's active subjects for quick navigation
$userSubjects = [];
if ($isAuthenticated && isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/config.php';
    $stmt = $pdo->prepare("
        SELECT DISTINCT s.id, s.name, usr.role, f.name as faculty_name
        FROM user_subject_role usr
        JOIN subjects s ON s.id = usr.subject_id
        JOIN faculties f ON f.id = s.faculty_id
        WHERE usr.user_id = :uid
        ORDER BY s.name
        LIMIT 10
    ");
    $stmt->execute(['uid' => $_SESSION['user_id']]);
    $userSubjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<style>
    .navbar-nav .dropdown-menu {
        border: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-radius: 8px;
        padding: 0.5rem 0;
    }
    
    .navbar-nav .dropdown-item {
        padding: 0.5rem 1.5rem;
        transition: all 0.3s ease;
    }
    
    .navbar-nav .dropdown-item:hover {
        background-color: #fff8e1;
        color: #ffc107;
        padding-left: 1.75rem;
    }
    
    .dropdown-divider {
        margin: 0.25rem 0;
    }
    
    .dropdown-header {
        font-size: 0.875rem;
        color: #6c757d;
        padding: 0.5rem 1.5rem;
        font-weight: 600;
    }
    
    .nav-badge {
        display: inline-block;
        padding: 0.15rem 0.4rem;
        font-size: 0.7rem;
        background: #ffc107;
        color: #333;
        border-radius: 10px;
        margin-left: 0.5rem;
    }
    
    .navbar-dark .dropdown-menu {
        background-color: #343a40;
        color: white;
    }
    
    .navbar-dark .dropdown-item {
        color: rgba(255,255,255,0.9);
    }
    
    .navbar-dark .dropdown-item:hover {
        background-color: #495057;
        color: #ffc107;
    }
    
    .navbar-dark .dropdown-divider {
        border-color: rgba(255,255,255,0.1);
    }
    
    .navbar-dark .dropdown-header {
        color: rgba(255,255,255,0.6);
    }
    
    .quick-access-menu {
        min-width: 250px;
    }
</style>

<nav class="<?= $navClass ?>">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="<?= $isAuthenticated ? 'Topdashboard.php' : 'Topindex.php' ?>">
      <img src="assets/img/logo.png" class="site-logo logo-dark" alt="BiblioBros Logo">
      <img src="assets/img/invlogo.png" class="site-logo logo-light" alt="BiblioBros Logo Inverted">
      <span class="ms-3">BiblioBros</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <?php if ($isAuthenticated): ?>
          <!-- Dashboard -->
          <li class="nav-item">
            <a class="nav-link" href="Topdashboard.php">
              <i class="fas fa-home me-1"></i>Dashboard
            </a>
          </li>
          
          <!-- Faculties Dropdown -->
          <?php if (!empty($userFaculties)): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="facultiesDropdown" role="button" 
               data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fas fa-building me-1"></i>Faculties
            </a>
            <ul class="dropdown-menu quick-access-menu" aria-labelledby="facultiesDropdown">
              <li><h6 class="dropdown-header">Your Faculties</h6></li>
              <?php foreach($userFaculties as $faculty): ?>
                <li>
                  <a class="dropdown-item" href="Topfaculty.php?faculty_id=<?= $faculty['id'] ?>">
                    <i class="fas fa-arrow-right me-2 text-warning"></i>
                    <?= htmlspecialchars($faculty['name']) ?>
                  </a>
                </li>
              <?php endforeach; ?>
              <?php if (count($userFaculties) >= 10): ?>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item text-center" href="Topdashboard.php">
                    <small>View all faculties</small>
                  </a>
                </li>
              <?php endif; ?>
            </ul>
          </li>
          <?php endif; ?>
          
          <!-- My Subjects Dropdown -->
          <?php if (!empty($userSubjects)): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="subjectsDropdown" role="button" 
               data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fas fa-book me-1"></i>My Subjects
            </a>
            <ul class="dropdown-menu quick-access-menu" aria-labelledby="subjectsDropdown">
              <?php 
              $mentorSubjects = array_filter($userSubjects, fn($s) => $s['role'] === 'mentor');
              $menteeSubjects = array_filter($userSubjects, fn($s) => $s['role'] === 'mentee');
              ?>
              
              <?php if (!empty($mentorSubjects)): ?>
                <li><h6 class="dropdown-header">As Mentor</h6></li>
                <?php foreach($mentorSubjects as $subject): ?>
                  <li>
                    <a class="dropdown-item" href="Topsubject_mentor.php?subject_id=<?= $subject['id'] ?>">
                      <i class="fas fa-user-graduate me-2 text-warning"></i>
                      <?= htmlspecialchars($subject['name']) ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              <?php endif; ?>
              
              <?php if (!empty($mentorSubjects) && !empty($menteeSubjects)): ?>
                <li><hr class="dropdown-divider"></li>
              <?php endif; ?>
              
              <?php if (!empty($menteeSubjects)): ?>
                <li><h6 class="dropdown-header">As Mentee</h6></li>
                <?php foreach($menteeSubjects as $subject): ?>
                  <li>
                    <a class="dropdown-item" href="Topsubject_mentee.php?subject_id=<?= $subject['id'] ?>">
                      <i class="fas fa-user me-2 text-info"></i>
                      <?= htmlspecialchars($subject['name']) ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              <?php endif; ?>
            </ul>
          </li>
          <?php endif; ?>
          
          <!-- Chats Dropdown -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="chatsDropdown" role="button" 
               data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fas fa-comments me-1"></i>Chats
              <?php
              // Optional: Show active chats count
              if ($isAuthenticated) {
                  $stmt = $pdo->prepare("
                    SELECT COUNT(*) as count FROM chats c
                    JOIN requests r ON r.id = c.request_id
                    WHERE (r.mentee_id = :uid OR r.mentor_id = :uid)
                    AND c.active = TRUE
                  ");
                  $stmt->execute(['uid' => $_SESSION['user_id']]);
                  $activeCount = $stmt->fetchColumn();
                  if ($activeCount > 0) {
                      echo '<span class="nav-badge">' . $activeCount . '</span>';
                  }
              }
              ?>
            </a>
            <ul class="dropdown-menu quick-access-menu" aria-labelledby="chatsDropdown">
              <li><h6 class="dropdown-header">Quick Access</h6></li>
              <li>
                <a class="dropdown-item" href="Topprofile.php#conversations">
                  <i class="fas fa-bolt me-2 text-success"></i>
                  Active Conversations
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="Topprofile.php#conversations">
                  <i class="fas fa-archive me-2 text-secondary"></i>
                  Closed Conversations
                </a>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item" href="Topprofile.php#ratings">
                  <i class="fas fa-star me-2 text-warning"></i>
                  My Ratings
                </a>
              </li>
            </ul>
          </li>
          
          <!-- Profile -->
          <li class="nav-item">
            <a class="nav-link" href="Topprofile.php">
              <i class="fas fa-user me-1"></i>Profile
            </a>
          </li>
          
          <!-- Logout -->
          <li class="nav-item">
            <a
              href="#"
              id="logout-button"
              data-bs-toggle="modal"
              data-bs-target="#logoutModal"
              class="<?= $logoutBtnClass ?> ms-3"
            >
              <i class="fas fa-sign-out-alt me-1"></i>Log out
            </a>
          </li>
        <?php else: ?>
          <!-- Public navigation -->
          <?php if (!$isPublicPage || $page === 'Topindex.php'): ?>
            <li class="nav-item">
              <a class="nav-link" href="Topindex.php">Home</a>
            </li>
          <?php endif; ?>
          <li class="nav-item">
            <a class="nav-link" href="Topindex.php#features">Features</a>
          </li>
          <?php if ($page !== 'Toplogin.php'): ?>
            <li class="nav-item">
              <a class="nav-link" href="Toplogin.php">Login</a>
            </li>
          <?php endif; ?>
          <?php if ($page !== 'Topregister.php'): ?>
            <li class="nav-item">
              <a class="nav-link" href="Topregister.php">Register</a>
            </li>
          <?php endif; ?>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Breadcrumbs remain the same -->
<?php 
$breadcrumbPages = [
  'Topfaculty.php',
  'Topsubject.php',
  'Topsubject_mentee.php',
  'Topsubject_mentor.php',
  'Topsubject_mentor_intro.php',
  'Topchat_mentee.php',
  'Topchat_mentor.php',
  'rating.php'
];

if ($isAuthenticated && in_array($page, $breadcrumbPages)): 
?>
<nav aria-label="breadcrumb" class="container mt-2">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="Topdashboard.php">Dashboard</a></li>
    <?php
    switch($page) {
      case 'Topfaculty.php':
        echo '<li class="breadcrumb-item active">Faculty</li>';
        break;
      case 'Topsubject.php':
      case 'Topsubject_mentee.php':
      case 'Topsubject_mentor.php':
      case 'Topsubject_mentor_intro.php':
        echo '<li class="breadcrumb-item">Faculty</li>';
        echo '<li class="breadcrumb-item active">Subject</li>';
        break;
      case 'Topchat_mentee.php':
      case 'Topchat_mentor.php':
        echo '<li class="breadcrumb-item">Subject</li>';
        echo '<li class="breadcrumb-item active">Chat</li>';
        break;
      case 'rating.php':
        echo '<li class="breadcrumb-item">Chat</li>';
        echo '<li class="breadcrumb-item active">Rate Mentor</li>';
        break;
    }
    ?>
  </ol>
</nav>
<?php endif; ?>