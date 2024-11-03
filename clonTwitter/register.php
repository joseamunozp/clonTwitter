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
$successMessage = "";

// Procesa el formulario de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $description = trim($_POST['description']);

    // Validación básica de los campos
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $errorMessage = "Todos los campos son obligatorios.";
    } elseif ($password !== $confirmPassword) {
        $errorMessage = "Las contraseñas no coinciden.";
    } else {
        // Verifica si el nombre de usuario o el correo electrónico ya existen
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
        $stmt->execute(['username' => $username, 'email' => $email]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            $errorMessage = "El nombre de usuario o el correo electrónico ya están en uso.";
        } else {
            // Inserta el nuevo usuario en la base de datos
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, description, createDate) VALUES (:username, :email, :password, :description, NOW())");

            if ($stmt->execute(['username' => $username, 'email' => $email, 'password' => $hashedPassword, 'description' => $description])) {
                // Redirige a la página de inicio de sesión después de registrarse
                $successMessage = "Registro exitoso. Redirigiendo a la página de inicio de sesión...";
                header("refresh:3; url=index.php");
                exit;
            } else {
                $errorMessage = "Ocurrió un error al crear la cuenta. Por favor, inténtalo nuevamente.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Clon de Twitter</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Registro de Usuario</h2>

        <!-- Muestra el mensaje de error o éxito -->
        <?php if ($errorMessage): ?>
            <p class="error-message"><?php echo $errorMessage; ?></p>
        <?php elseif ($successMessage): ?>
            <p class="success-message"><?php echo $successMessage; ?></p>
        <?php endif; ?>
        
        <form action="register.php" method="POST">
            <label for="username">Nombre de Usuario</label>
            <input type="text" name="username" id="username" required>
            
            <label for="email">Correo Electrónico</label>
            <input type="email" name="email" id="email" required>
            
            <label for="password">Contraseña</label>
            <input type="password" name="password" id="password" required>
            
            <label for="confirm_password">Confirmar Contraseña</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
            
            <label for="description">Descripción (opcional)</label>
            <textarea name="description" id="description" rows="3"></textarea>
            
            <button type="submit">Registrarse</button>
        </form>
        
        <p>¿Ya tienes una cuenta? <a href="index.php">Inicia sesión aquí</a></p>
    </div>
</body>
</html>
