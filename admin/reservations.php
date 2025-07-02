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
  <title>Gestion des Réservations - Admin NYABUNGO</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex">
  <?php include 'sidebar.php'; ?>
  <main class="flex-grow-1 p-5">
    <div class="mb-4 d-flex justify-content-between align-items-center">
      <h1 class="h4 fw-bold mb-0">Gestion des Réservations</h1>
    </div>
    <div class="mb-4 d-flex gap-3">
      <select id="branch-filter" class="form-select w-auto">
        <option value="1">Mutanga</option>
        <option value="2">Mutakura</option>
      </select>
      <input type="date" id="date-filter" class="form-control w-auto">
    </div>
    <div id="reservations-table" class="table-responsive"></div>
  </main>
  <!-- Bootstrap JS Bundle (inclut Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('branch-filter').onchange = loadReservations;
    document.getElementById('date-filter').onchange = loadReservations;
    async function loadReservations() {
      const branch = document.getElementById('branch-filter').value;
      const date = document.getElementById('date-filter').value;
      let url = `/api/reservations/${branch}`;
      const res = await fetch(url);
      const data = await res.json();
      if (data.status === 'ok') {
        let items = data.data;
        if (date) {
          items = items.filter(r => r.reservation_date === date);
        }
        renderTable(items);
      } else {
        document.getElementById('reservations-table').innerHTML = '<div class="text-danger">Erreur de chargement.</div>';
      }
    }
    function renderTable(items) {
      let html = `<table class='table table-bordered table-hover align-middle text-center'><thead class='table-light'><tr>
        <th>Date</th><th>Heure</th><th>Nom</th><th>Personnes</th><th>Téléphone</th><th>Email</th><th>Demandes</th><th>Statut</th><th>Actions</th></tr></thead><tbody>`;
      for (const item of items) {
        html += `<tr>
          <td>${item.reservation_date}</td>
          <td>${item.reservation_time}</td>
          <td>${item.name}</td>
          <td>${item.guests}</td>
          <td>${item.phone}</td>
          <td>${item.email || ''}</td>
          <td>${item.special_requests || ''}</td>
          <td>${renderStatus(item.status)}</td>
          <td>
            <button onclick='confirmRes(${item.id})' class='btn btn-link text-success p-0 me-2'>Confirmer</button>
            <button onclick='cancelRes(${item.id})' class='btn btn-link text-danger p-0'>Annuler</button>
          </td>
        </tr>`;
      }
      html += '</tbody></table>';
      document.getElementById('reservations-table').innerHTML = html;
    }
    function renderStatus(status) {
      if (status === 'pending') return '<span class="text-warning fw-bold">En attente</span>';
      if (status === 'confirmed') return '<span class="text-success fw-bold">Confirmée</span>';
      if (status === 'cancelled') return '<span class="text-danger fw-bold">Annulée</span>';
      return status;
    }
    window.confirmRes = async function(id) {
      await fetch(`/api/reservations/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status: 'confirmed' })
      });
      await loadReservations();
    };
    window.cancelRes = async function(id) {
      await fetch(`/api/reservations/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status: 'cancelled' })
      });
      await loadReservations();
    };
    loadReservations();
  </script>
</body>
</html> 