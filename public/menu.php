<?php
// public/menu.php
$branch = isset($_GET['branch']) ? $_GET['branch'] : '';
if (!$branch) {
  header('Location: index.php');
  exit;
}
$branchName = $branch === 'mutanga' ? 'Mutanga' : 'Mutakura';
$pageTitle = "Menu - NYABUNGO " . $branchName;

require_once __DIR__ . '/includes/header.php';

?>
  <main class="flex-fill p-4 container" style="margin-top: 90px;">
    <h1 class="section-title text-center mb-4">Menu - NYABUNGO <?php echo htmlspecialchars($branchName); ?></h1>
    <div class="text-center mb-4">
      <button onclick="changeBranch()" class="btn btn-dark px-4 py-2">Changer de succursale</button>
    </div>
    <h2 class="section-title mb-4">Restaurant</h2>
    <div id="restaurant-menu" class="row g-4 mb-5"></div>
    <h2 class="section-title mb-4">Bar</h2>
    <div id="bar-menu" class="row g-4"></div>
  </main>
<?php
require_once __DIR__ . '/includes/footer.php';
?>
  <script>
    // Fonction pour changer de succursale
    function changeBranch() {
      localStorage.removeItem('nyabungo_branch');
      window.location.href = 'index.php';
    }
    // Récupérer le menu via l'API
    const branchParam = '<?php echo $branch; ?>';
    function renderMenu(items, containerId) {
      const container = document.getElementById(containerId);
      if (!items.length) {
        container.innerHTML = '<div class="text-secondary">Aucun élément pour le moment.</div>';
        return;
      }
      container.innerHTML = items.map((item, idx) => `
        <div class="col-12 col-md-6">
          <div class="bg-white rounded shadow-sm p-4 d-flex flex-column flex-md-row gap-3 h-100">
            ${item.image ? `<img src="/assets/uploads/${item.image}" alt="${item.name}" class="rounded mb-2 mb-md-0" style="width: 8rem; height: 8rem; object-fit: cover;">` : ''}
            <div class="flex-grow-1 d-flex flex-column justify-content-between">
              <div>
                <div class="fw-bold fs-5 mb-2">${item.name}</div>
                <div class="text-secondary mb-2">${item.description || ''}</div>
                ${item.allergens ? `<div class="text-danger small mb-1">Allergènes : ${item.allergens}</div>` : ''}
                <div class="text-muted small mb-1">${item.is_available == 1 ? 'Disponible' : 'Indisponible'}</div>
              </div>
              <div class="d-flex align-items-center justify-content-between mt-2">
                <div class="fw-bold text-success">${item.price ? item.price + ' Fbu' : ''}</div>
                <button onclick="showDetails(${containerId === 'restaurant-menu' ? 'restaurantMenu' : 'barMenu'}[${idx}])" class="ms-2 btn btn-dark btn-sm">Voir détails</button>
              </div>
            </div>
          </div>
        </div>
      `).join('');
    }

    // Stocker les menus pour accès dans la modale
    let restaurantMenu = [];
    let barMenu = [];

    // Charger le menu restaurant
    fetch(`/api/menus/${branchParam}/restaurant`)
      .then(res => res.json())
      .then(data => {
        if (data.status === 'ok') {
          restaurantMenu = data.data;
          renderMenu(restaurantMenu, 'restaurant-menu');
        }
      });
    // Charger le menu bar
    fetch(`/api/menus/${branchParam}/bar`)
      .then(res => res.json())
      .then(data => {
        if (data.status === 'ok') {
          barMenu = data.data;
          renderMenu(barMenu, 'bar-menu');
        }
      });

    // Modale de détails
    function showDetails(item) {
      const modal = document.getElementById('details-modal');
      const overlay = document.getElementById('modal-overlay');
      document.getElementById('modal-img').src = item.image ? '/assets/uploads/' + item.image : '';
      document.getElementById('modal-img').style.display = item.image ? '' : 'none';
      document.getElementById('modal-name').textContent = item.name;
      document.getElementById('modal-desc').textContent = item.description || '';
      document.getElementById('modal-allergens').textContent = item.allergens ? 'Allergènes : ' + item.allergens : '';
      document.getElementById('modal-available').textContent = item.is_available == 1 ? 'Disponible' : 'Indisponible';
      document.getElementById('modal-price').textContent = item.price ? item.price + ' Fbu' : '';
      modal.classList.remove('d-none');
      overlay.classList.remove('d-none');
    }
    function closeModal() {
      document.getElementById('details-modal').classList.add('d-none');
      document.getElementById('modal-overlay').classList.add('d-none');
    }
  </script>
  <!-- Modale détails -->
  <div id="modal-overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 z-40 d-none" style="z-index:1040;" onclick="closeModal()"></div>
  <div id="details-modal" class="position-fixed top-50 start-50 translate-middle bg-white rounded shadow-lg p-4 w-100" style="max-width: 28rem; z-index:1050; display: block;" class="d-none">
    <button onclick="closeModal()" class="btn-close position-absolute top-0 end-0 m-2" aria-label="Fermer"></button>
    <img id="modal-img" src="" alt="" class="d-block rounded mx-auto mb-3" style="width: 10rem; height: 10rem; object-fit: cover; display:none;">
    <div class="fs-4 fw-bold mb-2" id="modal-name"></div>
    <div class="text-secondary mb-2" id="modal-desc"></div>
    <div class="text-danger small mb-1" id="modal-allergens"></div>
    <div class="text-muted small mb-1" id="modal-available"></div>
    <div class="fw-bold text-success text-end mb-2" id="modal-price"></div>
  </div>
</body>
</html> 