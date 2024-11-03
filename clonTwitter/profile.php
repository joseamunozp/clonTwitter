<?php
session_start();
require 'db.php';

// Verifica si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$userId = $_SESSION['user_id'];
$profileId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isOwnProfile = ($userId === $profileId);
$errorMessage = "";

// Obtiene la información del perfil del usuario
$stmt = $pdo->prepare("SELECT id, username, email, description FROM users WHERE id = :id");
$stmt->execute(['id' => $profileId]);
$profileUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profileUser) {
    $errorMessage = "El usuario no existe.";
}

// Verifica si el usuario actual sigue al perfil visitado
$stmt = $pdo->prepare("SELECT * FROM follows WHERE users_id = :userId AND userToFollowId = :profileId");
$stmt->execute(['userId' => $userId, 'profileId' => $profileId]);
$isFollowing = $stmt->fetch() !== false;

// Maneja las acciones de seguir/dejar de seguir
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['follow_action'])) {
    if ($_POST['follow_action'] === 'follow') {
        $stmt = $pdo->prepare("INSERT INTO follows (users_id, userToFollowId) VALUES (:userId, :profileId)");
        $stmt->execute(['userId' => $userId, 'profileId' => $profileId]);
        $isFollowing = true;
    } elseif ($_POST['follow_action'] === 'unfollow') {
        $stmt = $pdo->prepare("DELETE FROM follows WHERE users_id = :userId AND userToFollowId = :profileId");
        $stmt->execute(['userId' => $userId, 'profileId' => $profileId]);
        $isFollowing = false;
    }
}

// Obtiene los tweets del usuario en el perfil
$stmt = $pdo->prepare("SELECT text, createDate FROM publications WHERE userId = :profileId ORDER BY createDate DESC");
$stmt->execute(['profileId' => $profileId]);
$tweets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo htmlspecialchars($profileUser['username']); ?> - Clon de Twitter</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <?php if ($errorMessage): ?>
            <p class="error-message"><?php echo $errorMessage; ?></p>
        <?php else: ?>
            <h2>Perfil de <?php echo htmlspecialchars($profileUser['username']); ?></h2>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($profileUser['email']); ?></p>
            <p><strong>Descripción:</strong> <?php echo htmlspecialchars($profileUser['description']); ?></p>

            <!-- Botón de seguir o dejar de seguir -->
            <?php if (!$isOwnProfile): ?>
                <form action="profile.php?id=<?php echo $profileId; ?>" method="POST">
                    <?php if ($isFollowing): ?>
                        <button type="submit" name="follow_action" value="unfollow">Dejar de Seguir</button>
                    <?php else: ?>
                        <button type="submit" name="follow_action" value="follow">Seguir</button>
                    <?php endif; ?>
                </form>
            <?php else: ?>
                <p><a href="edit_profile.php">Editar Perfil</a></p>
            <?php endif; ?>

            <!-- Lista de tweets del usuario -->
            <h3>Tweets de <?php echo htmlspecialchars($profileUser['username']); ?></h3>
            <?php if (count($tweets) > 0): ?>
                <ul>
                    <?php foreach ($tweets as $tweet): ?>
                        <li>
                            <p><?php echo htmlspecialchars($tweet['text']); ?></p>
                            <small><?php echo htmlspecialchars($tweet['createDate']); ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Este usuario no tiene tweets.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
