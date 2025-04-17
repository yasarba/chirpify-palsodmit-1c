<?php
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require "database/database.php";


$stmt_messages = $conn->prepare("SELECT * FROM messages");
$stmt_messages->execute();
$messages = $stmt_messages->fetchAll(PDO::FETCH_ASSOC);


if (isset($_GET['action']) && $_GET['action'] == 'delete_message' && isset($_GET['id'])) {
    $message_id = $_GET['id'];

    
    $stmt_delete_message = $conn->prepare("DELETE FROM messages WHERE id = :id");
    $stmt_delete_message->bindParam(':id', $message_id);
    $stmt_delete_message->execute();

    
    header("Location: admin_dashboard.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beheerder Dashboard</title>
</head>
<body>
    <h1>Welkom Admin!</h1>
    <p>Je hebt toegang tot het beheerderdashboard.</p>
    
  
    <h2>Beheer Berichten</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Bericht</th>
                <th>Gebruiker ID</th>
                <th>Likes</th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($messages as $message): ?>
            <tr>
                <td><?php echo $message['id']; ?></td>
                <td><?php echo htmlspecialchars($message['content']); ?></td>
                <td><?php echo $message['user_id']; ?></td>
                <td><?php echo $message['likes']; ?></td>
                <td>
                    
                    <a href="admin_dashboard.php?action=delete_message&id=<?php echo $message['id']; ?>">Verwijder</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <hr>

   
    <h2>Uitloggen</h2>
    <p><a href="logout.php">Klik hier om uit te loggen</a></p>

</body>
</html>
