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
        <div class="login-container">
            <h2 class="text-center mb-4">Biblioteca CRUBA</h2>
            <div class="logo-container">
                <img src="src\assets\img\115881452_566833253987120_5272022735552340912_n.jpg" alt="Logo Biblioteca"
                    class="logo">
                <?php
                        // Mostrar mensaje de éxito si se cambió la foto
                        if (isset($_GET['success']) && $_GET['success']) {
                            echo '<div class="alert alert-success py-1">Usuario.</div>';
                        }
                        // Mostrar errores si existen
                        if (isset($_GET['error'])) {
                            echo '<div class="alert alert-danger py-1">'.htmlspecialchars($_GET['error']).'</div>';
                        }
                        ?>
                <form action="public\login.php" method="post">
                    <div class="mb-3">
                        <label for="cedula" class="form-label">Cédula</label>
                        <input type="text" class="form-control" id="cedula" name="cedula" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Iniciar sesión</button>
                </form>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>