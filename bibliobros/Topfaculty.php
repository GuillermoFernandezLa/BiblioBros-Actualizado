<?php
require_once __DIR__ . '/auth_guard.php'; // login + DB, session

$facultyId = isset($_GET['faculty_id']) ? (int) $_GET['faculty_id'] : 0;
if ($facultyId <= 0) {
    header('Location: Topdashboard.php');
    exit;
}

// Fetch faculty details
$stmt = $pdo->prepare("
    SELECT name, description
    FROM faculties
    WHERE id = :fid
    LIMIT 1
");
$stmt->execute(['fid' => $facultyId]);
$faculty = $stmt->fetch();

if (!$faculty) {
    header('Location: Topdashboard.php');
    exit;
}
$facultyName = htmlspecialchars($faculty['name']);
$facultyDesc = htmlspecialchars($faculty['description']);

// Fetch subjects with optional course codes (if your DB has them)
$stmt2 = $pdo->prepare("
    SELECT id, name, code
    FROM subjects
    WHERE faculty_id = :fid
    ORDER BY name
");
$stmt2->execute(['fid' => $facultyId]);
$subjects = $stmt2->fetchAll();

// Fetch top mentors
$stmt3 = $pdo->prepare("
    SELECT 
      u.id,
      u.fullname,
      COALESCE(ROUND(AVG(r.score), 2),0) AS avg_score,
      COUNT(r.id) AS num_ratings
    FROM mentor_subject ms
    JOIN users u ON u.id = ms.user_id
    JOIN subjects s ON s.id = ms.subject_id
    LEFT JOIN requests req ON req.mentor_id = u.id AND req.status = 'accepted'
    LEFT JOIN chats c ON c.request_id = req.id
    LEFT JOIN ratings r ON r.chat_id = c.id
    WHERE s.faculty_id = :fid
    GROUP BY u.id
    ORDER BY avg_score DESC, num_ratings DESC
    LIMIT 6
");
$stmt3->execute(['fid' => $facultyId]);
$mentors = $stmt3->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>BiblioBros – <?= $facultyName ?></title>
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
    
    .subject-card {
      transition: all 0.3s ease;
    }
    
    .subject-card.hidden {
      display: none !important;
    }
    
    .subject-card .card {
      height: 100%;
      border: 2px solid #e9ecef;
      transition: all 0.3s ease;
      background: white;
    }
    
    .subject-card .card:hover {
      border-color: #ffc107;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .subject-code {
      color: #6c757d;
      font-size: 0.875rem;
      font-weight: 500;
      margin-bottom: 0.25rem;
    }
    
    .subject-name {
      color: #212529;
      margin-bottom: 1rem;
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
    
    .tabs-nav {
      border-bottom: 2px solid #e9ecef;
      margin-bottom: 2rem;
    }
    
    .tab-btn {
      background: none;
      border: none;
      padding: 0.75rem 1.5rem;
      color: #6c757d;
      font-weight: 500;
      border-bottom: 3px solid transparent;
      margin-bottom: -2px;
      transition: all 0.3s ease;
    }
    
    .tab-btn:hover {
      color: #495057;
    }
    
    .tab-btn.active {
      color: #ffc107;
      border-bottom-color: #ffc107;
    }
    
    .results-count {
      color: #6c757d;
      font-size: 0.9rem;
      margin-bottom: 1rem;
      display: none;
    }
    
    .results-count.show {
      display: block;
    }
  </style>
</head>

<body class="d-flex flex-column min-vh-100">

  <!-- Navbar -->
  <div id="navbar-placeholder"></div>

  <!-- Main Content -->
  <main class="container py-5 flex-grow-1">

    <!-- Faculty name & description -->
    <section class="mb-4">
      <h2 class="section-title"><?= $facultyName ?></h2>
      <?php if ($facultyDesc): ?>
        <p class="lead"><?= $facultyDesc ?></p>
      <?php endif; ?>
    </section>

    <!-- Navigation tabs -->
    <div class="tabs-nav">
      <button class="tab-btn active" onclick="showSection('subjects')">
        <i class="fas fa-book me-2"></i>Subjects
      </button>
      <button class="tab-btn" onclick="showSection('mentors')">
        <i class="fas fa-user-graduate me-2"></i>Top Mentors
      </button>
    </div>

    <!-- Subjects tab -->
    <section id="subjects" class="mb-5">
      <!-- Search bar -->
      <div class="search-container">
        <div class="position-relative">
          <i class="fas fa-search search-icon"></i>
          <input 
            type="text" 
            id="subject-search" 
            class="form-control search-input" 
            placeholder="Search subjects by name or code..."
            autocomplete="off"
          >
          <button type="button" class="clear-search" id="clearSearch" aria-label="Clear search">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="results-count" id="resultsCount"></div>
      </div>

      <!-- Subjects grid -->
      <div id="subjects-list" class="row g-3">
        <?php if (empty($subjects)): ?>
          <div class="col-12">
            <p class="text-center text-muted">No subjects available in this faculty.</p>
          </div>
        <?php else: ?>
          <?php foreach ($subjects as $sub): ?>
            <div class="col-md-4 subject-card" 
                 data-subject-name="<?= strtolower(htmlspecialchars($sub['name'])) ?>"
                 data-subject-code="<?= strtolower(htmlspecialchars($sub['code'] ?? '')) ?>">
              <div class="card p-3">
                <?php if (!empty($sub['code'])): ?>
                  <div class="subject-code">
                    <i class="fas fa-tag me-1"></i><?= htmlspecialchars($sub['code']) ?>
                  </div>
                <?php endif; ?>
                <h5 class="subject-name"><?= htmlspecialchars($sub['name']) ?></h5>
                <a href="Topsubject.php?subject_id=<?= $sub['id'] ?>" class="btn btn-warning btn-sm">
                  <i class="fas fa-arrow-right me-1"></i>Go to Subject
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      
      <!-- No results message -->
      <div id="noResults" class="no-results">
        <i class="fas fa-search fa-3x mb-3 text-muted"></i>
        <h5>No subjects found</h5>
        <p>Try adjusting your search terms or <a href="#" id="clearFromNoResults">clear the search</a></p>
      </div>
    </section>

    <!-- Mentors tab -->
    <section id="mentors" class="d-none">
      <h4 class="text-warning mb-3">Top Mentors in <?= $facultyName ?></h4>
      <div class="row g-3">
        <?php if (empty($mentors)): ?>
          <div class="col-12">
            <p class="text-muted">No mentors available yet in this faculty.</p>
          </div>
        <?php else: ?>
          <?php foreach ($mentors as $m): ?>
            <div class="col-md-4">
              <div class="card p-3">
                <h5 class="mb-2">
                  <i class="fas fa-user-graduate me-2 text-warning"></i>
                  <?= htmlspecialchars($m['fullname']) ?>
                </h5>
                <p class="mb-1">
                  <span class="text-warning">
                    <?php for($i = 0; $i < floor($m['avg_score']); $i++): ?>
                      <i class="fas fa-star"></i>
                    <?php endfor; ?>
                    <?php if($m['avg_score'] - floor($m['avg_score']) >= 0.5): ?>
                      <i class="fas fa-star-half-alt"></i>
                    <?php endif; ?>
                    <?php for($i = ceil($m['avg_score']); $i < 5; $i++): ?>
                      <i class="far fa-star"></i>
                    <?php endfor; ?>
                  </span>
                  <span class="ms-2"><?= number_format($m['avg_score'], 1) ?></span>
                </p>
                <small class="text-muted">
                  Based on <?= $m['num_ratings'] ?> rating<?= $m['num_ratings'] != 1 ? 's' : '' ?>
                </small>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

  </main>

  <div id="modal-container"></div>
  <div id="footer-placeholder"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/main.js"></script>
  <script>
    // Tab switching
    function showSection(id) {
      // Hide all sections
      document.getElementById('subjects').classList.toggle('d-none', id !== 'subjects');
      document.getElementById('mentors').classList.toggle('d-none', id !== 'mentors');
      
      // Update active tab
      document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
      });
      event.target.classList.add('active');
    }

    // Search functionality
    const searchInput = document.getElementById('subject-search');
    const clearBtn = document.getElementById('clearSearch');
    const noResults = document.getElementById('noResults');
    const resultsCount = document.getElementById('resultsCount');
    const clearFromNoResults = document.getElementById('clearFromNoResults');
    
    function filterSubjects() {
      const searchTerm = searchInput.value.toLowerCase().trim();
      const cards = document.querySelectorAll('.subject-card');
      let visibleCount = 0;
      
      cards.forEach(card => {
        const name = card.dataset.subjectName;
        const code = card.dataset.subjectCode;
        const matches = name.includes(searchTerm) || code.includes(searchTerm);
        
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
        resultsCount.textContent = `Found ${visibleCount} subject${visibleCount !== 1 ? 's' : ''}`;
      } else {
        clearBtn.classList.remove('show');
        resultsCount.classList.remove('show');
      }
      
      // Show/hide no results message
      if (visibleCount === 0 && searchTerm.length > 0) {
        noResults.classList.add('show');
      } else {
        noResults.classList.remove('show');
      }
    }
    
    function clearSearch() {
      searchInput.value = '';
      clearBtn.classList.remove('show');
      resultsCount.classList.remove('show');
      noResults.classList.remove('show');
      document.querySelectorAll('.subject-card').forEach(card => {
        card.classList.remove('hidden');
      });
      searchInput.focus();
    }
    
    // Event listeners
    searchInput.addEventListener('input', filterSubjects);
    searchInput.addEventListener('keyup', function(e) {
      if (e.key === 'Escape') {
        clearSearch();
      }
    });
    
    clearBtn.addEventListener('click', clearSearch);
    clearFromNoResults.addEventListener('click', function(e) {
      e.preventDefault();
      clearSearch();
    });
  </script>
</body>

</html>