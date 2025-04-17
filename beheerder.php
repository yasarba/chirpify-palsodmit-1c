<?php
session_start();
include 'partials/header.php';

require 'database/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['rol']; 


if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['content'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = "Ongeldige CSRF-token.";
    } else {
        $content = trim($_POST['content']);

        if (!empty($content)) {
            $stmt = $conn->prepare("INSERT INTO messages (user_id, content, likes) VALUES (?, ?, 0)");
            if ($stmt->execute([$user_id, $content])) {
                $_SESSION['success_message'] = "Je bericht is geplaatst!";
            } else {
                $_SESSION['error_message'] = "Er ging iets mis bij het plaatsen van je bericht.";
            }
        } else {
            $_SESSION['error_message'] = "Bericht mag niet leeg zijn.";
        }
    }
    header("Location: berichten.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete']) && is_numeric($_POST['delete'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = "Ongeldige CSRF-token.";
        header("Location: berichten.php");
        exit();
    }

    $message_id = $_POST['delete'];

    
    if ($user_role === 'admin') {
        $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
        if ($stmt->execute([$message_id])) {
            $_SESSION['success_message'] = "Het bericht is verwijderd.";
        } else {
            $_SESSION['error_message'] = "Er ging iets mis bij het verwijderen van het bericht.";
        }
    } else {
       
        $stmt = $conn->prepare("SELECT user_id FROM messages WHERE id = ?");
        $stmt->execute([$message_id]);
        $message = $stmt->fetch();

        if ($message && $message['user_id'] == $user_id) {
            $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
            $stmt->execute([$message_id]);
            $_SESSION['success_message'] = "Je bericht is verwijderd.";
        } else {
            $_SESSION['error_message'] = "Je mag alleen je eigen berichten verwijderen.";
        }
    }

    header("Location: berichten.php");
    exit();
}


$stmt = $conn->prepare("
    SELECT 
        m.id,
        m.content,
        m.user_id,
        u.gebruikersnaam,
        (SELECT COUNT(*) FROM likes WHERE message_id = m.id) as like_count,
        EXISTS(SELECT 1 FROM likes WHERE message_id = m.id AND user_id = ?) as user_liked
    FROM messages m
    LEFT JOIN users u ON m.user_id = u.id
    ORDER BY m.id DESC
");
$stmt->execute([$user_id]);
$messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Berichten</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <h1>Berichten</h1>
    

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <h2>Plaats een nieuw bericht</h2>
    <form action="berichten.php" method="POST">
        <textarea name="content" rows="4" cols="50" placeholder="Wat wil je delen?" required></textarea><br>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <button type="submit">Bericht plaatsen</button>
    </form>

    <h2>Alle berichten</h2>

    <?php if (empty($messages)): ?>
        <p>Geen berichten gevonden.</p>
    <?php else: ?>
        <?php foreach ($messages as $message): ?>
            <div class="message">
                <p><?php echo htmlspecialchars($message['content']); ?></p>
                <p>Geplaatst door: <?php echo htmlspecialchars($message['gebruikersnaam']); ?></p>
                <p>Likes: <?php echo $message['like_count']; ?></p>

                
                <?php if ($message['user_liked']): ?>
                    <a href="berichten.php?unlike=<?php echo $message['id']; ?>" class="unlike-button">Unlike</a>
                <?php else: ?>
                    <a href="berichten.php?like=<?php echo $message['id']; ?>" class="like-button">Like</a>
                <?php endif; ?>

               
                <?php if ((int)$message['user_id'] === (int)$user_id || $user_role === 'admin'): ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="delete" value="<?php echo $message['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <button type="submit" class="delete-button">Verwijderen</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <h2>Uitloggen</h2>
    <p><a href="logout.php">Log uit</a></p>
    <p><a href="index.php">Terug naar home</a></p>
</body>
</html>



<ul>
    

            <li><a href="manage_users.php">👤 Gebruikers beheren</a></li>
            <li><a href="berichten.php">💬 Berichten beheren</a></li>
            <li><a href="manage_settings.php">⚙️ Instellingen</a></li>
        </ul>