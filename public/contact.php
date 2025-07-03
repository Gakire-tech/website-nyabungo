<?php
// public/contact.php
$pageTitle = "Contact & Succursales";

require_once __DIR__ . '/includes/header.php';

$mutangaBranchInfo = [];
$mutakuraBranchInfo = [];

try {
    $pdo = getPDOConnection(); // Assurez-vous que $pdo est défini ici si ce n'est pas déjà fait par l'inclusion du header

    $stmt = $pdo->prepare("SELECT * FROM branches WHERE LOWER(TRIM(name)) IN ('mutanga', 'mutakura')");
    $stmt->execute();
    $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($branches as $branch) {
        if (strtolower(trim($branch['name'])) === 'mutanga') {
            $mutangaBranchInfo = $branch;
        } elseif (strtolower(trim($branch['name'])) === 'mutakura') {
            $mutakuraBranchInfo = $branch;
        }
    }

} catch (PDOException $e) {
    error_log("Database error for contact page: " . $e->getMessage());
    // Les variables $mutangaBranchInfo et $mutakuraBranchInfo restent des tableaux vides,
    // ou vous pourriez vouloir définir des messages d'erreur spécifiques pour l'interface utilisateur ici.
}

?>
  <main class="flex-fill p-4 container" style="max-width: 1100px; margin-top: 90px;">
    <h1 class="section-title text-center mb-4">Nos Succursales & Contact</h1>
    <div id="contact-content" class="mb-5">
      <!-- Contenu dynamique de l'API sera inséré ici -->
    </div>
    <div class="row g-4 mb-5">
      <!-- Succursale Mutanga -->
      <div class="col-12 col-md-6">
        <div class="bg-white rounded shadow-sm p-4 h-100 d-flex flex-column">
          <h2 class="h5 fw-bold mb-2">
            <?php echo htmlspecialchars($mutangaBranchInfo['name'] ?? 'Mutanga'); ?>
          </h2>
          <div class="mb-2">
            <?php echo htmlspecialchars((string)($mutangaBranchInfo['address'] ?? 'Information non disponible')); ?>
          </div>
        <div class="mb-2">
            <a href="tel:<?php echo htmlspecialchars((string)($mutangaBranchInfo['phone'] ?? '')); ?>" class="link-primary">
              <?php echo htmlspecialchars((string)($mutangaBranchInfo['phone'] ?? 'Information non disponible')); ?>
            </a>
        </div>
        <div class="mb-2">
            <a href="mailto:<?php echo htmlspecialchars((string)($mutangaBranchInfo['email'] ?? '')); ?>" class="link-primary">
              <?php echo htmlspecialchars((string)($mutangaBranchInfo['email'] ?? 'Information non disponible')); ?>
            </a>
        </div>
          <div class="mb-2 text-muted small">Horaires : <?php echo htmlspecialchars((string)($mutangaBranchInfo['opening_hours'] ?? 'Information non disponible')); ?></div>
        <div class="mb-4">
            <iframe class="location-map"  src="<?php echo htmlspecialchars($mutangaBranchInfo['google_maps_link'] ?? 'https://www.google.com/maps?q=-3.3822,29.3644&z=15&output=embed'); ?>" width="100%" height="180" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
          </div>
          <!-- <div class="row g-2">
            <div class="col-4"><img src="../assets/mutanga1.jpg" alt="Mutanga 1" class="rounded object-fit-cover w-100" style="height: 80px;"></div>
            <div class="col-4"><img src="../assets/mutanga2.jpg" alt="Mutanga 2" class="rounded object-fit-cover w-100" style="height: 80px;"></div>
            <div class="col-4"><img src="../assets/mutanga3.jpg" alt="Mutanga 3" class="rounded object-fit-cover w-100" style="height: 80px;"></div>
        </div> -->
        </div>
      </div>
      <!-- Succursale Mutakura -->
      <div class="col-12 col-md-6">
        <div class="bg-white rounded shadow-sm p-4 h-100 d-flex flex-column">
          <h2 class="h5 fw-bold mb-2">
            <?php echo htmlspecialchars($mutakuraBranchInfo['name'] ?? 'Mutakura'); ?>
          </h2>
          <div class="mb-2">
            <?php echo htmlspecialchars((string)($mutakuraBranchInfo['address'] ?? 'Information non disponible')); ?>
          </div>
        <div class="mb-2">
            <a href="tel:<?php echo htmlspecialchars((string)($mutakuraBranchInfo['phone'] ?? '')); ?>" class="link-primary">
              <?php echo htmlspecialchars((string)($mutakuraBranchInfo['phone'] ?? 'Information non disponible')); ?>
            </a>
        </div>
        <div class="mb-2">
            <a href="mailto:<?php echo htmlspecialchars((string)($mutakuraBranchInfo['email'] ?? '')); ?>" class="link-primary">
              <?php echo htmlspecialchars((string)($mutakuraBranchInfo['email'] ?? 'Information non disponible')); ?>
            </a>
        </div>
          <div class="mb-2 text-muted small">Horaires : <?php echo htmlspecialchars((string)($mutakuraBranchInfo['opening_hours'] ?? 'Information non disponible')); ?></div>
        <div class="mb-4">
            <iframe class="location-map"  src="<?php echo htmlspecialchars($mutakuraBranchInfo['google_maps_link'] ?? 'https://www.google.com/maps?q=-3.3822,29.3644&z=15&output=embed'); ?>" width="100%" height="180" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
          </div>
          <!-- <div class="row g-2">
            <div class="col-4"><img src="../assets/mutakura1.jpg" alt="Mutakura 1" class="rounded object-fit-cover w-100" style="height: 80px;"></div>
            <div class="col-4"><img src="../assets/mutakura2.jpg" alt="Mutakura 2" class="rounded object-fit-cover w-100" style="height: 80px;"></div>
            <div class="col-4"><img src="../assets/mutakura3.jpg" alt="Mutakura 3" class="rounded object-fit-cover w-100" style="height: 80px;"></div>
        </div> -->
        </div>
      </div>
    </div>
    <!-- Formulaire de contact -->
    <div class="bg-white rounded shadow-sm p-4 mx-auto mt-5" style="max-width: 480px;">
      <h2 class="h5 fw-bold mb-4">Contactez-nous</h2>
      <form id="contact-form" class="vstack gap-3">
        <div>
          <label class="form-label fw-bold">Nom</label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div>
          <label class="form-label fw-bold">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div>
          <label class="form-label fw-bold">Message</label>
          <textarea name="message" class="form-control" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-dark w-100 fw-bold">Envoyer</button>
        <div id="contact-message" class="mt-3 text-center fw-bold"></div>
      </form>
    </div>
    <!-- Réseaux sociaux -->
    <div class="d-flex justify-content-center gap-4 mt-5 mb-5">
      <a href="https://facebook.com/nyabungo" target="_blank" aria-label="Facebook" class="text-decoration-none text-primary-emphasis fs-3"><i class="bi bi-facebook"></i></a>
      <a href="https://instagram.com/nyabungo" target="_blank" aria-label="Instagram" class="text-decoration-none text-danger fs-3"><i class="bi bi-instagram"></i></a>
      <a href="https://wa.me/25761234567" target="_blank" aria-label="WhatsApp" class="text-decoration-none text-success fs-3"><i class="bi bi-whatsapp"></i></a>
    </div>
    <!-- Plan d'accès global -->
    <!-- <div class="bg-white rounded shadow-sm p-4 mx-auto mb-5" style="max-width: 700px;">
      <h2 class="h5 fw-bold mb-4">Plan d'accès général</h2>
      <iframe src="https://www.google.com/maps/d/embed?mid=1vQw8Qw8Qw8Qw8Qw8Qw8Qw8Qw8Qw8Qw8Qw8&hl=fr" width="100%" height="320" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
    </div> -->
  </main>
