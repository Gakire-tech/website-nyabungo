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

    // Récupérer les menus pour la branche sélectionnée (image, name, description)
    if ($selectedBranchId) {
        $stmt = $pdo->prepare("
            SELECT image, name, description
            FROM menus
            WHERE branch_id = :branch_id AND is_active = TRUE
            ORDER BY id
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

    // Récupérer les événements (images et textes) pour la branche id=1
    $evenementImages = [];
    $stmt = $pdo->prepare("SELECT image_path, alt_text FROM event_images WHERE branch_id = 1 ORDER BY display_order ASC, id ASC");
    $stmt->execute();
    $evenementImages = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $currentMenus = [];
    $branches = [];
    $evenementImages = [];
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
      <div class="mb-4 d-flex justify-content-center" id="branches-filter"></div>
      <div id="branchesCarouselWrapper">
        <div id="branchesCarousel" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-inner" id="branches-carousel-inner"></div>
          <button class="carousel-control-prev" type="button" data-bs-target="#branchesCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Précédent</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#branchesCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Suivant</span>
          </button>
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
          <?php foreach ($currentMenus as $menu): ?>
            <div class="menu-item-card d-flex flex-column align-items-center text-center p-2">
                <img src="../assets/uploads/<?php echo htmlspecialchars($menu['image']); ?>" alt="<?php echo htmlspecialchars($menu['name']); ?>" class="rounded shadow-sm menu-item-img">
                <h5 class="mt-3 mb-1 fw-bold"><?php echo htmlspecialchars($menu['name']); ?></h5>
                <p class="text-muted mb-1 small"><?php echo htmlspecialchars($menu['description']); ?></p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-center w-100">Aucun menu disponible pour cette branche pour le moment.</p>
        <?php endif; ?>
      </div>
    </section>
    <!-- Événementiel (carrousel dynamique) -->
    <section class="container py-5" id="evenement2">
      <div class="section-title text-center mb-4">Événementiel</div>
      <?php if (!empty($evenementImages)): ?>
        <div id="evenementCarousel" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-inner">
            <?php foreach ($evenementImages as $idx => $img): ?>
              <div class="carousel-item<?php if ($idx === 0) echo ' active'; ?>">
                <div class="card shadow-sm border-0" style="border-radius:1.2rem;">
                  <div class="row align-items-center g-2 flex-lg-row flex-column-reverse p-3">
                    <div class="col-12 col-lg-6">
                      <div class="section-text text-lg-start text-center" style="font-size:1rem; padding:0.5rem 0;">
                        <?php echo htmlspecialchars($img['alt_text'] ?: ''); ?>
                      </div>
                    </div>
                    <div class="col-12 col-lg-6 mb-3 mb-lg-0 d-flex justify-content-center">
                      <div style="width:100%; max-width:320px; height:220px; border-radius:1rem; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.10);">
                        <img src="../assets/<?php echo htmlspecialchars($img['image_path']); ?>" alt="<?php echo htmlspecialchars($img['alt_text']); ?>" style="width:100%; height:100%; object-fit:cover; display:block;">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#evenementCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Précédent</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#evenementCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Suivant</span>
          </button>
        </div>
      <?php else: ?>
        <div class="text-center">Aucun événement à afficher pour le moment.</div>
      <?php endif; ?>
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

let branchAutoScrollInterval;

async function loadBranchesCarousel(branchName = '') {
  const url = branchName ? `../api/get_branches.php?branch=${encodeURIComponent(branchName)}` : '../api/get_branches.php';
  const res = await fetch(url);
  const data = await res.json();
  const carouselInner = document.getElementById('branches-carousel-inner');
  carouselInner.innerHTML = '';
  if (!data.branches.length) {
    carouselInner.innerHTML = '<div class="text-center w-100 py-4">Aucune branche trouvée.</div>';
    return;
  }
  // Générer les cards par paires (2 par slide)
  let cards = [];
  data.branches.forEach(branch => {
    branch.images.forEach(img => {
      cards.push({
        name: branch.name,
        image: img.image,
        caption: img.caption || ''
      });
    });
  });
  for (let i = 0; i < cards.length; i += 2) {
    const active = i === 0 ? 'active' : '';
    const card1 = cards[i];
    const card2 = cards[i+1];
    carouselInner.innerHTML += `
      <div class="carousel-item ${active}">
        <div class="d-flex justify-content-center align-items-stretch gap-4">
          <div class="card branch-card flex-row align-items-stretch" style="min-width:480px; max-width:540px; height:260px;">
            <div class="branch-card-img-wrap d-flex align-items-center justify-content-center" style="flex:0 0 200px; height:100%;">
              <img src="../assets/${card1.image}" class="branch-card-img" alt="${card1.name}" style="width:180px; height:180px; object-fit:cover; border-radius:1rem;">
            </div>
            <div class="card-body d-flex flex-column justify-content-center" style="flex:1 1 0;">
              <h5 class="card-title mb-2">${card1.name}</h5>
              <p class="card-text">${card1.caption}</p>
            </div>
          </div>
          ${card2 ? `
          <div class="card branch-card flex-row align-items-stretch" style="min-width:480px; max-width:540px; height:260px;">
            <div class="branch-card-img-wrap d-flex align-items-center justify-content-center" style="flex:0 0 200px; height:100%;">
              <img src="../assets/${card2.image}" class="branch-card-img" alt="${card2.name}" style="width:180px; height:180px; object-fit:cover; border-radius:1rem;">
            </div>
            <div class="card-body d-flex flex-column justify-content-center" style="flex:1 1 0;">
              <h5 class="card-title mb-2">${card2.name}</h5>
              <p class="card-text">${card2.caption}</p>
            </div>
          </div>
          ` : ''}
        </div>
      </div>
    `;
  }
}

// Générer le filtre de branches
async function renderBranchFilter() {
  const res = await fetch('../api/get_branches.php');
  const data = await res.json();
  const filterDiv = document.getElementById('branches-filter');
  if (!data.branches.length) return;
  filterDiv.innerHTML = '';
  data.branches.forEach(branch => {
    const btn = document.createElement('button');
    btn.className = 'btn btn-outline-dark mx-1';
    btn.textContent = branch.name;
    btn.onclick = () => loadBranchesCarousel(branch.name);
    filterDiv.appendChild(btn);
  });
  // Bouton pour tout afficher
  const allBtn = document.createElement('button');
  allBtn.className = 'btn btn-dark mx-1';
  allBtn.textContent = 'Toutes';
  allBtn.onclick = () => loadBranchesCarousel('');
  filterDiv.prepend(allBtn);
}

renderBranchFilter();
loadBranchesCarousel();
</script>

<style>
#branchesCarouselWrapper {
  max-width: 1200px;
  margin: 0 auto;
}
#branchesCarousel .carousel-inner {
  width: 100%;
}
.branch-card {
  background: #fff;
  border-radius: 1.2rem;
  border: none;
  box-shadow: 0 2px 12px rgba(0,0,0,0.08);
  transition: box-shadow 0.2s;
  min-width: 600px;
  max-width: 700px;
  height: 260px;
}
.branch-card:hover {
  box-shadow: 0 4px 24px rgba(0,0,0,0.13);
}
.branch-card-img-wrap {
  padding: 0.5rem 0.5rem 0.5rem 1rem;
}
.branch-card-img {
  border-radius: 1rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
@media (max-width: 991.98px) {
  .branch-card { min-width: 98vw; max-width: 99vw; height: 180px; }
  .branch-card-img-wrap { flex:0 0 90px; }
  .branch-card-img { width: 80px; height: 80px; }
  #branchesCarouselWrapper { max-width: 100vw; }
}
</style>