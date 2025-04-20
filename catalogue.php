<?php
require_once 'config/database.php';
require_once 'models/Livre.php';
require_once 'auth/auth.php';

$database = new Database();
$db = $database->getConnection();
$livre = new Livre($db);

// Récupérer les livres
$livres = $livre->lireTous();

require_once 'includes/header.php';
?>

<!-- movies section start -->
<div class="movies_section layout_padding">
   <div class="container">
      <h2 class="letest_text">Catalogue des Livres</h2>
      <div class="movies_section_2 layout_padding">
         <div class="row">
            <?php foreach ($livres as $livre): ?>
            <div class="col-lg-4 col-sm-6">
               <div class="movies_main">
                  <div class="movies_img">
                     <img src="<?php echo htmlspecialchars($livre['image_url']); ?>" class="image" style="width:100%; height:400px; object-fit:cover;">
                     <div class="middle">
                        <div class="text_main">
                           <h6 class="play_text"><?php echo htmlspecialchars($livre['titre']); ?></h6>
                           <p class="there_text"><?php echo htmlspecialchars($livre['description']); ?></p>
                           <?php if(isset($_SESSION['user_id'])): ?>
                              <?php if($livre['quantite_disponible'] > 0): ?>
                                 <form action="emprunter.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="livre_id" value="<?php echo $livre['id']; ?>">
                                    <button type="submit" class="btn btn-primary">Emprunter</button>
                                 </form>
                              <?php else: ?>
                                 <button class="btn btn-secondary" disabled>Indisponible</button>
                              <?php endif; ?>
                           <?php else: ?>
                              <a href="role.php" class="btn btn-info">Connectez-vous pour emprunter</a>
                           <?php endif; ?>
                        </div>
                     </div>
                  </div>
                  <h1 class="code_text"><?php echo htmlspecialchars($livre['titre']); ?></h1>
                  <p class="there_text">Par <?php echo htmlspecialchars($livre['auteur']); ?></p>
                  <div class="star_icon">
                     <ul>
                        <li><a href="#"><img src="images/star-icon.png"></a></li>
                        <li><a href="#"><img src="images/star-icon.png"></a></li>
                        <li><a href="#"><img src="images/star-icon.png"></a></li>
                        <li><a href="#"><img src="images/star-icon.png"></a></li>
                        <li><a href="#"><img src="images/star-icon.png"></a></li>
                     </ul>
                  </div>
               </div>
            </div>
            <?php endforeach; ?>
         </div>
      </div>
   </div>
</div>
<!-- movies section end -->

<?php require_once 'includes/footer.php'; ?> 