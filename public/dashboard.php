<?php
require_once '../config/config.php';
require_once '../controllers/DashboardController.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$dashboardController = new DashboardController($conn);

// --- Mueve aquí el procesamiento del formulario ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_avatar'])) {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['avatar']['tmp_name'];
        $fileName = basename($_FILES['avatar']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileExt, $allowed)) {
            $newName = 'avatar' . $_SESSION['user_id'] . '_' . time() . '.' . $fileExt;
            $dest = '../src/assets/uploads/' . $newName;
            if (!is_dir('../src/assets/uploads/')) {
                mkdir('../src/assets/uploads/', 0755, true);
            }
            if (move_uploaded_file($fileTmp, $dest)) {
                // Actualizar en la base de datos
                $avatarPath = '../src/assets/uploads/' . $newName;
                $stmt = $conn->prepare("UPDATE estudiantes SET foto = :foto WHERE id_estudiante = :id");
                $stmt->bindParam(':foto', $avatarPath);
                $stmt->bindParam(':id', $_SESSION['user_id']);
                $stmt->execute();
                header("Location: dashboard.php?success=avatar");
                exit;
            } else {
                header("Location: dashboard.php?error=Error+al+subir+el+archivo");
                exit;
            }
        } else {
            header("Location: dashboard.php?error=Formato+de+imagen+no+permitido");
            exit;
        }
    } else {
        header("Location: dashboard.php?error=No+se+seleccionó+ningún+archivo");
        exit;
    }
}
// --- Fin del bloque movido ---

