<?php
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require "database/database.php";


$stmt = $conn->prepare("SELECT * FROM messages");
$stmt->execute();
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beheer Berichten</title>
</head>
<body>
    <h1>Beheer Berichten</h1>
    
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
                   
                    <a href="delete_message.php?id=<?php echo $message['id']; ?>">Verwijder</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <p><a href="admin_dashboard.php">Terug naar het dashboard</a></p>
</body>
</html>
