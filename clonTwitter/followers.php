<?php
session_start();
require 'db.php';

// Verifica si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Obtiene el ID del perfil a ver o usa el ID del usuario logueado
$userId = $_SESSION['user_id'];
$profileId = isset($_GET['id']) ? (int)$_GET['id'] : $userId;
$isOwnProfile = ($userId === $profileId);

// Obtiene la información del perfil visitado
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = :id");
$stmt->execute(['id' => $profileId]);
$profileUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profileUser) {
    echo "El usuario no existe.";
    exit;
}

// Obtiene la lista de seguidores del usuario del perfil visitado
$stmt = $pdo->prepare("
    SELECT u.id, u.username
    FROM follows f
    JOIN users u ON f.users_id = u.id
    WHERE f.userToFollowId = :profileId
");
$stmt->execute(['profileId' => $profileId]);
$followers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguidores de <?php echo htmlspecialchars($profileUser['username']); ?> - Clon de Twitter</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Seguidores de <?php echo htmlspecialchars($profileUser['username']); ?></h2>
        <p><a href="profile.php?id=<?php echo $profileId; ?>">Volver al perfil de <?php echo htmlspecialchars($profileUser['username']); ?></a></p>
        
        <?php if (count($followers) > 0): ?>
            <ul>
                <?php foreach ($followers as $user): ?>
                    <li>
                        <a href="profile.php?id=<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p><?php echo htmlspecialchars($profileUser['username']); ?> no tiene seguidores.</p>
        <?php endif; ?>
    </div>
</body>
</html>
