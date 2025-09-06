<?php
/*
 * footer.php
 *
 * This file renders the global footer, including branding and contact information.
 * It adapts to light/dark themes automatically.
 */
?>
<footer class="mt-auto py-4 text-white">
  <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between">
    <a href="Topdashboard.php">
     <img src="assets/img/logo.png"
           alt="BiblioBros Logo"
           class="site-logo logo-dark"/>

      <!-- inverted logo for dark theme -->
      <img src="assets/img/invlogo.png"
           alt="BiblioBros Logo Inverted"
           class="site-logo logo-light"/>
    </a>
    <div class="text-center text-md-end mt-3 mt-md-0">
      <p class="mb-1">
        Contact:
        <a href="mailto:contact@bibliobros.com" class="text-white text-decoration-underline">
          contact@bibliobros.com
        </a>
      </p>
      <p class="mb-0">Developed by the BiblioBros team</p>
    </div>
  </div>
</footer>