try {
    $estudiante = $dashboardController->getEstudianteInfo($_SESSION['user_id']);

    // Registrar salida si se solicita
    if (isset($_GET['action']) && $_GET['action'] == 'salida') {
        $dashboardController->registrarSalidaBiblioteca($_SESSION['user_id']);
        header("Location: dashboard.php?success=salida");
        exit;
    }

    $asistencia = $dashboardController->getAsistenciaHoy($_SESSION['user_id']);
    $computadoras = $dashboardController->getUsoComputadorasHoy($_SESSION['user_id']);
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca CRUBA - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../src/assets/css/styles.css">

</head>

<body>
    <div id="globalSpinner"
        style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(255,255,255,0.7);z-index:99999;align-items:center;justify-content:center;">
        <div class="loading-spinner"></div>
    </div>

    <?php if (!isset($_SESSION['welcome_shown'])): ?>
    <div class="welcome-overlay">
        <div class="welcome-card">
            <div class="welcome-header">
                <h2><i class="bi bi-book-half me-2"></i> Biblioteca CRUBA</h2>
            </div>
            <div class="welcome-body">
                <div class="welcome-icon">
                    <i class="bi bi-emoji-smile"></i>
                </div>
                <div class="welcome-name">¡Hola, <?php echo htmlspecialchars($estudiante['nombre']); ?>!</div>
                <div class="welcome-text">
                    Bienvenido al sistema de gestión de la biblioteca.<br>
                    Ahora puedes acceder a todos nuestros recursos académicos, reservar computadoras y gestionar tus
                    préstamos.
                </div>
                <button class="welcome-button" id="closeWelcome">
                    Comenzar <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </div>
    </div>
    <?php $_SESSION['welcome_shown'] = true; ?>
    <script>
    // Cerrar al hacer clic en el botón
    document.getElementById('closeWelcome').addEventListener('click', function() {
        const overlay = document.querySelector('.welcome-overlay');
        overlay.style.animation = 'fadeIn 0.5s reverse forwards';
        setTimeout(() => {
            overlay.remove();
        }, 500);
    });

    // Cerrar automáticamente después de 5 segundos
    setTimeout(() => {
        const overlay = document.querySelector('.welcome-overlay');
        if (overlay) {
            overlay.style.animation = 'fadeIn 0.5s reverse forwards';
            setTimeout(() => overlay.remove(), 500);
        }
    }, 5000);
    </script>

    <?php endif; ?>

    <nav class="navbar navbar-expand-lg navbar-dark ">
        <div class="container">
            <img src="../src/assets/img/logoUnachi.jpg" alt="Logo Biblioteca" class="logoUniv">
            <a class="navbar-brand" href="#">Biblioteca CRUBA</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="bi bi-house-door"></i> Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="libros.php"><i class="bi bi-book"></i> Libros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="computadoras.php"><i class="bi bi-pc"></i> Computadoras</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="solicitudes.php"><i class="bi bi-journal-text"></i> Mis
                            solicitudes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Información del Estudiante</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="profile-image-container">
                                <img src="<?php echo htmlspecialchars($estudiante['foto']); ?>" class="profile-img mb-3"
                                    alt="Foto perfil">
                                <div
                                    class="profile-status <?php echo $asistencia ? 'status-active' : 'status-inactive'; ?>">
                                    <i class="bi <?php echo $asistencia ? 'bi-circle-fill' : 'bi-circle'; ?>"></i>
                                    <?php echo $asistencia ? 'En Biblioteca' : 'Fuera de Biblioteca'; ?>
                                </div>
                            </div>
                            <h4 class="profile-name"><?php echo htmlspecialchars($estudiante['nombre']); ?></h4>
                            <p class="profile-role">Estudiante</p>
                        </div>

                        <div class="profile-info">
                            <div class="info-item">
                                <i class="bi bi-person-badge"></i>
                                <div class="info-content">
                                    <span class="info-label">Cédula</span>
                                    <span
                                        class="info-value"><?php echo htmlspecialchars($estudiante['cedula']); ?></span>
                                </div>
                            </div>

                            <div class="info-item">
                                <i class="bi bi-building"></i>
                                <div class="info-content">
                                    <span class="info-label">Facultad</span>
                                    <span
                                        class="info-value"><?php echo htmlspecialchars($estudiante['facultad']); ?></span>
                                </div>
                            </div>

                            <div class="info-item">
                                <i class="bi bi-mortarboard"></i>
                                <div class="info-content">
                                    <span class="info-label">Escuela</span>
                                    <span
                                        class="info-value"><?php echo htmlspecialchars($estudiante['escuela']); ?></span>
                                </div>
                            </div>

                            <?php if (isset($estudiante['email'])): ?>
                            <div class="info-item">
                                <i class="bi bi-envelope"></i>
                                <div class="info-content">
                                    <span class="info-label">Email</span>
                                    <span
                                        class="info-value"><?php echo htmlspecialchars($estudiante['email']); ?></span>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (isset($estudiante['telefono'])): ?>
                            <div class="info-item">
                                <i class="bi bi-telephone"></i>
                                <div class="info-content">
                                    <span class="info-label">Teléfono</span>
                                    <span
                                        class="info-value"><?php echo htmlspecialchars($estudiante['telefono']); ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php
                        // Mostrar mensaje de éxito si se cambió la foto
                        if (isset($_GET['success']) && $_GET['success'] == 'avatar') {
                            echo '<div class="alert alert-success py-1">Avatar actualizado correctamente.</div>';
                        }
                        // Mostrar errores si existen
                        if (isset($_GET['error'])) {
                            echo '<div class="alert alert-danger py-1">' . htmlspecialchars($_GET['error']) . '</div>';
                        }
                        ?>
                        <form action="dashboard.php" method="post" enctype="multipart/form-data" class="mt-4">
                            <div class="upload-avatar-container">
                                <input type="file" name="avatar" accept="image/*" class="form-control mb-2" required>
                                <button type="submit" name="cambiar_avatar" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-person-circle"></i> Cambiar avatar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="row">
                    <div class="w-100">
                        <div class="card mb-2">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">Mi actividad hoy</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="activity-info">
                                        <?php if ($asistencia): ?>
                                        <p><strong>Hora de entrada:</strong>
                                            <?php echo htmlspecialchars($asistencia['hora_entrada']); ?></p>
                                        <?php if ($asistencia['hora_salida']): ?>
                                        <p><strong>Hora de salida:</strong>
                                            <?php echo htmlspecialchars($asistencia['hora_salida']); ?>
                                        </p>
                                        <?php else: ?>
                                        <p class='text-success'><strong>Actualmente en la biblioteca</strong></p>
                                        <?php endif; ?>
                                        <?php else: ?>
                                        <p>No has registrado entrada hoy.</p>
                                        <?php endif; ?>

                                        <?php if (count($computadoras) > 0): ?>
                                        <h6 class='mt-4'>Uso de computadoras:</h6>
                                        <?php foreach ($computadoras as $comp): ?>
                                        <p>
                                            <strong>Computadora
                                                #<?php echo htmlspecialchars($comp['computadora_id']); ?></strong><br>
                                            Inicio: <?php echo htmlspecialchars($comp['hora_inicio']); ?><br>
                                            <?php if ($comp['hora_fin']): ?>
                                            Fin: <?php echo htmlspecialchars($comp['hora_fin']); ?>
                                            <?php else: ?>
                                            <span class='text-success'>En uso actualmente</span>
                                            <?php endif; ?>
                                        </p>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ms-3">
                                        <a href="dashboard.php?action=salida" class="quick-action-btn danger">
                                            <i class="bi bi-box-arrow-left"></i>
                                            <span>Registrar salida</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="w-100">
                        <div class="card mb-2">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">Acciones rápidas</h5>
                            </div>
                            <div class="content-card">
                                <div class="quick-actions">
                                    <a href="libros.php" class="quick-action-btn hover-line" data-hover="Buscar libros">
                                        <i class="bi bi-book"></i>
                                        <span>Buscar libros</span>
                                    </a>
                                    <a href="solicitudes.php" class="quick-action-btn hover-line"
                                        data-hover="Mis solicitudes">
                                        <i class="bi bi-journal-text"></i>
                                        <span>Mis solicitudes</span>
                                    </a>
                                    <a href="computadoras.php" class="quick-action-btn hover-line"
                                        data-hover="Usar computadora">
                                        <i class="bi bi-pc"></i>
                                        <span>Usar computadora</span>
                                    </a>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Mis solicitudes recientes</h5>
                    </div>
                    <div class="card-body" style="max-height: 384px; overflow-y: auto;">
                        <?php
                        // Obtener las últimas 3 solicitudes del estudiante
                        try {
                            $stmt = $conn->prepare("SELECT s.*, l.titulo 
                                                  FROM solicitudes_libros s
                                                  JOIN libros l ON s.id_libro = l.id_libro
                                                  WHERE s.id_estudiante = :id_estudiante
                                                  ORDER BY s.fecha_solicitud DESC
                                                  LIMIT 3");
                            $stmt->bindParam(':id_estudiante', $_SESSION['user_id']);
                            $stmt->execute();
                            $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            if (count($solicitudes) > 0) {
                                foreach ($solicitudes as $solicitud) {
                                    echo "<div class='mb-3 p-2 border rounded'>";
                                    echo "<h6>" . htmlspecialchars($solicitud['titulo']) . "</h6>";
                                    echo "<p><strong>Estado:</strong> " . htmlspecialchars($solicitud['estado']) . "<br>";
                                    echo "<strong>Fecha solicitud:</strong> " . htmlspecialchars($solicitud['fecha_solicitud']) . "</p>";
                                    echo "</div>";
                                }
                                echo "<a href='solicitudes.php' class='btn btn-primary mt-2'>Ver todas</a>";
                            } else {
                                echo "<p>No tienes solicitudes recientes.</p>";
                                echo "<a href='libros.php' class='btn btn-primary'>Buscar libros</a>";
                            }
                        } catch (PDOException $e) {
                            die("Error: " . $e->getMessage());
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Mostrar spinner al navegar entre páginas
    const links = document.querySelectorAll('a, button[type="submit"]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            // Solo mostrar para enlaces internos
            if (link.href && link.href.indexOf(window.location.host) !== -1) {
                document.getElementById('globalSpinner').style.display = 'flex';
                setTimeout(() => {
                    document.getElementById('globalSpinner').style.display = 'none';
                }, 2000); // Oculta el spinner después de 2 segundos
            }
        });
    });
    // Ocultar spinner al cargar la página
    window.addEventListener('DOMContentLoaded', () => {
        document.getElementById('globalSpinner').style.display = 'none';
    });
    </script>
</body>

</html>