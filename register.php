<?php
require_once 'auth/auth.php';

// Rediriger si déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$pageTitle = "Inscription";
require_once 'includes/header.php';
?>

<style>
    body {
        background-image: url('images/TAB.png');
        background-size: cover;
        background-position: center;
    }
    .card {
        background-color: rgba(88, 61, 61, 0.5);
        border: 2px solid rgba(10, 9, 9, 0.8);
        border-radius: 10px;
    }
    .form-title {
       color: #cccccc;
    }
    .label-brown {
        color: brown;
    }
    label {
        color: rgb(252, 249, 249);
    }
    .p {
        color: rgb(221, 216, 216);
    }
    .btn {
        color: rgb(255, 255, 255);
    }
    .form-title {
        text-align: center;
        margin-bottom: 20px;
        font-family: Arial, Helvetica, sans-serif;
        font-size: larger;
        color: white;
    }
    .logo {
        font-size: 50px;
        text-align: center;
        color: #6f42c1;
    }
    .container {
        display: flex;
        align-items: center;
        background-color: rgba(44, 18, 10, 0.4);
        border-radius: 8px 0 0 8px;
        border: solid;
        padding: 30px;
        width: 900px;
        height: 500px;
    }
    .logo {
        font-size: 50px;
        color: #6f42c1;
        margin-right: 20px;
    }
    .form-title {
        margin: 0 20px;
        font-size: 14px;
        text-align: left;
        color: rgb(240, 233, 233);
        font-family: Arial, Helvetica, sans-serif;
    }
    .form-container {
        display: flex;
        flex-direction: column;
        width: 100%;
        color: rgb(240, 233, 233);
        font-family: Arial, Helvetica, sans-serif;
    }
    .form-group {
        margin-bottom: 15px;
        font-size: larger;
    }
    label {
        display: block;
        margin-bottom: 5px;
    }
    input {
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        border: 1px solid #cccccc;
        border-radius: 5px;
    }
    .form-check {
        margin: 10px 0;
        text-align: left;
    }
    .hr {
        font-size: larger;
    }
    .btn {
        background-color: #6f42c1;
        color: white;
        padding: 10px;
        border: none;
        border-radius: 5px;
        width: 100%;
        cursor: pointer;
        font-size: 16px;
    }
    .btn:hover {
        background-color: #5a32a2;
    }
</style>

<div class="container border">
    <div class="logo">👨🏻‍🏫</div>
    <div class="t">
        <div class="form-title text-white">
            <h4 class="hr text-white">✨Inscrivez-vous sur notre bibliothèque et découvrez un monde de livres passionnants✨</h4>
        </div>
    </div>
    <div class="form-container">
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <form action="auth/auth.php" method="POST" onsubmit="return validateForm()">
            <input type="hidden" name="action" value="register">
            <div class="form-group">
                <label for="nom">Votre nom</label>
                <input type="text" id="nom" name="nom" placeholder="Nom complet" required>
            </div>
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" placeholder="E-mail" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" placeholder="Mot de passe" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirmer le mot de passe" required>
            </div>
            <button type="submit" class="btn">Créer le compte</button>
            <br><br>
        </form>
    </div>
</div>

<script>
function validateForm() {
    var password = document.getElementById("password").value;
    var confirm_password = document.getElementById("confirm_password").value;
    
    if (password != confirm_password) {
        alert("Les mots de passe ne correspondent pas !");
        return false;
    }
    return true;
}
</script>

<?php require_once 'includes/footer.php'; ?> 