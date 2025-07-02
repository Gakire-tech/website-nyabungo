<?php
// public/reservation.php
$pageTitle = "Réservation";

require_once __DIR__ . '/includes/header.php';

$branchesData = [];
try {
    $stmt = $pdo->prepare("SELECT id, name, opening_hours FROM branches");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $branchesData[strtolower($row['name'])] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'opening_hours' => $row['opening_hours'],
            // 'time_slots' => [] // TODO: Définir ou récupérer les créneaux horaires par succursale si dynamiques
        ];
    }
} catch (PDOException $e) {
    error_log("Database error for reservation page: " . $e->getMessage());
}

$branchParam = isset($_GET['branch']) ? $_GET['branch'] : '';
?>
  <main class="flex-fill d-flex align-items-center justify-content-center p-4" style="margin-top: 90px;">
    <form id="reservation-form" class="bg-white rounded shadow-sm p-5 w-100" style="max-width: 520px;">
      <h1 class="section-title text-center mb-4">Réserver une table</h1>
      <div class="mb-3">
        <label class="form-label fw-bold">Succursale</label>
        <select id="branch" name="branch_id" class="form-select" required>
          <option value="">Choisir...</option>
          <?php foreach ($branchesData as $key => $branchInfo): ?>
            <option value="<?php echo htmlspecialchars($key); ?>" <?php echo ($branchParam === $key) ? 'selected' : ''; ?> data-id="<?php echo htmlspecialchars($branchInfo['id']); ?>">
              <?php echo htmlspecialchars($branchInfo['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <div id="opening-hours" class="form-text mt-1"></div>
      </div>
      <div class="row g-3 mb-3">
        <div class="col">
          <label class="form-label fw-bold">Date</label>
          <input type="date" name="reservation_date" class="form-control" required>
        </div>
        <div class="col">
          <label class="form-label fw-bold">Heure</label>
          <select id="reservation_time" name="reservation_time" class="form-select" required>
            <option value="">Choisir...</option>
          </select>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label fw-bold">Nombre de personnes</label>
        <input type="number" name="guests" min="1" max="30" class="form-control" required>
      </div>
      <div class="row g-3 mb-3">
        <div class="col">
          <label class="form-label fw-bold">Nom</label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="col">
          <label class="form-label fw-bold">Prénom</label>
          <input type="text" name="firstname" class="form-control">
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label fw-bold">Téléphone</label>
        <input type="tel" name="phone" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label fw-bold">Email</label>
        <input type="email" name="email" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label fw-bold">Occasion</label>
        <select name="occasion" class="form-select">
          <option value="">Aucune</option>
          <option value="Anniversaire">Anniversaire</option>
          <option value="Affaires">Affaires</option>
          <option value="Autre">Autre</option>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label fw-bold">Demandes spéciales</label>
        <textarea name="special_requests" class="form-control" rows="2"></textarea>
      </div>
      <button type="submit" class="btn btn-dark w-100 fw-bold">Réserver</button>
      <div id="form-message" class="mt-3 text-center fw-bold"></div>
    </form>
  </main>
<?php
require_once __DIR__ . '/includes/footer.php';
?>
  <script>
    // Horaires d'ouverture et créneaux horaires dynamiques récupérés via PHP
    const branchesData = <?php echo json_encode($branchesData); ?>;
    const timeSlots = {
      mutanga: [
        '11:30', '12:00', '12:30', '13:00', '13:30', '14:00',
        '18:30', '19:00', '19:30', '20:00', '20:30', '21:00', '21:30', '22:00'
      ],
      mutakura: [
        '12:00', '12:30', '13:00', '13:30', '14:00', '14:30', '15:00',
        '19:00', '19:30', '20:00', '20:30', '21:00', '21:30', '22:00', '22:30', '23:00'
      ]
    };

    const branchSelect = document.getElementById('branch');
    const openingHoursDiv = document.getElementById('opening-hours');
    const timeSelect = document.getElementById('reservation_time');
    // Afficher horaires et créneaux selon la succursale
    function updateBranchInfo() {
      const branchKey = branchSelect.value;
      const selectedBranch = branchesData[branchKey];
      if (selectedBranch) {
        openingHoursDiv.textContent = 'Horaires d\'ouverture : ' + selectedBranch.opening_hours;
        // Générer les créneaux horaires basés sur la succursale sélectionnée
        timeSelect.innerHTML = '<option value="">Choisir...</option>' +
          timeSlots[branchKey].map(t => `<option value="${t}">${t}</option>`).join('');
      } else {
        openingHoursDiv.textContent = '';
        timeSelect.innerHTML = '<option value="">Choisir...</option>';
      }
    }
    branchSelect.addEventListener('change', function() {
      updateBranchInfo();
      updateDisabledSlots();
    });
    document.querySelector('input[name=reservation_date]').addEventListener('change', updateDisabledSlots);

    // Initialiser les informations de la succursale si une est pré-sélectionnée
    if (branchSelect.value) {
        updateBranchInfo();
    }

    // Appeler aussi après pré-remplissage
    if (branchSelect.value && document.querySelector('input[name=reservation_date]').value) {
      updateDisabledSlots();
    }
    let pendingData = null;
    document.getElementById('reservation-form').addEventListener('submit', function(e) {
      e.preventDefault();
      const form = e.target;
      // Récupérer l'ID numérique de la succursale à partir de l'option sélectionnée
      const selectedOption = branchSelect.options[branchSelect.selectedIndex];
      const branchId = selectedOption ? selectedOption.dataset.id : null;

      const data = {
        branch_id: branchId,
        reservation_date: form.reservation_date.value,
        reservation_time: form.reservation_time.value,
        guests: form.guests.value,
        name: form.name.value + (form.firstname.value ? ' ' + form.firstname.value : ''),
        phone: form.phone.value,
        email: form.email.value,
        occasion: form.occasion.value,
        special_requests: form.special_requests.value
      };
      // Validation simple
      if (!data.branch_id || !data.reservation_date || !data.reservation_time || !data.guests || !data.name || !data.phone) {
        showMessage('Veuillez remplir tous les champs obligatoires.', 'text-danger');
        return;
      }
      // Afficher la modale de récapitulatif
      showRecapModal(data);
    });

    function showRecapModal(data) {
      pendingData = data;
      // Utiliser le nom de la succursale de branchesData plutôt que l'ID numérique
      const branchNameForDisplay = Object.values(branchesData).find(b => b.id == data.branch_id)?.name || 'N/A';
      document.getElementById('recap-branch').textContent = branchNameForDisplay;
      document.getElementById('recap-date').textContent = data.reservation_date;
      document.getElementById('recap-time').textContent = data.reservation_time;
      document.getElementById('recap-guests').textContent = data.guests;
      document.getElementById('recap-name').textContent = data.name;
      document.getElementById('recap-phone').textContent = data.phone;
      document.getElementById('recap-email').textContent = data.email;
      document.getElementById('recap-occasion').textContent = data.occasion || 'Aucune';
      document.getElementById('recap-special').textContent = data.special_requests || 'Aucune';
      document.getElementById('recap-modal').classList.remove('d-none');
      document.getElementById('recap-overlay').classList.remove('d-none');
    }
    function closeRecapModal() {
      document.getElementById('recap-modal').classList.add('d-none');
      document.getElementById('recap-overlay').classList.add('d-none');
      pendingData = null;
    }
  </script>
  <!-- Modale de récapitulatif (exclue du fichier) -->
  <div id="recap-overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 z-40 d-none" style="z-index:1040;" onclick="closeRecapModal()"></div>
  <div id="recap-modal" class="position-fixed top-50 start-50 translate-middle bg-white rounded shadow-lg p-4 w-100 d-none" style="max-width: 28rem; z-index:1050;">
    <button onclick="closeRecapModal()" class="btn-close position-absolute top-0 end-0 m-2" aria-label="Fermer"></button>
    <h5 class="fw-bold mb-3">Confirmer votre réservation</h5>
    <p><strong>Succursale :</strong> <span id="recap-branch"></span></p>
    <p><strong>Date :</strong> <span id="recap-date"></span></p>
    <p><strong>Heure :</strong> <span id="recap-time"></span></p>
    <p><strong>Personnes :</strong> <span id="recap-guests"></span></p>
    <p><strong>Nom :</strong> <span id="recap-name"></span></p>
    <p><strong>Téléphone :</strong> <span id="recap-phone"></span></p>
    <p><strong>Email :</strong> <span id="recap-email"></span></p>
    <p><strong>Occasion :</strong> <span id="recap-occasion"></span></p>
    <p><strong>Demandes spéciales :</strong> <span id="recap-special"></span></p>
    <button onclick="confirmReservation()" class="btn btn-success w-100 mt-3">Confirmer et réserver</button>
  </div>
  <script>
    async function confirmReservation() {
      if (!pendingData) return;
      showMessage('Envoi de la réservation...', 'text-secondary');
      try {
        const res = await fetch('/api/reservations', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(pendingData)
        });
        const result = await res.json();
        if (result.status === 'ok') {
          closeRecapModal();
          window.location.href = `merci.php?name=${encodeURIComponent(pendingData.name)}&date=${encodeURIComponent(pendingData.reservation_date)}&time=${encodeURIComponent(pendingData.reservation_time)}&branch=${encodeURIComponent(pendingData.branch_id === 1 ? 'Mutanga' : 'Mutakura')}&guests=${encodeURIComponent(pendingData.guests)}`;
        } else {
          showMessage(result.message || 'Erreur lors de la réservation.', 'text-danger');
        }
      } catch (err) {
        showMessage('Erreur lors de la réservation.', 'text-danger');
      }
    }
    function showMessage(msg, cls) {
      const el = document.getElementById('form-message');
      el.textContent = msg;
      el.className = 'mt-3 text-center fw-bold ' + cls;
    }

    // Appeler updateBranchInfo au chargement initial si une succursale est pré-sélectionnée
    document.addEventListener('DOMContentLoaded', () => {
      if (branchSelect.value) {
        updateBranchInfo();
      }
    });
    // Placeholder for updateDisabledSlots if it's implemented elsewhere for actual slot availability checking
    function updateDisabledSlots() {
      // This function would fetch actual available slots from the server based on date and branch.
      // For now, it's a placeholder. Implement real logic here if needed.
      console.log('Checking for disabled slots...');
    }
  </script> 