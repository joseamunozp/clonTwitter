<?php
session_start();
require 'db.php';

// Verifica si el usuario ya está logueado
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}

// Inicializa los mensajes de error y éxito
$errorMessage = "";

// Procesa el formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Consulta para verificar las credenciales
    $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifica si el usuario existe y la contraseña es correcta
    if ($user && password_verify($password, $user['password'])) {
        // Guarda el ID del usuario en la sesión y redirige a la página principal
        $_SESSION['user_id'] = $user['id'];
        header("Location: home.php");
        exit;
    } else {
        // Mensaje de error si las credenciales no son válidas
        $errorMessage = "Nombre de usuario o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Clon de Twitter</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Iniciar Sesión</h2>
        <!-- Muestra el mensaje de error si existe -->
        <?php if ($errorMessage): ?>
            <p class="error-message"><?php echo $errorMessage; ?></p>
        <?php endif; ?>
        
        <form action="index.php" method="POST">
            <label for="username">Nombre de Usuario</label>
            <input type="text" name="username" id="username" required>
            
            <label for="password">Contraseña</label>
            <input type="password" name="password" id="password" required>
            
            <button type="submit">Iniciar Sesión</button>
        </form>
        
        <p>¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a></p>
    </div>
</body>
</html>
