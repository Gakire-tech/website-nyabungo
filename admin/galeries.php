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
  <title>Gestion des Galeries - Admin NYABUNGO</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex">
  <?php include 'sidebar.php'; ?>
  <main class="flex-grow-1 p-5">
    <div class="mb-4 d-flex justify-content-between align-items-center">
      <h1 class="h4 fw-bold mb-0">Gestion des Galeries</h1>
      <button id="add-img-btn" class="btn btn-dark fw-bold">Ajouter une image</button>
    </div>
    <div class="mb-4 d-flex gap-3">
      <select id="branch-filter" class="form-select w-auto">
        <option value="1">Mutanga</option>
        <option value="2">Mutakura</option>
      </select>
    </div>
    <div id="gallery-table" class="table-responsive"></div>
    <!-- Modale ajout/modif -->
    <div id="img-modal-overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-none" style="z-index:1040;"></div>
    <div id="img-modal" class="position-fixed top-50 start-50 translate-middle bg-white rounded shadow-lg p-4 w-100 d-none" style="max-width: 28rem; z-index:1050;">
      <h2 id="img-modal-title" class="h5 fw-bold mb-3">Ajouter une image</h2>
      <form id="img-form" class="vstack gap-3">
        <input type="hidden" name="id">
        <div>
          <label class="form-label fw-bold">Nom de fichier (dans /assets)</label>
          <input type="text" name="image" class="form-control" required>
        </div>
        <div>
          <label class="form-label fw-bold">Légende</label>
          <input type="text" name="caption" class="form-control">
        </div>
        <div class="d-flex align-items-center gap-2">
          <label class="form-label fw-bold mb-0">Active</label>
          <input type="checkbox" name="is_active" checked>
        </div>
        <div>
          <label class="form-label fw-bold">Ordre d'affichage</label>
          <input type="number" name="display_order" class="form-control" value="0">
        </div>
        <div class="d-flex justify-content-end gap-2">
          <button type="button" id="cancel-img-modal" class="btn btn-outline-secondary">Annuler</button>
          <button type="submit" class="btn btn-dark">Enregistrer</button>
        </div>
        <div id="img-modal-message" class="mt-2 text-center fw-bold"></div>
      </form>
    </div>
  </main>
  <!-- Bootstrap JS Bundle (inclut Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function openImgModal(img = null) {
      document.getElementById('img-modal-overlay').classList.remove('d-none');
      document.getElementById('img-modal').classList.remove('d-none');
      document.getElementById('img-modal-title').textContent = img ? 'Modifier une image' : 'Ajouter une image';
      document.getElementById('img-modal-message').textContent = '';
      const form = document.getElementById('img-form');
      form.reset();
      if (img) {
        form.id.value = img.id;
        form.image.value = img.image;
        form.caption.value = img.caption;
        form.is_active.checked = img.is_active == 1;
        form.display_order.value = img.display_order;
      } else {
        form.id.value = '';
        form.is_active.checked = true;
        form.display_order.value = 0;
      }
    }
    function closeImgModal() {
      document.getElementById('img-modal-overlay').classList.add('d-none');
      document.getElementById('img-modal').classList.add('d-none');
    }
    document.getElementById('add-img-btn').onclick = () => openImgModal();
    document.getElementById('cancel-img-modal').onclick = closeImgModal;
    document.getElementById('img-modal-overlay').onclick = closeImgModal;
    document.getElementById('branch-filter').onchange = loadGallery;
    async function loadGallery() {
      const branch = document.getElementById('branch-filter').value;
      const res = await fetch(`/api/galleries/${branch}`);
      const data = await res.json();
      if (data.status === 'ok') {
        renderGalleryTable(data.data);
      } else {
        document.getElementById('gallery-table').innerHTML = '<div class="text-danger">Erreur de chargement.</div>';
      }
    }
    function renderGalleryTable(items) {
      let html = `<table class='table table-bordered table-hover align-middle text-center'><thead class='table-light'><tr>
        <th>Image</th><th>Légende</th><th>Active</th><th>Ordre</th><th>Actions</th></tr></thead><tbody>`;
      for (const img of items) {
        html += `<tr>
          <td>${img.image ? `<img src='/assets/${img.image}' class='rounded' style='height: 64px; width: 96px; object-fit: cover;'/>` : ''}</td>
          <td>${img.caption || ''}</td>
          <td>${img.is_active == 1 ? '✅' : '❌'}</td>
          <td>${img.display_order}</td>
          <td>
            <button onclick='editImg(${JSON.stringify(img)})' class='btn btn-link text-primary p-0 me-2'>Modifier</button>
            <button onclick='deleteImg(${img.id})' class='btn btn-link text-danger p-0 me-2'>Supprimer</button>
            <button onclick='toggleActiveImg(${img.id},${img.is_active})' class='btn btn-link text-secondary p-0'>${img.is_active == 1 ? 'Désactiver' : 'Activer'}</button>
          </td>
        </tr>`;
      }
      html += '</tbody></table>';
      document.getElementById('gallery-table').innerHTML = html;
    }
    window.editImg = function(img) { openImgModal(img); };
    window.deleteImg = async function(id) {
      if (!confirm('Supprimer cette image ?')) return;
      await fetch(`/api/galleries/${id}`, { method: 'DELETE' });
      await loadGallery();
    };
    window.toggleActiveImg = async function(id, active) {
      await fetch(`/api/galleries/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ is_active: active == 1 ? 0 : 1 })
      });
      await loadGallery();
    };
    document.getElementById('img-form').onsubmit = async function(e) {
      e.preventDefault();
      const form = e.target;
      const branch = document.getElementById('branch-filter').value;
      const data = {
        branch_id: branch,
        image: form.image.value,
        caption: form.caption.value,
        is_active: form.is_active.checked ? 1 : 0,
        display_order: form.display_order.value
      };
      if (form.id.value) {
        // Update
        await fetch(`/api/galleries/${form.id.value}`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        });
      } else {
        // Create
        await fetch('/api/galleries', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        });
      }
      closeImgModal();
      await loadGallery();
    };
    loadGallery();
  </script>
</body>
</html> 