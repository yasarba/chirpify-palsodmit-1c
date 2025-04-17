<?php
session_start();
include 'partials/header.php';

require 'database/database.php';




if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


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

    header("Location: berichten.php");
    exit();
}


if (isset($_GET['like']) && is_numeric($_GET['like'])) {
    $message_id = (int)$_GET['like'];

    $conn->beginTransaction();
    try {
        $check_msg = $conn->prepare("SELECT id FROM messages WHERE id = ?");
        $check_msg->execute([$message_id]);
        if (!$check_msg->fetch()) {
            throw new Exception("Bericht niet gevonden");
        }

        $check_like = $conn->prepare("SELECT id FROM likes WHERE message_id = ? AND user_id = ?");
        $check_like->execute([$message_id, $user_id]);

        if (!$check_like->fetch()) {
            $like_stmt = $conn->prepare("INSERT INTO likes (message_id, user_id) VALUES (?, ?)");
            $like_stmt->execute([$message_id, $user_id]);

            $update_stmt = $conn->prepare("UPDATE messages SET likes = (SELECT COUNT(*) FROM likes WHERE message_id = ?) WHERE id = ?");
            $update_stmt->execute([$message_id, $message_id]);

            $conn->commit();
            $_SESSION['success_message'] = "Je hebt het bericht geliked!";
        } else {
            $conn->rollBack();
            $_SESSION['error_message'] = "Je hebt dit bericht al geliked!";
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error_message'] = "Er ging iets mis met liken.";
    }

    header("Location: berichten.php");
    exit();
}


if (isset($_GET['unlike']) && is_numeric($_GET['unlike'])) {
    $message_id = $_GET['unlike'];

    $conn->beginTransaction();
    try {
        $delete_stmt = $conn->prepare("DELETE FROM likes WHERE message_id = ? AND user_id = ?");
        $delete_stmt->execute([$message_id, $user_id]);

        $update_stmt = $conn->prepare("UPDATE messages SET likes = (SELECT COUNT(*) FROM likes WHERE message_id = ?) WHERE id = ?");
        $update_stmt->execute([$message_id, $message_id]);

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error_message'] = "Er ging iets mis met unliken.";
    }

    header("Location: berichten.php");
    exit();
}


$stmt = $conn->prepare("SELECT gebruikersnaam FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();


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

<div class="container">
    <h1>Berichten</h1>
   
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <form action="berichten.php" method="POST" class="message-form">
        <textarea name="content" placeholder="Wat wil je delen?" required></textarea>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <button type="submit" class="btn">Plaatsen</button>
    </form>

    <div class="messages">
        <?php if (empty($messages)): ?>
            <p>Geen berichten gevonden.</p>
        <?php else: ?>
            <?php foreach ($messages as $message): ?>
                <div class="message">
                    <p class="message-content"><?php echo htmlspecialchars($message['content']); ?></p>
                    <div class="message-meta">
                        <span>Door: <?php echo htmlspecialchars($message['gebruikersnaam']); ?></span>
                        <span>Likes: <?php echo $message['like_count']; ?></span>
                    </div>
                    <div class="message-actions">
                        <?php if ($message['user_liked']): ?>
                            <a href="berichten.php?unlike=<?php echo $message['id']; ?>" class="btn">Unlike</a>
                        <?php else: ?>
                            <a href="berichten.php?like=<?php echo $message['id']; ?>" class="btn">Like</a>
                        <?php endif; ?>

                        <?php if ((int)$message['user_id'] === (int)$user_id): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="delete" value="<?php echo $message['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <button type="submit" class="btn delete-button">Verwijderen</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <p><a href="index.php" class="btn">Terug naar home</a></p>
</div>

</body>
</html>
