<?php

session_start();
require "database/database.php";
include "partials/header.php";

if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit();
} else if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$user_id = (int) $_SESSION['user_id'];  

require "database/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Ongeldige CSRF-token.");
    }

    if (isset($_POST['gebruikersnaam']) && isset($_POST['wachtwoord'])) {
        $gebruikersnaam = trim($_POST['gebruikersnaam']);
        $wachtwoord = trim($_POST['wachtwoord']);

        if (!empty($wachtwoord)) {
            $wachtwoord_hash = password_hash($wachtwoord, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE users SET gebruikersnaam = :gebruikersnaam, wachtwoord = :wachtwoord WHERE id = :user_id");
            $stmt->bindParam(':gebruikersnaam', $gebruikersnaam, PDO::PARAM_STR);
            $stmt->bindParam(':wachtwoord', $wachtwoord_hash, PDO::PARAM_STR);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        } else {
           
            $stmt = $conn->prepare("UPDATE users SET gebruikersnaam = :gebruikersnaam WHERE id = :user_id");
            $stmt->bindParam(':gebruikersnaam', $gebruikersnaam, PDO::PARAM_STR);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        }

        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo "Profiel succesvol bijgewerkt!";
        } else {
            echo "Geen wijzigingen aangebracht.";
        }
    }
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

try {
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user !== false && isset($user['gebruikersnaam'])) {
        echo "Welkom, " . htmlspecialchars($user['gebruikersnaam']);
    } else {
        
    }
} catch (PDOException $e) {
    echo "Fout bij uitvoeren query: " . $e->getMessage();
    exit;
}

?>

</header> 

<main> 
    <link rel="stylesheet" href="css/main.css">
    <div class="profile-container">
        <h1>Welkom, <?= isset($user['gebruikersnaam']) ? htmlspecialchars($user['gebruikersnaam']) : 'Onbekend' ?>!</h1>

        <div class="profile-info">
            <p><strong>Gebruikersnaam:</strong> <?= isset($user['gebruikersnaam']) ? htmlspecialchars($user['gebruikersnaam']) : 'Onbekend' ?></p>
        </div>
      
        <h2>Profiel bewerken</h2>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="input-group">
                <label for="gebruikersnaam">gebruikersnaam</label>
                <input type="text" name="gebruikersnaam" id="gebruikersnaam" value="<?= isset($user['gebruikersnaam']) ? htmlspecialchars($user['gebruikersnaam']) : '' ?>" required>
            </div>
            <div class="input-group">
                <label for="wachtwoord"> wachtwoord</label>
                <input type="password" name="wachtwoord" id="wachtwoord">
                <label for="nieuwe_user_id">Nieuwe gebruikers-ID</label>
                <input type="text" name="nieuwe_user_id" id="nieuwe_user_id" value="<?= isset($user['id']) ? htmlspecialchars($user['id']) : '' ?>" required>
                <label for="wachtwoord">nieuwe wachtwoord</label>
                <input type="password" name="wachtwoord" id="wachtwoord" placeholder="Vul je nieuwe wachtwoord in" value="<?= isset($user['wachtwoord']) ? htmlspecialchars($user['wachtwoord']) : '' ?>" required>
            </div>
            <button type="submit" class="btn">Profiel bijwerken</button>
        </form>
    </div>
</main>

</body>
</html>
