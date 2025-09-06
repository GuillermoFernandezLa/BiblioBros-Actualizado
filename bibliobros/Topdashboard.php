<?php

require_once __DIR__ . '/auth_guard.php'; // starts session, connects PDO, checks auth

error_log('Session ID: ' . session_id());
error_log('User ID set: ' . ($_SESSION['user_id'] ?? 'null'));
// Fetch user info
$stmt = $pdo->prepare("
    SELECT fullname, university_id, has_seen_tutorial
    FROM users
    WHERE id = :uid
    LIMIT 1
");
$stmt->execute(['uid' => $_SESSION['user_id']]);
$user = $stmt->fetch();

$showTutorial = false;

if (!$user['has_seen_tutorial']) {
  $showTutorial = true;

  // Actualizamos la base de datos para no mostrarlo más veces
  $updateStmt = $pdo->prepare("UPDATE users SET has_seen_tutorial = TRUE WHERE id = ?");
  $updateStmt->execute([$_SESSION['user_id']]);
}

if (!$user) {
  session_destroy();
  header('Location: Toplogin.php');
  exit;
}

// Save in session if needed
$_SESSION['fullname'] = $user['fullname'];
$_SESSION['university_id'] = $user['university_id'];

// Fetch faculties for dropdown
$stmt2 = $pdo->prepare("
    SELECT id, name, description
    FROM faculties
    WHERE university_id = :uni
    ORDER BY name
");
$stmt2->execute(['uni' => $user['university_id']]);
$faculties = $stmt2->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>BiblioBros – Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />
  
  <style>
    .search-container {
      max-width: 600px;
      margin: 0 auto 2rem;
    }
    
    .search-input {
      background-color: #f8f9fa;
      border: 2px solid transparent;
      border-radius: 10px;
      padding: 12px 20px 12px 45px;
      font-size: 1rem;
      transition: all 0.3s ease;
    }
    
    .search-input:focus {
      background-color: white;
      border-color: #ffc107;
      box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.15);
      outline: none;
    }
    
    .search-icon {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #6c757d;
      pointer-events: none;
    }
    
    .faculty-card {
      transition: all 0.3s ease;
    }
    
    .faculty-card.hidden {
      display: none !important;
    }
    
    .btn-faculty {
      border: 2px solid #ffc107;
      border-radius: 10px;
      text-decoration: none;
      transition: all 0.3s ease;
      background: white;
    }
    
    .btn-faculty:hover {
      background-color: #fff8e1;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
    }
    
    .btn-faculty .h6 {
      color: #ffc107;
      font-weight: 600;
    }
    
    .no-results {
      text-align: center;
      padding: 3rem;
      color: #6c757d;
      display: none;
    }
    
    .no-results.show {
      display: block;
    }
    
    .clear-search {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      background: transparent;
      border: none;
      color: #6c757d;
      cursor: pointer;
      padding: 5px;
      display: none;
    }
    
    .clear-search:hover {
      color: #495057;
    }
    
    .clear-search.show {
      display: block;
    }
    
    .results-count {
      color: #6c757d;
      font-size: 0.9rem;
      margin-bottom: 1rem;
      display: none;
      text-align: center;
    }
    
    .results-count.show {
      display: block;
    }
    
    .faculties-section {
      position: relative;
    }
    
    aside {
      margin-top: 2rem;
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }
    
    @media (max-width: 768px) {
      .stats-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body class="d-flex flex-column min-vh-100">

  <!-- Navbar -->
  <div id="navbar-placeholder"></div>

  <!-- Main Dashboard -->
  <main class="container py-5 flex-grow-1">
    <h2 class="section-title text-center mb-4">Dashboard</h2>
    <p class="text-center fs-5 text-muted">
      Welcome, <?= htmlspecialchars($user['fullname']) ?>!
    </p>

    <div class="card mb-4 p-4 position-relative faculties-section">
      <h3 class="mb-4">Your Faculties</h3>

      <?php if (count($faculties) > 0): ?>
        <!-- Search bar for faculties -->
        <div class="search-container">
          <div class="position-relative">
            <i class="fas fa-search search-icon"></i>
            <input 
              type="text" 
              id="faculty-search" 
              class="form-control search-input" 
              placeholder="Search faculties by name..."
              autocomplete="off"
            >
            <button type="button" class="clear-search" id="clearSearch" aria-label="Clear search">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="results-count" id="resultsCount"></div>
        </div>

        <!-- Faculties grid -->
        <div class="row row-cols-1 row-cols-md-2 g-3" id="faculties-list">
          <?php foreach ($faculties as $fac): ?>
            <div class="col faculty-card" 
                 data-faculty-name="<?= strtolower(htmlspecialchars($fac['name'])) ?>">
              <a href="Topfaculty.php?faculty_id=<?= $fac['id'] ?>" class="btn-faculty w-100 text-start p-3 d-block">
                <i class="fas fa-building me-2 text-warning"></i>
                <span class="h6"><?= htmlspecialchars($fac['name']) ?></span>
                <?php if (!empty($fac['description'])): ?>
                  <small class="d-block text-muted mt-1">
                    <?= htmlspecialchars($fac['description']) ?>
                  </small>
                <?php endif; ?>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
        
        <!-- No results message -->
        <div id="noResults" class="no-results">
          <i class="fas fa-search fa-3x mb-3 text-muted"></i>
          <h5>No faculties found</h5>
          <p>Try adjusting your search terms or <a href="#" id="clearFromNoResults">clear the search</a></p>
        </div>
      <?php else: ?>
        <p class="text-muted">You don't have any assigned faculties.</p>
      <?php endif; ?>
    </div>

    <!-- Quick Stats & Announcements -->
    <div class="stats-grid">
      <div class="card p-4">
        <h3 class="mb-3"><i class="fas fa-chart-bar me-2 text-warning"></i>Quick Stats</h3>
        <ul class="list-unstyled">
          <li class="mb-2">
            <i class="fas fa-user-check me-2 text-success"></i>
            Profile Completion: <strong id="profile-completion">100%</strong>
          </li>
          <li class="mb-2">
            <i class="fas fa-question-circle me-2 text-info"></i>
            Pending Requests: <strong>0</strong>
          </li>
          <li class="mb-2">
            <i class="fas fa-comments me-2 text-primary"></i>
            Active Chats: <strong>0</strong>
          </li>
        </ul>
      </div>
      <div class="card p-4">
        <h3 class="mb-3"><i class="fas fa-bullhorn me-2 text-warning"></i>Announcements</h3>
        <p class="text-muted">No announcements at the moment.</p>
      </div>
    </div>
  </main>

  <?php if ($showTutorial): ?>
    <div class="modal fade show" id="tutorialModal" tabindex="-1" aria-labelledby="tutorialModalLabel" aria-hidden="true"
      style="display: block;" data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="tutorialModalLabel">Welcome to BiblioBros!</h5>
          </div>
          <div class="modal-body" style="max-height: 300px; overflow-y: auto;">
            <p><strong>Welcome to BiblioBros!</strong></p>

            <p>This platform helps university students request and offer academic help across various subjects.</p>

            <p><strong>🔍 Navigation:</strong> The top navigation bar is available throughout the entire platform. It
              provides quick access to your <em>Dashboard</em>, your <em>Profile</em>, and a shortcut to <em>Logout</em>.
            </p>

            <p><strong>🏛️ Structure:</strong> The app is organized by <em>public universities</em>, each containing
              several <em>faculties</em>, and each faculty offering multiple <em>subjects</em>. We've simplified the
              structure by skipping degrees — this is because many degrees share the same subjects.</p>

            <p><strong>📍 On this Dashboard:</strong> You can browse the faculties available in your university and select
              the one you want to explore. Each faculty will let you discover its subjects and request or offer mentoring
              in them.</p>

            <p><strong>💡 Tip:</strong> Use the search bar to quickly find the faculty you're looking for!</p>

            <p>Let's get started!</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" onclick="closeTutorial()">OK</button>
          </div>
        </div>
      </div>
    </div>
    <script>
      function closeTutorial() {
        const modal = document.getElementById('tutorialModal');
        modal.classList.remove('show');
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) backdrop.remove();
      }
      // Añadimos backdrop manual porque está forzado a visible al inicio
      document.addEventListener('DOMContentLoaded', () => {
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
      });
    </script>
  <?php endif; ?>


  <div id="modal-container"></div>
  <div id="footer-placeholder"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/main.js"></script>
  
  <script>
    // Search functionality for faculties
    const searchInput = document.getElementById('faculty-search');
    const clearBtn = document.getElementById('clearSearch');
    const noResults = document.getElementById('noResults');
    const resultsCount = document.getElementById('resultsCount');
    const clearFromNoResults = document.getElementById('clearFromNoResults');
    
    function filterFaculties() {
      const searchTerm = searchInput.value.toLowerCase().trim();
      const cards = document.querySelectorAll('.faculty-card');
      let visibleCount = 0;
      
      cards.forEach(card => {
        const name = card.dataset.facultyName;
        const matches = name.includes(searchTerm);
        
        if (matches) {
          card.classList.remove('hidden');
          visibleCount++;
        } else {
          card.classList.add('hidden');
        }
      });
      
      // Show/hide clear button
      if (searchTerm.length > 0) {
        clearBtn.classList.add('show');
        resultsCount.classList.add('show');
        
        const totalFaculties = cards.length;
        if (visibleCount === totalFaculties) {
          resultsCount.textContent = `Showing all ${totalFaculties} faculties`;
        } else {
          resultsCount.textContent = `Found ${visibleCount} of ${totalFaculties} faculties`;
        }
      } else {
        clearBtn.classList.remove('show');
        resultsCount.classList.remove('show');
      }
      
      // Show/hide no results message
      if (visibleCount === 0 && searchTerm.length > 0) {
        noResults.classList.add('show');
        document.getElementById('faculties-list').style.display = 'none';
      } else {
        noResults.classList.remove('show');
        document.getElementById('faculties-list').style.display = '';
      }
    }
    
    function clearSearch() {
      searchInput.value = '';
      clearBtn.classList.remove('show');
      resultsCount.classList.remove('show');
      noResults.classList.remove('show');
      document.getElementById('faculties-list').style.display = '';
      document.querySelectorAll('.faculty-card').forEach(card => {
        card.classList.remove('hidden');
      });
      searchInput.focus();
    }
    
    // Event listeners
    if (searchInput) {
      searchInput.addEventListener('input', filterFaculties);
      searchInput.addEventListener('keyup', function(e) {
        if (e.key === 'Escape') {
          clearSearch();
        }
      });
    }
    
    if (clearBtn) {
      clearBtn.addEventListener('click', clearSearch);
    }
    
    if (clearFromNoResults) {
      clearFromNoResults.addEventListener('click', function(e) {
        e.preventDefault();
        clearSearch();
      });
    }
  </script>
</body>

</html>