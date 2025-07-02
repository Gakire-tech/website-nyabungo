<?php
// public/evenements.php
$pageTitle = "Salons Privés & Événements";

require_once __DIR__ . '/includes/header.php';

$evenementsPresentation = 'NYABUNGO vous propose des salons privés élégants et modulables pour vos réceptions, dîners d\'affaires, anniversaires, cocktails, et événements sur-mesure. Profitez d\'un cadre raffiné, d\'un service personnalisé et d\'une cuisine d\'exception.';
$evenementsTypes = 'Dîners d\'affaires, Réunions professionnelles, Anniversaires, Réceptions privées, Cocktails & afterworks, Événements sur-mesure';

$selectedBranchId = $_GET['branch_id'] ?? null;

try {
    $stmt = $pdo->prepare("SELECT block, content FROM site_content WHERE page = 'evenements' AND status = 'published'");
    $stmt->execute();
    $pageContents = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $evenementsPresentation = $pageContents['presentation'] ?? $evenementsPresentation;
    $evenementsTypes = $pageContents['types'] ?? $evenementsTypes;

    // Récupérer les branches pour le filtre
    $stmtBranches = $pdo->prepare("SELECT id, name FROM branches ORDER BY name ASC");
    $stmtBranches->execute();
    $branches = $stmtBranches->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les images des événements depuis la base de données avec filtre par branche
    $sqlImages = "SELECT image_path, alt_text FROM event_images";
    if ($selectedBranchId && is_numeric($selectedBranchId)) {
        $sqlImages .= " WHERE branch_id = :branch_id";
    }
    $sqlImages .= " ORDER BY display_order ASC LIMIT 4";

    $stmtImages = $pdo->prepare($sqlImages);
    if ($selectedBranchId && is_numeric($selectedBranchId)) {
        $stmtImages->bindValue(':branch_id', $selectedBranchId, PDO::PARAM_INT);
    }
    $stmtImages->execute();
    $eventImages = $stmtImages->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database error for evenements page: " . $e->getMessage());
    $branches = [];
    $eventImages = []; // Assurez-vous que $eventImages est vide en cas d'erreur
}

