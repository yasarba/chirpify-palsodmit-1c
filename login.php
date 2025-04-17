<?php
session_start();
require "database/database.php";
include "partials/header.php";
?>

<div class="login-wrapper">
    <div class="login-container">
        <h2>Welcome Back</h2>
        <form id="loginForm" method="POST" action="login.php">
            <div class="input-group">
                <label for="username">Gebruikersnaam</label>
                <input type="text" id="username" name="username" placeholder="Vul je gebruikersnaam in" required>
            </div>
            <div class="input-group">
                <label for="password">Wachtwoord</label>
                <input type="password" id="password" name="password" placeholder="Vul je wachtwoord in" required>
            </div>
            <button type="submit" class="btn btn-login">Inloggen</button>
        </form>
       
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="error-message">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        
        <p class="register-link">Nog geen account? <a href="register-form.php">Registreer hier</a></p>
    </div>
</div>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['username']) && isset($_POST['password'])) {
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE gebruikersnaam = :username");
        $stmt->bindParam(":username", $_POST['username']);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);  

        if (!$user) {
            $_SESSION['error_message'] = 'Fout: Gebruiker bestaat niet.';
            header("Location: login.php");
            exit;
        }

     
        $iscorrect = password_verify($_POST['password'], $user['wachtwoord']);
        if (!$iscorrect) {
            $_SESSION['error_message'] = 'Fout login! Verkeerd wachtwoord.';
            header("Location: login.php");
            exit;
        } else {
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['gebruikersnaam'] = $user['gebruikersnaam'];
            $_SESSION['rol'] = $user['rol']; 

          
            if ($user['rol'] === 'admin') {
                
                header("Location: beheerder.php");
                exit;
            } else {
               
                header("Location: index.php");
                exit;
            }
        }
    } else {
        $_SESSION['error_message'] = 'Vul zowel gebruikersnaam als wachtwoord in.';
        header("Location: login.php");
        exit;
    }
}
?>
