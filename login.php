<?php
require_once 'auth/auth.php';
require_once 'config/database.php';

// Initialiser l'objet Auth
$auth = new Auth();

// Vérifier s'il y a une action de déconnexion
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    $auth->logout();
    header('Location: login.php?success=' . urlencode('Vous avez été déconnecté avec succès'));
    exit();
}

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;
    
    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs";
    } else {
        if ($auth->login($email, $password, $remember)) {
            // Redirection selon le rôle
            if ($auth->isAdmin()) {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: adherent.php');
            }
            exit();
        } else {
            $error = "Email ou mot de passe incorrect";
        }
    }
}

// Rediriger si déjà connecté et pas d'action de déconnexion
if ($auth->isLoggedIn() && (!isset($_GET['action']) || $_GET['action'] != 'logout')) {
    if ($auth->isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: adherent.php');
    }
    exit();
}

$pageTitle = "Connexion";
require_once 'includes/header.php';
?>

<style>
    body {
        font-family: 'Poppins', Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-image: url('images/TAB.png');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        min-height: 100vh;
    }
    
    .main-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 150px);
        padding: 20px;
    }
    
    .container {
        display: flex;
        justify-content: center;
        align-items: stretch;
        max-width: 1000px;
        margin: 20px;
        background: rgba(44, 18, 10, 0.4);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    }
    
    .welcome {
        text-align: center;
        color: #ffffff;
        padding: 40px;
        flex: 1;
        background: linear-gradient(135deg, rgba(88, 61, 61, 0.8), rgba(44, 18, 10, 0.8));
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    
    .welcome::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url('images/pattern.png');
        opacity: 0.1;
    }
    
    .welcome h1 {
        font-size: 3em;
        margin-bottom: 10px;
        position: relative;
        font-weight: 700;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }
    
    .welcome h2 {
        font-size: 1.5em;
        margin-bottom: 30px;
        position: relative;
        color: rgba(255, 255, 255, 0.9);
    }
    
    .login-form {
        padding: 40px;
        flex: 1;
        background: rgba(44, 18, 10, 0.4);
        backdrop-filter: blur(10px);
        border-left: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .login-form h2 {
        color: #ffffff;
        font-size: 2em;
        margin-bottom: 30px;
        text-align: center;
        font-weight: 600;
    }
    
    .form-group {
        margin-bottom: 25px;
    }
    
    .form-group label {
        color: #ffffff;
        display: block;
        margin-bottom: 8px;
        font-size: 0.9em;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
    
    .form-group input {
        width: 100%;
        padding: 12px 15px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 5px;
        color: #ffffff;
        font-size: 1em;
        transition: all 0.3s ease;
    }
    
    .form-group input:focus {
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.5);
        outline: none;
    }
    
    .btn-primary {
        background: linear-gradient(45deg, #583D3D, #2C120A);
        border: none;
        padding: 12px 20px;
        color: #ffffff;
        border-radius: 5px;
        font-size: 1.1em;
        font-weight: 500;
        letter-spacing: 1px;
        transition: all 0.3s ease;
        cursor: pointer;
        width: 100%;
        margin-top: 20px;
    }
    
    .btn-primary:hover {
        background: linear-gradient(45deg, #2C120A, #583D3D);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(44, 18, 10, 0.4);
    }
    
    .smiley {
        font-size: 80px;
        margin: 20px 0;
        position: relative;
        animation: float 3s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }
    
    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border: none;
        border-radius: 5px;
        font-size: 0.9em;
    }
    
    .alert-danger {
        background: rgba(220, 53, 69, 0.9);
        color: #ffffff;
    }
    
    .alert-success {
        background: rgba(40, 167, 69, 0.9);
        color: #ffffff;
    }
    
    .form-footer {
        margin-top: 20px;
        text-align: center;
        color: #ffffff;
    }
    
    .form-footer a {
        color: #ffffff;
        text-decoration: none;
        transition: color 0.3s ease;
    }
    
    .form-footer a:hover {
        color: rgba(255, 255, 255, 0.8);
    }
    
    .remember-me {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        color: #ffffff;
    }
    
    .remember-me input[type="checkbox"] {
        margin-right: 10px;
    }
    
    .password-toggle {
        position: relative;
    }
    
    .password-toggle i {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #ffffff;
        cursor: pointer;
    }
    
    @media (max-width: 768px) {
        .container {
            flex-direction: column;
        }
        
        .welcome {
            padding: 20px;
        }
        
        .login-form {
            border-left: none;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
    }
</style>

<div class="main-container">
    <div class="container">
        <div class="welcome">
            <h1>Bienvenue sur BiblioTech</h1>
            <h2>Votre bibliothèque numérique</h2>
            <div class="smiley">📚</div>
            <p>Connectez-vous pour accéder à votre espace personnel</p>
        </div>
        <div class="login-form">
            <h2>Connexion</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php" id="loginForm" novalidate>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           aria-describedby="emailHelp">
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <div class="password-toggle">
                        <input type="password" id="password" name="password" required
                               aria-describedby="passwordHelp">
                        <i class="fas fa-eye" onclick="togglePassword()"></i>
                    </div>
                </div>
                
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Se souvenir de moi</label>
                </div>
                
                <button type="submit" class="btn-primary">Se connecter</button>
                
                <div class="form-footer">
                    <p>Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const icon = document.querySelector('.password-toggle i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

document.getElementById('loginForm').addEventListener('submit', function(e) {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    if (!email || !password) {
        e.preventDefault();
        alert('Veuillez remplir tous les champs');
        return;
    }
    
    if (!isValidEmail(email)) {
        e.preventDefault();
        alert('Veuillez entrer une adresse email valide');
        return;
    }
});

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}
</script>

<?php require_once 'includes/footer.php'; ?> 