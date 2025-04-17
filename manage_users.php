<?php

session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
  
    header("Location: login.php");
    exit();
}


require "database/database.php";


$stmt = $conn->prepare("SELECT * FROM users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beheer Gebruikers</title>
</head>
<body>
    <h1>Beheer Gebruikers</h1>
    <p><a href="beheerder.php">Terug naar het dashboard</a></p>

    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Gebruikersnaam</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Geactiveerd</th>
                <th>Datum aangemaakt</th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody>
            <?php
           
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                echo "<td>" . htmlspecialchars($user['gebruikersnaam']) . "</td>";
                echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                echo "<td>" . htmlspecialchars($user['rol']) . "</td>";
                echo "<td>" . ($user['geactiveerd'] ? 'Ja' : 'Nee') . "</td>";
                echo "<td>" . htmlspecialchars($user['datum_aangemaakt']) . "</td>";
                echo "<td><a href='edit_user.php?id=" . $user['id'] . "'>Bewerken</a> | <a href='delete_user.php?id=" . $user['id'] . "'>Verwijderen</a></td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

</body>
</html>
