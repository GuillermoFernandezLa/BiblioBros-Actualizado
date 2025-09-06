<?php
/*
 * 404.php
 *
 * Custom 404 error page.
 * Displays a user-friendly message when a page is not found, and links back to the homepage.
 */

session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>BiblioBros – Page Not Found</title>

  <!-- Bootstrap + Your styles -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />

  <style>
    .error-container {
      animation: fadeSlideUp 0.7s ease-out;
      text-align: center;
      max-width: 600px;
      margin: 0 auto;
    }

    @keyframes fadeSlideUp {
      0% {
        opacity: 0;
        transform: translateY(30px);
      }
      100% {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .error-code {
      font-size: 5rem;
      font-weight: bold;
    }

    .error-message {
      font-size: 1.25rem;
      margin-bottom: 2rem;
    }

    .error-icon {
      font-size: 4rem;
      margin-bottom: 1rem;
    }
  </style>
</head>

<body class="d-flex flex-column min-vh-100">

  <!-- Navbar -->
  <div id="navbar-placeholder"></div>

  <main class="container d-flex flex-grow-1 align-items-center justify-content-center py-5">
    <div class="error-container">

      <!-- Optional icon or emoji -->
      <div class="error-icon">📚</div>

      <div class="error-code">404</div>
      <p class="error-message">Oops, the page you’re looking for doesn’t exist or has moved.</p>

      <a href="Topindex.php" class="btn btn-secondary btn-lg">Return to Home</a>
    </div>
  </main>

  <!-- Footer and Modals -->
  <div id="modal-container"></div>
  <div id="footer-placeholder"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/main.js"></script>
</body>
</html>
