<?php
require 'database/database.php';
session_start();


if (!isset($_SESSION['gebruikersnaam']) || $_SESSION['beheerder'] != 1) {
    echo "Je hebt geen toestemming om deze actie uit te voeren.";
    exit;
}


if (isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];

   
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
    
    
    if ($stmt->execute()) {
        echo "De gebruiker is succesvol verwijderd.";
    } else {
        echo "Er is een fout opgetreden bij het verwijderen van de gebruiker.";
    }
} else {
    echo "Geen gebruikers-ID opgegeven.";
}
?>