<?php
require_once __DIR__ . '/includes/footer.php';
?>
  <script>
    // Charger dynamiquement le contenu "contact" depuis l'API
    fetch('/api/site_content/contact')
      .then(res => res.json())
      .then(data => {
        if (data.status === 'ok' && data.data && data.data.content) {
          document.getElementById('contact-content').innerHTML = data.data.content;
        } else {
          document.getElementById('contact-content').innerHTML = '<div class="text-danger">Contenu non disponible.</div>';
        }
      })
      .catch(() => {
        document.getElementById('contact-content').innerHTML = '<div class="text-danger">Erreur de chargement du contenu.</div>';
      });
    document.getElementById('contact-form').addEventListener('submit', async function(e) {
      e.preventDefault();
      const form = e.target;
      const data = {
        name: form.name.value,
        email: form.email.value,
        message: form.message.value
      };
      if (!data.name || !data.email || !data.message) {
        showContactMsg('Veuillez remplir tous les champs.', 'text-danger');
        return;
      }
      showContactMsg('Envoi en cours...', 'text-secondary');
      try {
        const res = await fetch('/api/contact', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.status === 'ok') {
          showContactMsg('Votre message a bien été envoyé. Merci !', 'text-success');
          form.reset();
        } else {
          showContactMsg(result.message || 'Erreur lors de l\'envoi.', 'text-danger');
        }
      } catch (err) {
        showContactMsg('Erreur lors de l\'envoi.', 'text-danger');
      }
    });
    function showContactMsg(msg, cls) {
      const el = document.getElementById('contact-message');
      el.textContent = msg;
      el.className = 'mt-3 text-center fw-bold ' + cls;
    }
  </script>