?>
  <main class="flex-fill p-4 container" style="max-width: 1100px; margin-top: 120px;">
    <!-- Présentation des espaces -->
    <section class="mb-5">
      <h1 class="section-title text-center mb-4">Salons Privés & Événements</h1>
      <h2 class="h4 fw-bold mb-3">Des espaces d'exception pour vos événements</h2>
      <p class="mb-4 section-text"><?php echo $evenementsPresentation; ?></p>

      <!-- Sélecteur de branche -->
      <div class="mb-4 d-flex justify-content-center">
        <form action="evenements.php" method="GET" class="d-flex align-items-center">
          <label for="branch_select" class="form-label me-2 mb-0 fw-bold">Filtrer par branche :</label>
          <select name="branch_id" id="branch_select" class="form-select w-auto">
            <option value="">Toutes les branches</option>
            <?php foreach ($branches as $branch): ?>
              <option value="<?php echo htmlspecialchars($branch['id']); ?>" <?php echo ($selectedBranchId == $branch['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($branch['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </form>
      </div>

      <div class="event-gallery-container" id="event-gallery-display">
        <div class="row flex-nowrap g-4">
        <?php if (!empty($eventImages)): ?>
            <?php foreach ($eventImages as $image): ?>
                <div class="col-6 col-md-3">
                    <img src="../assets/uploads/events/<?php echo htmlspecialchars($image['image_path']); ?>" alt="<?php echo htmlspecialchars($image['alt_text']); ?>" class="rounded object-fit-cover w-100 event-gallery-img">
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Images de fallback si la base de données est vide ou en cas d'erreur -->
            <div class="col-6 col-md-3"><img src="../assets/salon1.jpg" alt="Salon privé 1" class="rounded object-fit-cover w-100 event-gallery-img"></div>
            <div class="col-6 col-md-3"><img src="../assets/salon2.jpg" alt="Salon privé 2" class="rounded object-fit-cover w-100 event-gallery-img"></div>
            <div class="col-6 col-md-3"><img src="../assets/salon3.jpg" alt="Salon privé 3" class="rounded object-fit-cover w-100 event-gallery-img"></div>
            <div class="col-6 col-md-3"><img src="../assets/salon4.jpg" alt="Salon privé 4" class="rounded object-fit-cover w-100 event-gallery-img"></div>
        <?php endif; ?>
        </div>
      </div>
    </section>
    <!-- Types d'événements et Formulaire de demande de privatisation en parallèle -->
    <div class="row mb-5">
      <div class="col-md-6 mb-4 mb-md-0">
        <section>
          <h2 class="h4 fw-bold mb-3">Types d'événements accueillis</h2>
          <ul class="list-group list-group-flush mb-4">
            <?php
              $evenementsTypesItems = explode(', ', $evenementsTypes);
              foreach ($evenementsTypesItems as $item) {
                  echo '<li class="list-group-item">' . htmlspecialchars($item) . '</li>';
              }
            ?>
          </ul>
        </section>
      </div>
      <div class="col-md-6">
        <section>
          <h2 class="h4 fw-bold mb-4">Demande de privatisation</h2>
          <form id="event-form" class="bg-white rounded shadow-sm p-4 w-100">
            <div class="mb-3">
              <label class="form-label fw-bold">Type d'événement</label>
              <select name="event_type" class="form-select" required>
                <option value="">Choisir...</option>
                <option>Dîner d'affaires</option>
                <option>Anniversaire</option>
                <option>Réception privée</option>
                <option>Cocktail</option>
                <option>Autre</option>
              </select>
            </div>
            <div class="row g-3 mb-3">
              <div class="col">
                <label class="form-label fw-bold">Nombre d'invités</label>
                <input type="number" name="guests" min="2" max="100" class="form-control" required>
              </div>
              <div class="col">
                <label class="form-label fw-bold">Date souhaitée</label>
                <input type="date" name="requested_date" class="form-control" required>
              </div>
            </div>
            <div class="row g-3 mb-3">
              <div class="col">
                <label class="form-label fw-bold">Nom</label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div class="col">
                <label class="form-label fw-bold">Téléphone</label>
                <input type="tel" name="phone" class="form-control" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Email</label>
              <input type="email" name="email" class="form-control">
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Message / Précisions</label>
              <textarea name="message" class="form-control" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-dark w-100 fw-bold">Envoyer la demande</button>
            <div id="event-message" class="mt-3 text-center fw-bold"></div>
          </form>
        </section>
      </div>
    </div>
  </main>
<?php
require_once __DIR__ . '/includes/footer.php';
?>
  <script>
    document.getElementById('event-form').addEventListener('submit', async function(e) {
      e.preventDefault();
      const form = e.target;
      const data = {
        event_type: form.event_type.value,
        guests: form.guests.value,
        requested_date: form.requested_date.value,
        name: form.name.value,
        phone: form.phone.value,
        email: form.email.value,
        message: form.message.value
      };
      if (!data.event_type || !data.guests || !data.requested_date || !data.name || !data.phone) {
        showEventMsg('Veuillez remplir tous les champs obligatoires.', 'text-danger');
        return;
      }
      showEventMsg('Envoi en cours...', 'text-secondary');
      try {
        const res = await fetch('/api/events', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.status === 'ok') {
          showEventMsg('Votre demande a bien été envoyée. Nous vous contacterons rapidement.', 'text-success');
          form.reset();
        } else {
          showEventMsg(result.message || 'Erreur lors de l\'envoi.', 'text-danger');
        }
      } catch (err) {
        showEventMsg('Erreur lors de l\'envoi.', 'text-danger');
      }
    });
    function showEventMsg(msg, cls) {
      const el = document.getElementById('event-message');
      el.textContent = msg;
      el.className = 'mt-3 text-center fw-bold ' + cls;
    }

    // Script pour le filtrage AJAX des images d'événements
    const branchSelect = document.getElementById('branch_select');
    const eventGallery = document.getElementById('event-gallery-display');

    async function loadEventImages(branchId) {
      try {
        // Construire l'URL de l'API
        let url = '../api/get_event_images.php';
        if (branchId) {
          url += `?branch_id=${branchId}`;
        }

        const response = await fetch(url);
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        const images = await response.json();

        // Mettre à jour la galerie d'images
        let imagesHtml = '';
        if (images.length > 0) {
          imagesHtml = '<div class="row flex-nowrap g-4">\n';
          images.forEach(image => {
            imagesHtml += `
              <div class="col-6 col-md-3">
                  <img src="../assets/uploads/events/${image.image_path}" alt="${image.alt_text}" class="rounded object-fit-cover w-100 event-gallery-img">
              </div>
            `;
          });
          imagesHtml += '</div>';
        } else {
          imagesHtml = '<p class="text-center col-12">Aucune image disponible pour cette sélection.</p>';
        }
        eventGallery.innerHTML = imagesHtml;

      } catch (error) {
        console.error('Erreur lors du chargement des images d\'événements:', error);
        eventGallery.innerHTML = '<p class="text-center col-12 text-danger">Erreur lors du chargement des images.</p>';
      }
    }

    // Charger les images initiales lors du chargement de la page
    loadEventImages(branchSelect.value);

    // Écouter les changements sur le sélecteur de branche
    branchSelect.addEventListener('change', (event) => {
      loadEventImages(event.target.value);
    });

  </script> 