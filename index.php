<?php
require_once 'config\config.php';
session_start();

// Verificar si el usuario ya está logueado
if(isset($_SESSION['user_id'])) {
    header("Location: public\dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca CRUBA - Inicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="src/assets/css/styles.css">
    <style>
    body {
        background-color: #f8f9fa;
    }

    .login-container {
        max-width: 400px;
        margin: 100px auto;
        padding: 20px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    </style>
</head>

<body>
    <div class="container">
        <img src="src/assets/img/IMG_20250611_150709.jpg" alt="Logo Biblioteca" class="logoUniv">
        <div class="login-container">
            <h2 class="text-center mb-4">
                <i class="bi bi-book"></i> Biblioteca CRUBA
            </h2>
            <?php
                // Mostrar mensaje de éxito si se cambió la foto
                if (isset($_GET['success']) && $_GET['success']) {
                    echo '<div class="alert alert-success py-1"><i class="bi bi-check-circle"></i> Usuario.</div>';
                }
                // Mostrar errores si existen
                if (isset($_GET['error'])) {
                    echo '<div class="alert alert-danger py-1"><i class="bi bi-exclamation-triangle"></i> '.htmlspecialchars($_GET['error']).'</div>';
                }
                ?>
            <form action="public\login.php" method="post" class="form-group">
                <div class="mb-3">
                    <label for="cedula" class="form-label"><i class="bi bi-person-badge"></i> Cédula</label>
                    <input type="text" class="form-control" id="cedula" name="cedula" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label"><i class="bi bi-lock"></i> Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-login w-100">
                    <i class="bi bi-box-arrow-in-right"></i> Iniciar sesión
                </button>
            </form>
        </div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>