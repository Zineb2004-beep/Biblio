<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Livre.php';
require_once __DIR__ . '/auth/auth.php';

$database = new Database();
$db = $database->getConnection();
$livre = new Livre($db);
$auth = new Auth();

// Récupérer les derniers livres
$stmt = $livre->lireTous();
$derniers_livres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement du formulaire de contact
$success_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['contact_form'])) {
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';
    
    if (!empty($nom) && !empty($email) && !empty($message)) {
        try {
            $sql = "INSERT INTO messages (nom, email, message, date_envoi) VALUES (:nom, :email, :message, NOW())";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':nom' => $nom,
                ':email' => $email,
                ':message' => $message
            ]);
            $success_message = "Votre message a été envoyé avec succès!";
        } catch(PDOException $e) {
            $error_message = "Erreur: " . $e->getMessage();
        }
    } else {
        $error_message = "Tous les champs sont obligatoires.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
   <head>
      <!-- basic -->
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <!-- mobile metas -->
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="viewport" content="initial-scale=1, maximum-scale=1">
      <!-- site metas -->
      <title>Accueil</title>
      <meta name="keywords" content="">
      <meta name="description" content="">
      <meta name="author" content="">
      <!-- bootstrap css -->
      <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
      <!-- style css -->
      <link rel="stylesheet" type="text/css" href="css/style.css">
      <!-- Responsive-->
      <link rel="stylesheet" href="css/responsive.css">
      <!-- fevicon -->
      <link rel="icon" href="images/fevicon.png" type="image/gif" />
      <!-- Scrollbar Custom CSS -->
      <link rel="stylesheet" href="css/jquery.mCustomScrollbar.min.css">
      <!-- Tweaks for older IEs-->
      <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
      <!-- owl stylesheets --> 
      <link rel="stylesheet" href="css/owl.carousel.min.css">
      <link rel="stylesheet" href="css/owl.theme.default.min.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css" media="screen">
      <link href="https://unpkg.com/gijgo@1.9.13/css/gijgo.min.css" rel="stylesheet" type="text/css" />
   </head>
   <style>
      .contact{
    padding: 70px 0 80px;
    background: #000;
    
  }
  .contact-content{
    display: flex;
    margin: 20px;
    gap: 48px;
    align-items: flex-start;
    justify-content: space-between;
  }
  .form-input{
    width: 50%;
    height: 60px;
    padding: 0 12px;
    outline: none;
    margin-bottom: 16px;
    background: #fff;
    border-radius: 5px;
    border: 5px ;
  }
  .contact-form{
    max-width: 50%;
  }
  .contact-form textarea .form-input {
    height: 100px;
    padding: 12px;
    resize: vertical;
  
  }
  .contact-titre {
    color: white;
    padding: 50px;
    margin: 20px;
    margin-top: 20px;
    padding-top: 20px;
    text-align: center;
    font-weight: bolder ;
  }
  .contact-info{
    color: white;
    display: flex;
    gap: 20px;
    margin: 20px 0;
    align-items: center;
  }
  .submit-button{
    padding: 10px 26px;
    margin-top: 10px;
    color: #235347;
    background-color: #87bc98;
    border-radius: 10px;
    border: 1px solid var(--medium-gray-color);
    transition:0.3s ease ;
  }
  .submit-button:hover{
    background: #DAF1DE;
  }
 
   </style>
   <body>
      <!-- header section start -->
      <?php include 'includes/header.php'; ?>
      <!-- header section end -->
      <!-- banner section end -->
      <div class="banner_section layout_padding">
         <div class="container">
            <div class="row">
               <div class="col-md-6">
                  <div class="banner_taital">Découvrez <br>Notre Monde de Livres</div>
                  <p class="banner_text">Explorez notre vaste collection de livres et profitez de nos services de bibliothèque. </p>
                  <div class="see_bt">
                     <a href="catalogue.php">En savoir plus</a></div>
               </div>
               
            </div>
         </div>
      </div>
      <!-- banner section end -->
      <!-- arrival section start -->
      <div class="arrival_section layout_padding bg-light">
         <div class="container">
            <div class="row">
               <div class="col-sm-6 col-lg-4">
                  <div class="image_1">
                     <h2 class="jesusroch_text">Dostoevsky</h2>
                     <p class="movie_text"></p>
                  </div>
               </div>
               <div class="col-sm-6 col-lg-4">
                  <div class="image_2">
                     <h2 class="jesusroch_text">Dostoevsky</h2>
                     <p class="movie_text"></p>
                  </div>
               </div>
               <div class="col-sm-8 col-lg-4 ">
                  <h1 class="arrival_text">É C R I V A I N</h1>
                  <div class="movie_main">
                     <div class="mins_text_1">Russe</div>
                     <div class="mins_text">ÉCRIVAIN</div>
                     
                  </div>
                  <p class="long_text"> <b> Fiodor Dostoïevski</b> (1821-1881) était un écrivain russe majeur, connu pour ses romans psychologiques comme Crime et Châtiment. Marqué par son exil en Sibérie après son arrestation, il a exploré des thèmes profonds comme la foi, la culpabilité et la rédemption, faisant de lui une figure incontournable de la littérature mondiale.</p>
                  <div class="rating_main">
                     <div class="row">
                      
                     </div>
                  </div>

               </div>
            </div>
         </div>
      </div>
      <!-- arrival section end -->
      <!-- movies section start -->
      <div class="movies_section layout_padding">
         <div class="container">
            
            <div class="movies_section_2 layout_padding">
               <h2 class="letest_text">Dernier Livre</h2>
               <div class="movies_main">
                  <div class="iamge_movies_main">
                     <?php foreach($derniers_livres as $livre): ?>
                     <div class="iamge_movies">
                        <div class="image_3">
                           <img src="<?php echo htmlspecialchars($livre['image_url']); ?>" class="image" style="width:100%">
                           <div class="middle">
                              
                           </div>
                        </div>
                        <h1 class="code_text"><?php echo htmlspecialchars($livre['titre']); ?></h1>
                       
                     </div>
                     <?php endforeach; ?>
                  </div>
               </div>
            </div>
            <div class="movies_section_2 layout_padding">
               <h2 class="letest_text">Roman</h2>

               <div class="movies_main">
                  <div class="iamge_movies_main">
                     <div class="iamge_movies">
                        <div class="image_3">
                           <img src="images/Antigone.jpeg" class="image" style="width:100%">
                           <div class="middle">
                              
                           </div>
                        </div>
                        <h1 class="code_text">Antigone</h1>
                       
                     </div>
                     <div class="iamge_movies">
                        <div class="image_3">
                           <img src="images/L'Idiot - Dostoievski.jpeg" class="image" style="width:100%">
                           <div class="middle">
                              
                           </div>
                        </div>
                        <h1 class="code_text">L'idiot</h1>
                       
                     </div>
                     <div class="iamge_movies">
                        <div class="image_3">
                           <img src="images/Crime et châtiment.jpeg" class="image" style="width:100%">
                           <div class="middle">
                              <div class="playnow_bt">Play Now</div>
                           </div>
                        </div>
                        <h1 class="code_text">Crime et chatiment</h1>
                       
                     </div>
                     <div class="iamge_movies">
                        <div class="image_3">
                           <img src="images/,,War and Peace'' by Leo Tolstoy ( 1867 )_.jpeg" class="image" style="width:100%">
                           <div class="middle">
                              
                           </div>
                        </div>
                        <h1 class="code_text">War and Peace</h1>
                       
                     </div>
                     <div class="iamge_movies">
                        <div class="image_3">
                           <img src="images/Dead Souls.jpeg" class="image" style="width:100%">
                           <div class="middle">
                              
                           </div>
                        </div>
                        <h1 class="code_text">Dead Souls</h1>
                       
                     </div>
                  </div>
               </div>
            </div>
            <div class="movies_section_2 layout_padding">
               <h2 class="letest_text">Policier</h2>
               
               <div class="movies_main">
                  <div class="iamge_movies_main">
                     <div class="iamge_movies">
                        <div class="image_3">
                           <img src="images/The 100 greatest novels of all time_ The list.jpeg" class="image" style="width:80%">
                           <div class="middle">
                              
                           </div>
                        </div>
                        <h1 class="code_text">Le comte de Monte-Cristo</h1>
                       
                     </div>
                     <div class="iamge_movies">
                        <div class="image_3">
                           <img src="images/Sherlock Holmes _ La BD dont Vous êtes le Héros T4 - Le Défi d'Irène Adler - TRIBULLES.jpeg" class="image" style="width:80%">
                           <div class="middle">
                            
                           </div>
                        </div>
                        <h1 class="code_text">Le défi d'irène adler</h1>
                       
                     </div>
                     <div class="iamge_movies">
                        <div class="image_3">
                           <img src="images/Sherlock Holmes et la Bête des Stapleton (Grand format).jpeg" class="image" style="width:80%">
                           <div class="middle">
                              
                           </div>
                        </div>
                        <h1 class="code_text">Sherlock HolmseLa bete des stapleton</h1>
                       
                        </div>
                     </div>
                    
                  </div>
               </div>
            </div>
             <div class="seebt_1"><a href="catalogue.php">Découvrez Plus</a></div>
         </div>
      </div>
      <!-- movies section end-->
      <!-- newsletter section start -->
      <section class="contact"> 
         <h1 class="contact-titre">Contactez-nous</h1>
         
         <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" style="max-width: 50%; margin: 0 auto;">
                <?php echo $success_message; ?>
            </div>
         <?php endif; ?>
         
         <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" style="max-width: 50%; margin: 0 auto;">
                <?php echo $error_message; ?>
            </div>
         <?php endif; ?>
         
         <div class="contact-content">
             <ul class="contact-info-list">
                 <li class="contact-info">
                     <p>BP 2400 Hay Hassani Route d'essaouira 40000</p>
                 </li>
                 <li class="contact-info">
                     <p>Biblio@gmail.com</p>
                 </li>
                 <li class="contact-info">
                     <p>(+212) 5 24 34 01 25</p>
                 </li>
             </ul>
             <form action="" method="POST" class="contact-form">
                 <input type="hidden" name="contact_form" value="1">
                 <input type="text" name="nom" placeholder="Votre nom" class="form-input" required>
                 <input type="email" name="email" placeholder="Votre email" class="form-input" required>
                 <textarea name="message" placeholder="Votre message" class="form-input" required></textarea> <br>
                 <button type="submit" class="btn btn-primary submit-button">Envoyer</button>
             </form>
         </div>
     
      
 </section>
      <!-- newsletter section end -->
      <!-- cooming  section start -->
      <div class="cooming_section layout_padding">
         <div class="container">
            <div class="row">
               <div class="col-md-6">
                  <div class="image_17">
                     <div class="image_17"><img src="images/Le Dernier Jour d'un condamné, de Victor Hugo de Stanislas Gros, Stanislas Gros, Marie Galopin - Album.jpeg"></div>
                  </div>
               </div>
               <div class="col-md-6">
                  <h1 class="number_text">01</h1>
                  <h1 class="Cooming_soon_taital">Cooming soon</h1>
                  <p class="long_text_1">Le Dernier Jour d'un Condamné" est un roman publié en 1829 qui raconte l'histoire d'un homme condamné à mort. Le récit est écrit à la première personne, ce qui permet au lecteur de plonger dans les pensées et les émotions du protagoniste</p>
                 
               </div>
            </div>
         </div>
      </div>
      <!-- cooming  section end -->
      <!-- footer  section start -->
      <?php include 'includes/footer.php'; ?>
      
      <!-- copyright section end -->
      <!-- Javascript files-->
      <script src="js/jquery.min.js"></script>
      <script src="js/popper.min.js"></script>
      <script src="js/bootstrap.bundle.min.js"></script>
      <script src="js/jquery-3.0.0.min.js"></script>
      <!-- sidebar -->
      <script src="js/jquery.mCustomScrollbar.concat.min.js"></script>
      <script src="js/custom.js"></script>
      <!-- javascript --> 
      <script src="js/owl.carousel.js"></script>
      <script src="https:cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.js"></script>
      <script src="https://unpkg.com/gijgo@1.9.13/js/gijgo.min.js" type="text/javascript"></script>
      <script>
         $('#datepicker').datepicker({
             uiLibrary: 'bootstrap4'
         });
      </script>
   </body>
</html>