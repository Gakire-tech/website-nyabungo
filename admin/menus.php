<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header('Location: login.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion des Menus - Admin NYABUNGO</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex">
  <!-- Sidebar -->
  <?php include 'sidebar.php'; ?>
  <!-- Main content -->
  <main class="flex-grow-1 p-5">
    <div class="mb-4 d-flex justify-content-between align-items-center">
      <h1 class="h4 fw-bold mb-0">Gestion des Menus</h1>
      <button id="add-item-btn" class="btn btn-dark fw-bold">Ajouter un item</button>
    </div>
    <div class="mb-4 d-flex gap-3">
      <select id="branch-filter" class="form-select w-auto">
        <option value="1">Mutanga</option>
        <option value="2">Mutakura</option>
      </select>
      <select id="type-filter" class="form-select w-auto">
        <option value="restaurant">Restaurant</option>
        <option value="bar">Bar</option>
      </select>
    </div>
    <div id="menu-table" class="table-responsive"></div>
    <!-- Modale ajout/modif -->
    <div id="item-modal-overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-none" style="z-index:1040;"></div>
    <div id="item-modal" class="position-fixed top-50 start-50 translate-middle bg-white rounded shadow-lg p-4 w-100 d-none" style="max-width: 28rem; z-index:1050;">
      <h2 id="modal-title" class="h5 fw-bold mb-3">Ajouter un item</h2>
      <form id="item-form" class="vstack gap-3">
        <input type="hidden" name="id">
        <div>
          <label class="form-label fw-bold">Nom</label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div>
          <label class="form-label fw-bold">Description</label>
          <textarea name="description" class="form-control" rows="2"></textarea>
        </div>
        <div>
          <label class="form-label fw-bold">Prix (Fbu)</label>
          <input type="number" name="price" min="0" step="0.01" class="form-control" required>
        </div>
        <div>
          <label class="form-label fw-bold">Allergènes</label>
          <input type="text" name="allergens" class="form-control">
        </div>
        <div>
          <label class="form-label fw-bold">Image (nom de fichier)</label>
          <input type="text" name="image" class="form-control">
        </div>
        <div class="d-flex align-items-center gap-2">
          <label class="form-label fw-bold mb-0">Disponible</label>
          <input type="checkbox" name="is_available" checked>
        </div>
        <div class="d-flex justify-content-end gap-2">
          <button type="button" id="cancel-modal" class="btn btn-outline-secondary">Annuler</button>
          <button type="submit" class="btn btn-dark">Enregistrer</button>
        </div>
        <div id="modal-message" class="mt-2 text-center fw-bold"></div>
      </form>
    </div>
  </main>
  <!-- Bootstrap JS Bundle (inclut Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Variables globales
    let currentEditId = null;
    // Ouvrir/fermer la modale
    function openModal(item = null) {
      document.getElementById('item-modal-overlay').classList.remove('d-none');
      document.getElementById('item-modal').classList.remove('d-none');
      document.getElementById('modal-title').textContent = item ? 'Modifier un item' : 'Ajouter un item';
      document.getElementById('modal-message').textContent = '';
      const form = document.getElementById('item-form');
      form.reset();
      if (item) {
        form.id.value = item.id;
        form.name.value = item.name;
        form.description.value = item.description;
        form.price.value = item.price;
        form.allergens.value = item.allergens;
        form.image.value = item.image;
        form.is_available.checked = item.is_available == 1;
      } else {
        form.id.value = '';
        form.is_available.checked = true;
      }
    }
    function closeModal() {
      document.getElementById('item-modal-overlay').classList.add('d-none');
      document.getElementById('item-modal').classList.add('d-none');
      currentEditId = null;
    }
    document.getElementById('add-item-btn').onclick = () => openModal();
    document.getElementById('cancel-modal').onclick = closeModal;
    document.getElementById('item-modal-overlay').onclick = closeModal;
    // Filtres
    document.getElementById('branch-filter').onchange = loadMenu;
    document.getElementById('type-filter').onchange = loadMenu;
    // Charger la liste des items
    async function loadMenu() {
      const branch = document.getElementById('branch-filter').value;
      const type = document.getElementById('type-filter').value;
      const res = await fetch(`/api/menus/${branch}/${type}`);
      const data = await res.json();
      if (data.status === 'ok') {
        renderTable(data.data);
      } else {
        document.getElementById('menu-table').innerHTML = '<div class="text-danger">Erreur de chargement.</div>';
      }
    }
    // Afficher le tableau
    function renderTable(items) {
      let html = `<table class='table table-bordered table-hover align-middle text-center'><thead class='table-light'><tr>
        <th>Nom</th><th>Description</th><th>Prix</th><th>Allergènes</th><th>Image</th><th>Dispo</th><th>Actions</th></tr></thead><tbody>`;
      for (const item of items) {
        html += `<tr>
          <td class='fw-bold'>${item.name}</td>
          <td>${item.description || ''}</td>
          <td>${item.price} Fbu</td>
          <td>${item.allergens || ''}</td>
          <td>${item.image ? `<img src='/assets/${item.image}' class='rounded' style='height: 40px; width: 40px; object-fit: cover;'/>` : ''}</td>
          <td>${item.is_available == 1 ? '✅' : '❌'}</td>
          <td>
            <button onclick='editItem(${JSON.stringify(item)})' class='btn btn-link text-primary p-0 me-2'>Modifier</button>
            <button onclick='deleteItem(${item.id})' class='btn btn-link text-danger p-0 me-2'>Supprimer</button>
            <button onclick='toggleDispo(${item.id},${item.is_available})' class='btn btn-link text-secondary p-0'>${item.is_available == 1 ? 'Indispo.' : 'Dispo.'}</button>
          </td>
        </tr>`;
      }
      html += '</tbody></table>';
      document.getElementById('menu-table').innerHTML = html;
    }
    // Actions CRUD (à relier à l'API)
    window.editItem = function(item) { openModal(item); };
    window.deleteItem = async function(id) {
      if (!confirm('Supprimer cet item ?')) return;
      const res = await fetch(`/api/menus/${id}`, { method: 'DELETE' });
      await loadMenu();
    };
    window.toggleDispo = async function(id, dispo) {
      await fetch(`/api/menus/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ is_available: dispo == 1 ? 0 : 1 })
      });
      await loadMenu();
    };
    // Soumission du formulaire
    document.getElementById('item-form').onsubmit = async function(e) {
      e.preventDefault();
      const form = e.target;
      const data = {
        menu_id: document.getElementById('type-filter').value === 'restaurant' ? (document.getElementById('branch-filter').value == '1' ? 1 : 3) : (document.getElementById('branch-filter').value == '1' ? 2 : 4),
        name: form.name.value,
        description: form.description.value,
        price: form.price.value,
        allergens: form.allergens.value,
        image: form.image.value,
        is_available: form.is_available.checked ? 1 : 0
      };
      if (form.id.value) {
        // Update
        await fetch(`/api/menus/${form.id.value}`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        });
      } else {
        // Create
        await fetch('/api/menus', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        });
      }
      closeModal();
      await loadMenu();
    };
    // Initial load
    loadMenu();
  </script>
</body>
</html> 