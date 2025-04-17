<?php


include 'database/database.php'; 

session_start(); 


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    
    if (isset($_POST['gebruikersnaam']) && isset($_POST['wachtwoord'])) {
      
        $hash = password_hash($_POST['wachtwoord'], PASSWORD_DEFAULT);

       
        $insert_user = $conn->prepare("INSERT INTO users (gebruikersnaam, wachtwoord) VALUES (:gebruikersnaam, :wachtwoord)");
        $insert_user->bindParam(":gebruikersnaam", $_POST['gebruikersnaam']);
        $insert_user->bindParam(":wachtwoord", $hash);
        $insert_user->execute();

        echo "Account succesvol aangemaakt!";
        header("Location: login.php"); 
        exit();
    }

  
    if (!isset($_SESSION['user_id'])) {
        echo "Je moet ingelogd zijn om een bericht te sturen.";
        exit(); 
    }

    if (isset($_POST['content']) && !empty($_POST['content'])) {
        $content = $_POST['content'];
        $user_id = $_SESSION['user_id']; 

        $stmt = $conn->prepare("INSERT INTO messages (content, user_id) VALUES (:content, :user_id)");
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        echo "Bericht is succesvol verstuurd!";
        header("Location: index.php"); 
        exit();
    } else {
        echo "Je bericht mag niet leeg zijn!";
    }
}


header("Location: index.php");

?>
