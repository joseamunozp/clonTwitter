<?php
session_start();
require 'db.php';

// Verifica si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Obtiene la información del usuario logueado
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, email, description FROM users WHERE id = :id");
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Inicializa los mensajes de error y éxito
$errorMessage = "";
$successMessage = "";

// Procesa el formulario para crear un nuevo tweet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tweet_text'])) {
    $tweetText = trim($_POST['tweet_text']);

    if (!empty($tweetText)) {
        $stmt = $pdo->prepare("INSERT INTO publications (userId, text, createDate) VALUES (:userId, :text, NOW())");
        if ($stmt->execute(['userId' => $userId, 'text' => $tweetText])) {
            $successMessage = "Tweet publicado correctamente.";
        } else {
            $errorMessage = "Hubo un problema al publicar tu tweet. Intenta nuevamente.";
        }
    } else {
        $errorMessage = "El tweet no puede estar vacío.";
    }
}

// Obtiene los tweets de los usuarios que sigue el usuario logueado
$stmt = $pdo->prepare("
    SELECT p.id, p.text, p.createDate, u.username
    FROM publications p
    JOIN follows f ON p.userId = f.userToFollowId
    JOIN users u ON p.userId = u.id
    WHERE f.users_id = :userId
    ORDER BY p.createDate DESC
");
$stmt->execute(['userId' => $userId]);
$followingTweets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtiene los tweets de todos los usuarios
$stmt = $pdo->query("
    SELECT p.id, p.text, p.createDate, u.username
    FROM publications p
    JOIN users u ON p.userId = u.id
    ORDER BY p.createDate DESC
");
$allTweets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Principal - Clon de Twitter</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Bienvenido, <?php echo htmlspecialchars($user['username']); ?>!</h2>
        <p><?php echo htmlspecialchars($user['description']); ?></p>
        <p><a id="logoutButton" href="logout.php">Cerrar sesión</a></p>

        <!-- Formulario para crear un nuevo tweet -->
        <h3>Publicar un nuevo tweet</h3>
        <?php if ($errorMessage): ?>
            <p class="error-message"><?php echo $errorMessage; ?></p>
        <?php elseif ($successMessage): ?>
            <p class="success-message"><?php echo $successMessage; ?></p>
        <?php endif; ?>
        
        <form action="home.php" method="POST">
            <textarea name="tweet_text" rows="3" placeholder="¿Qué estás pensando?" required></textarea>
            <button type="submit">Publicar</button>
        </form>

        <!-- Tweets de las personas que sigue el usuario -->
        <h3>Tweets de las personas que sigues</h3>
        <?php if (count($followingTweets) > 0): ?>
            <ul>
                <?php foreach ($followingTweets as $tweet): ?>
                    <li>
                        <strong><a href="profile.php?id=<?php echo $tweet['username']; ?>"><?php echo htmlspecialchars($tweet['username']); ?></a></strong>
                        <p><?php echo htmlspecialchars($tweet['text']); ?></p>
                        <small><?php echo htmlspecialchars($tweet['createDate']); ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No hay tweets de las personas que sigues.</p>
        <?php endif; ?>

        <!-- Tweets de todos los usuarios -->
        <h3>Tweets de todos los usuarios</h3>
        <?php if (count($allTweets) > 0): ?>
            <ul>
                <?php foreach ($allTweets as $tweet): ?>
                    <li>
                        <strong><a href="profile.php?id=<?php echo $tweet['username']; ?>"><?php echo htmlspecialchars($tweet['username']); ?></a></strong>
                        <p><?php echo htmlspecialchars($tweet['text']); ?></p>
                        <small><?php echo htmlspecialchars($tweet['createDate']); ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No hay tweets disponibles.</p>
        <?php endif; ?>
    </div>
</body>
</html>