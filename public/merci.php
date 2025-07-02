<?php
// public/merci.php
$pageTitle = "Merci pour votre réservation";

require_once __DIR__ . '/includes/header.php';

?>
  <main class="flex-fill d-flex align-items-center justify-content-center p-4" style="margin-top: 90px;">
    <div class="bg-white rounded shadow-sm p-5 mx-auto" style="max-width: 420px; width: 100%; text-align: center;">
      <h1 class="section-title text-center mb-4">Merci pour votre réservation !</h1>
      <div class="fs-1 mb-4">🎉</div>
      <div class="fs-5 fw-bold mb-2">Votre demande de réservation a bien été enregistrée.</div>
      <div class="mb-4">Vous recevrez une confirmation par e-mail très prochainement.<br>Nous avons hâte de vous accueillir chez <span class="fw-bold">NYABUNGO RESTAURANT & BAR</span> !</div>
      <div id="qrcode" class="d-flex justify-content-center my-4"></div>
      <button id="download-qr" class="btn btn-dark w-100 mb-3">Télécharger le QR code</button>
      <a href="index.php" class="btn btn-dark w-100">Retour à l'accueil</a>
    </div>
  </main>
<?php
require_once __DIR__ . '/includes/footer.php';
?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <script>
    // Exemple de données à encoder (à adapter si besoin)
    const params = new URLSearchParams(window.location.search);
    const name = params.get('name') || 'Client';
    const date = params.get('date') || '';
    const time = params.get('time') || '';
    const branch = params.get('branch') || '';
    const guests = params.get('guests') || '';
    const qrData = `NYABUNGO\nNom: ${name}\nDate: ${date}\nHeure: ${time}\nSuccursale: ${branch}\nPersonnes: ${guests}`;
    new QRCode(document.getElementById('qrcode'), {
      text: qrData,
      width: 160,
      height: 160
    });
    document.getElementById('download-qr').onclick = function() {
      const img = document.querySelector('#qrcode img');
      if (img) {
        const a = document.createElement('a');
        a.href = img.src;
        a.download = 'reservation-nyabungo-qr.png';
        a.click();
      }
    };
  </script> 