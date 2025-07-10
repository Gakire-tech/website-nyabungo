<?php
// Page d'accueil inspirée du design Le Clarence
require_once __DIR__ . '/../config/database.php';

$pdo = getPDOConnection();

$selectedBranchId = $_GET['branch_id'] ?? null; // Récupérer la branche sélectionnée
$currentMenus = [];

// Default content for static sections
$notreHistoireContent = 'Fondé en 2022, NYABUNGO RESTAURANT & BAR est né de la passion pour la gastronomie, l\'art de recevoir et le raffinement. Notre établissement s\'inspire des grandes maisons européennes tout en valorisant les saveurs et le terroir du Burundi. Nous avons à cœur d\'offrir à nos clients une expérience unique, où chaque détail compte.';
$leChefContent = 'Jean-Claude Niyonzima propose une cuisine créative, élégante et généreuse, mêlant produits locaux d\'exception et inspirations internationales. Son ambition : sublimer chaque ingrédient et faire de chaque repas un moment inoubliable.';

try {
    // Récupérer toutes les branches pour le sélecteur
    $stmtBranches = $pdo->prepare("SELECT id, name FROM branches ORDER BY name ASC");
    $stmtBranches->execute();
    $branches = $stmtBranches->fetchAll(PDO::FETCH_ASSOC);

    // Si aucune branche n'est sélectionnée, prendre la première par défaut
    if (empty($selectedBranchId) && !empty($branches)) {
        $selectedBranchId = $branches[0]['id'];
    }

    // Récupérer les items de menu pour la branche sélectionnée, limités à 4
    if ($selectedBranchId) {
        $stmt = $pdo->prepare("
            SELECT mi.image, mi.name, mi.description, mi.price
            FROM menu_items mi
            JOIN menus m ON mi.menu_id = m.id
            WHERE m.branch_id = :branch_id AND mi.is_available = TRUE
            ORDER BY mi.id
            LIMIT 4
        ");
        $stmt->execute(['branch_id' => $selectedBranchId]);
        $currentMenus = $stmt->fetchAll();
    }

    // Récupérer les informations complètes des succursales (pour Contact & Localisation)
    $mutangaBranchInfo = null; // Réinitialiser pour éviter d'utiliser d'anciennes données si non pertinentes
    $mutakuraBranchInfo = null;

    foreach ($branches as $branch) {
        if ($branch['name'] === 'Mutanga') {
            $stmt = $pdo->prepare("SELECT * FROM branches WHERE id = :id");
            $stmt->execute(['id' => $branch['id']]);
            $mutangaBranchInfo = $stmt->fetch();
        } elseif ($branch['name'] === 'Mutakura') {
            $stmt = $pdo->prepare("SELECT * FROM branches WHERE id = :id");
            $stmt->execute(['id' => $branch['id']]);
            $mutakuraBranchInfo = $stmt->fetch();
        }
    }

    // Récupérer le contenu de la page d'accueil
    $stmt = $pdo->prepare("SELECT block, content FROM site_content WHERE page = 'accueil' AND status = 'published'");
    $stmt->execute();
    $pageContents = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $notreHistoireContent = $pageContents['histoire'] ?? $notreHistoireContent;
    $leChefContent = $pageContents['chef'] ?? $leChefContent;

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $currentMenus = [];
    $branches = [];
}

$pageTitle = "Accueil";
require_once __DIR__ . '/includes/header.php';
?>
  <!-- Hero Section -->
  <header class="hero-bg">
    <div class="hero-overlay"></div>
    <div class="hero-content container">
      <div class="hero-logo">NYABUNGO</div>
      <div class="hero-subtitle">Un lieu d'exception où la gastronomie burundaise rencontre l'élégance et la convivialité.</div>
    </div>
  </header>
  <main class="flex-fill">
    <!-- Notre histoire -->
    <section class="container py-5 text-center" id="table">
      <div class="section-title"></div>
      <div class="section-text">
        <?php echo $notreHistoireContent; ?>
      </div>
    </section>
    <!-- Le chef -->
    <section class="container py-5 text-center">
      <div class="section-title">Le chef</div>
      <img src="../assets/logo1.PNG" alt="Chef Jean-Claude" class="chef-img mb-3">
      <div class="section-text">
        <?php echo $leChefContent; ?>
      </div>
    </section>
    <!-- Branches (deux photos à gauche, texte à droite) -->
    <section class="container py-5" id="branches-section">
      <div class="section-title text-center mb-4">Nos Branches</div>
      <div class="row g-4 align-items-center">
        <div class="col-12 col-lg-6 mb-4 mb-lg-0 d-flex justify-content-center align-items-center">
          <div class="row g-2 w-100">
            <div class="col-6">
              <img src="../assets/event.jpg" alt="Succursale Mutanga" class="rounded shadow-sm w-100 branch-img">
            </div>
            <div class="col-6">
              <img src="../assets/event1.jpg" alt="Succursale Mutakura" class="rounded shadow-sm w-100 branch-img">
            </div>
          </div>
        </div>
        <div class="col-12 col-lg-6">
          <div class="section-text text-lg-start text-center">
            NYABUNGO Restaurant & Bar vous accueille dans ses deux établissements uniques à Bujumbura, Mutanga et Mutakura. Chaque branche offre une atmosphère distinctive et un service impeccable pour vos repas, événements et moments de détente. Découvrez l'ambiance qui vous correspond le mieux.
          </div>
        </div>
      </div>
    </section>
    <!-- Menus du jour par branche (avec sélecteur et affichage horizontal) -->
    <section class="container py-5" id="menus-jour">
      <div class="section-title text-center mb-4">Menus du jour</div>
      
      <!-- Sélecteur de branche pour les menus -->
      <div class="mb-4 d-flex justify-content-center">
        <form action="index.php" method="GET" class="d-flex align-items-center">
          <label for="menu_branch_select" class="form-label me-2 mb-0 fw-bold">Filtrer par branche :</label>
          <select name="branch_id" id="menu_branch_select" class="form-select w-auto">
            <?php if (empty($branches)): ?>
                <option value="">Aucune branche disponible</option>
            <?php else: ?>
                <?php foreach ($branches as $branch): ?>
                    <option value="<?php echo htmlspecialchars($branch['id']); ?>" <?php echo ($selectedBranchId == $branch['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($branch['name']); ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </form>
      </div>

      <!-- Affichage des menus horizontalement -->
      <div class="menu-gallery-scroll" id="menu-gallery-display">
        <?php if (!empty($currentMenus)): ?>
          <?php foreach ($currentMenus as $menuItem): ?>
            <div class="menu-item-card d-flex flex-column align-items-center text-center p-2">
                <img src="../assets/uploads/<?php echo htmlspecialchars($menuItem['image']); ?>" alt="<?php echo htmlspecialchars($menuItem['name']); ?>" class="rounded shadow-sm menu-item-img">
                <h5 class="mt-3 mb-1 fw-bold"><?php echo htmlspecialchars($menuItem['name']); ?></h5>
                <p class="text-muted mb-1 small"><?php echo htmlspecialchars($menuItem['description']); ?></p>
                <p class="fw-bold text-gold"><?php echo htmlspecialchars(number_format($menuItem['price'], 0, ',', ' ')); ?> FBU</p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-center w-100">Aucun menu disponible pour cette branche pour le moment.</p>
        <?php endif; ?>
      </div>
    </section>
    <!-- Événementiel (texte à gauche, photo à droite) -->
    <section class="container py-5" id="evenement2">
      <div class="row align-items-center g-3 g-lg-2 flex-lg-row flex-column-reverse">
        <div class="col-12 col-lg-6">
          <div class="section-title text-lg-start text-center">Événementiel</div>
          <div class="section-text text-lg-start text-center">
            Découvrez nos soirées à thème, concerts live et événements exclusifs organisés tout au long de l'année chez NYABUNGO.<br><br>
            Profitez d'une ambiance unique et festive, idéale pour partager des moments inoubliables entre amis ou en famille.
          </div>
        </div>
        <div class="col-12 col-lg-6 mb-4 mb-lg-0 d-flex justify-content-center">
          <div style="width:100%; max-width:480px; height:380px; border-radius:1.2rem; overflow:hidden; box-shadow:0 2px 16px rgba(0,0,0,0.10);">
            <img src="../assets/event2.jpeg" alt="Événementiel spécial Nyabungo" style="width:100%; height:100%; object-fit:cover; display:block;">
          </div>
        </div>
      </div>
    </section>
    <!-- Événementiel (en deux colonnes) -->
    <section class="container py-5" id="evenement">
      <div class="row align-items-center g-3 g-lg-2">
        <div class="col-12 col-lg-6 mb-4 mb-lg-0 d-flex justify-content-center">
          <div style="width:100%; max-width:800px; height:380px; border-radius:1.2rem; overflow:hidden; box-shadow:0 2px 16px rgba(0,0,0,0.10);">
            <img src="../assets/event1.jpg" alt="Événementiel Nyabungo" style="width:100%; height:100%; object-fit:cover; display:block;">
          </div>
        </div>
        <div class="col-12 col-lg-6">
          <div class="section-title text-lg-start text-center">Événementiel</div>
          <div class="section-text text-lg-start text-center">
            Repas d'affaires, séminaires, déjeuners de presse ou dîners privés… NYABUNGO propose des espaces privatifs et un service sur-mesure pour faire de chaque occasion un moment d'exception.<br><br>
            Notre équipe vous accompagne dans l'organisation de vos événements, qu'ils soient professionnels ou familiaux, pour une expérience inoubliable dans un cadre raffiné.
          </div>
        </div>
      </div>
    </section>
    <!-- Contact & Localisation -->
    <section class="location-section py-5">
      <div class="container">
        <div class="row g-4 align-items-stretch">
          <div class="col-12 col-lg-6 mb-4 mb-lg-0">
            <iframe class="location-map" src="https://www.google.com/maps?q=-3.3822,29.3644&z=15&output=embed" width="100%" height="320" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
          </div>
          <div class="col-12 col-lg-6 d-flex align-items-center">
            <div class="location-info w-100">
              <h5>Nos adresses</h5>
              <div class="address-block">
                <?php if ($mutangaBranchInfo): ?>
                  <strong><?php echo htmlspecialchars($mutangaBranchInfo['name']); ?> :</strong><br>
                  <?php echo htmlspecialchars($mutangaBranchInfo['address']); ?><br>
                  Tél : <a href="tel:<?php echo htmlspecialchars($mutangaBranchInfo['phone']); ?>" class="contact-link"><?php echo htmlspecialchars($mutangaBranchInfo['phone']); ?></a><br>
                  Email : <a href="mailto:<?php echo htmlspecialchars($mutangaBranchInfo['email']); ?>" class="contact-link"><?php echo htmlspecialchars($mutangaBranchInfo['email']); ?></a><br>
                  Horaires : <?php echo htmlspecialchars($mutangaBranchInfo['opening_hours']); ?>
                <?php else: ?>
                  <strong>Mutanga :</strong><br>
                  Informations non disponibles.
                <?php endif; ?>
              </div>
              <div class="address-block">
                <?php if ($mutakuraBranchInfo): ?>
                  <strong><?php echo htmlspecialchars($mutakuraBranchInfo['name']); ?> :</strong><br>
                  <?php echo htmlspecialchars($mutakuraBranchInfo['address']); ?><br>
                  Tél : <a href="tel:<?php echo htmlspecialchars($mutakuraBranchInfo['phone']); ?>" class="contact-link"><?php echo htmlspecialchars($mutakuraBranchInfo['phone']); ?></a><br>
                  Email : <a href="mailto:<?php echo htmlspecialchars($mutakuraBranchInfo['email']); ?>" class="contact-link"><?php echo htmlspecialchars($mutakuraBranchInfo['email']); ?></a><br>
                  Horaires : <?php echo htmlspecialchars($mutakuraBranchInfo['opening_hours']); ?>
                <?php else: ?>
                  <strong>Mutakura :</strong><br>
                  Informations non disponibles.
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
    </div>
    </section>
  </main>
<?php
require_once __DIR__ . '/includes/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function showEventMsg(msg, cls) {
      const el = document.getElementById('event-message');
      el.textContent = msg;
      el.className = 'mt-3 text-center fw-bold ' + cls;
    }

    // Script pour le filtrage AJAX des images d'événements
    const branchSelect = document.getElementById('branch_select');
    const eventGallery = document.getElementById('event-gallery-display');

    if (branchSelect && eventGallery) { // Vérifiez si les éléments existent avant d'interagir
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
    }

    // Script pour le filtrage AJAX des menus du jour
    const menuBranchSelect = document.getElementById('menu_branch_select');
    const menuGalleryDisplay = document.getElementById('menu-gallery-display');

    if (menuBranchSelect && menuGalleryDisplay) { // Vérifiez si les éléments existent
        async function loadDayMenus(branchId) {
          try {
            let url = '../api/get_menus.php';
            if (branchId) {
              url += `?branch_id=${branchId}`;
            }

            const response = await fetch(url);
            if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
            }
            const menuItems = await response.json();

            let menusHtml = '';
            if (menuItems.length > 0) {
              menusHtml = '';
              menuItems.forEach(item => {
                menusHtml += `
                  <div class="menu-item-card d-flex flex-column align-items-center text-center p-2">
                      <img src="../assets/uploads/${item.image}" alt="${item.name}" class="rounded shadow-sm menu-item-img">
                      <h5 class="mt-3 mb-1 fw-bold">${item.name}</h5>
                      <p class="text-muted mb-1 small">${item.description}</p>
                      <p class="fw-bold text-gold">${item.price.toLocaleString('fr-BI', { style: 'currency', currency: 'BIF', minimumFractionDigits: 0, maximumFractionDigits: 0 })}</p>
                  </div>
                `;
              });
            } else {
              menusHtml = '<p class="text-center w-100">Aucun menu disponible pour cette branche pour le moment.</p>';
            }
            menuGalleryDisplay.innerHTML = menusHtml;

          } catch (error) {
            console.error('Erreur lors du chargement des menus du jour:', error);
            menuGalleryDisplay.innerHTML = '<p class="text-center w-100 text-danger">Erreur lors du chargement des menus.</p>';
          }
        }

        // Charger les menus initiaux lors du chargement de la page
        loadDayMenus(menuBranchSelect.value);

        // Écouter les changements sur le sélecteur de branche des menus
        menuBranchSelect.addEventListener('change', (event) => {
          loadDayMenus(event.target.value);
        });
    }
});
</script>