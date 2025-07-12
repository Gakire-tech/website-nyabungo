<?php
// public/includes/footer.php
// Cette partie sera incluse dans toutes les pages publiques
?>
  <footer class="footer text-center mt-5">
    <img src="../assets/logo.jpg" alt="NYABUNGO Logo" class="footer-logo-img mb-3">
    <div class="footer-socials">
      <a href="https://facebook.com/nyabungo" target="_blank"><i class="bi bi-facebook"></i></a>
      <a href="https://instagram.com/nyabungo" target="_blank"><i class="bi bi-instagram"></i></a>
      <a href="https://twitter.com/nyabungo" target="_blank"><i class="bi bi-twitter"></i></a>
      <a href="https://wa.me/25761234567" target="_blank"><i class="bi bi-whatsapp"></i></a>
    </div>
    <div class="footer-copyright">&copy; 2025 NYABUNGO RESTAURANT & BAR</div>
  </footer>
  <!-- Bootstrap JS Bundle (inclut Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Smart sticky header (hide on scroll down, show on scroll up)
    (function() {
      const navbar = document.getElementById('mainNavbar');
      let lastScrollY = window.scrollY;
      let ticking = false;
      function onScroll() {
        const currentScrollY = window.scrollY;
        if (currentScrollY > 40) {
          navbar.classList.add('scrolled');
        } else {
          navbar.classList.remove('scrolled');
        }
        if (currentScrollY > lastScrollY && currentScrollY > 120) {
          // Scroll down
          navbar.classList.add('hide-up');
        } else {
          // Scroll up
          navbar.classList.remove('hide-up');
        }
        lastScrollY = currentScrollY;
        ticking = false;
      }
      window.addEventListener('scroll', function() {
        if (!ticking) {
          window.requestAnimationFrame(onScroll);
          ticking = true;
        }
      });
    })();
  </script>
</body>
</html> 