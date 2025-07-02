<?php
// public/a-propos.php
$pageTitle = "À propos & Notre histoire";

require_once __DIR__ . '/includes/header.php';

// Récupérer le contenu dynamique pour la page 'a-propos'
$aproposHistoire = 'Fondé en 2022, NYABUNGO RESTAURANT & BAR est né de la passion pour la gastronomie, l\'art de recevoir et le raffinement. Notre établissement s\'inspire des grandes maisons européennes tout en valorisant les saveurs et le terroir du Burundi. Nous avons à cœur d\'offrir à nos clients une expérience unique, où chaque détail compte.';
$aproposChefVision = 'Notre chef, Jean-Claude Niyonzima, propose une cuisine créative, élégante et généreuse, mêlant produits locaux d\'exception et inspirations internationales. Son ambition : sublimer chaque ingrédient et faire de chaque repas un moment inoubliable.';
$aproposExperience = 'Ambiance chaleureuse et raffinée, Service attentionné et personnalisé, Carte des vins soigneusement sélectionnée, Événements et salons privés sur-mesure';
$aproposEquipe = 'Une équipe passionnée, engagée à faire de chaque visite un souvenir mémorable.';

try {
    $stmt = $pdo->prepare("SELECT block, content FROM site_content WHERE page = 'a-propos' AND status = 'published'");
    $stmt->execute();
    $pageContents = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $aproposHistoire = $pageContents['histoire'] ?? $aproposHistoire;
    $aproposChefVision = $pageContents['chef-vision'] ?? $aproposChefVision;
    $aproposExperience = $pageContents['experience-unique'] ?? $aproposExperience;
    $aproposEquipe = $pageContents['equipe'] ?? $aproposEquipe;

} catch (PDOException $e) {
    error_log("Database error for a-propos page: " . $e->getMessage());
}

?>
  <main class="flex-fill p-4 container" style="max-width: 800px; margin-top: 120px;">
    <h1 class="section-title text-center mb-4">À propos & Notre histoire</h1>
    <div id="apropos-content">
      <!-- Contenu dynamique de l'API sera inséré ici si l'API était utilisée ainsi -->
    </div>
    <section class="mb-5">
      <h2 class="section-title text-center">L'histoire de NYABUNGO</h2>
      <p class="section-text"><?php echo $aproposHistoire; ?></p>
    </section>
    <section class="mb-5">
      <h2 class="section-title text-center">La vision du chef</h2>
      <p class="section-text"><?php echo $aproposChefVision; ?></p>
    </section>
    <section class="mb-5">
      <h2 class="section-title text-center">Une expérience unique</h2>
      <ul class="ps-3 text-secondary mb-4">
        <?php
          $experienceItems = explode(', ', $aproposExperience);
          foreach ($experienceItems as $item) {
              echo '<li>' . htmlspecialchars($item) . '</li>';
          }
        ?>
      </ul>
    </section>
    <section class="mb-5 text-center">
      <h2 class="section-title">Notre équipe</h2>
      <div class="row g-3 mb-4 justify-content-center">
        <div class="col-6 col-md-3"><img src="/assets/chef.jpg" alt="Chef Jean-Claude" class="rounded object-fit-cover w-100" style="height: 150px;"></div>
        <div class="col-6 col-md-3"><img src="/assets/equipe1.jpg" alt="Équipe 1" class="rounded object-fit-cover w-100" style="height: 150px;"></div>
        <div class="col-6 col-md-3"><img src="/assets/equipe2.jpg" alt="Équipe 2" class="rounded object-fit-cover w-100" style="height: 150px;"></div>
        <div class="col-6 col-md-3"><img src="/assets/salle.jpg" alt="Salle" class="rounded object-fit-cover w-100" style="height: 150px;"></div>
      </div>
      <p class="text-muted section-text"><?php echo $aproposEquipe; ?></p>
    </section>
  </main>
<?php
require_once __DIR__ . '/includes/footer.php';
?> 