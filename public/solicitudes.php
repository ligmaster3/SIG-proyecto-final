<?php
require_once '../config/config.php';
require_once '../controllers/SolicitudesController.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$solicitudesController = new SolicitudesController($conn);

try {
    // Obtener todas las solicitudes del estudiante
    $solicitudes = $solicitudesController->getSolicitudesEstudiante($_SESSION['user_id']);

    // Procesar cancelación de solicitud
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancelar_solicitud'])) {
        $id_solicitud = $_POST['id_solicitud'];
        try {
            $solicitudesController->cancelarSolicitud($id_solicitud, $_SESSION['user_id']);
            $_SESSION['mensaje'] = "Solicitud cancelada correctamente.";
            header("Location: solicitudes.php");
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header("Location: solicitudes.php");
            exit;
        }
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca CRUBA - Mis Solicitudes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <?php include '../src/assets/includes/navbar.php'; ?>

    <div class="container mt-4">
        <h2 class="mb-4">Mis Solicitudes de Libros</h2>

        <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['mensaje'];
                                                unset($_SESSION['mensaje']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error'];
                                            unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <?php if (count($solicitudes) > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Libro</th>
                        <th>Autor</th>
                        <th>Categoría</th>
                        <th>Fecha solicitud</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($solicitudes as $solicitud): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($solicitud['titulo']); ?></td>
                        <td><?php echo htmlspecialchars($solicitud['autor']); ?></td>
                        <td><?php echo htmlspecialchars($solicitud['categoria']); ?></td>
                        <td><?php echo htmlspecialchars($solicitud['fecha_solicitud']); ?></td>
                        <td>
                            <span
                                class="badge <?php echo $solicitudesController->getEstadoBadgeClass($solicitud['estado']); ?>">
                                <?php echo htmlspecialchars($solicitud['estado']); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                data-bs-target="#modalDetalle<?php echo $solicitud['id_solicitud']; ?>">
                                <i class="bi bi-info-circle"></i> Detalles
                            </button>
                            <?php if ($solicitud['estado'] == 'Pendiente'): ?>
                            <form method="post" action="solicitudes.php" class="d-inline">
                                <input type="hidden" name="id_solicitud"
                                    value="<?php echo $solicitud['id_solicitud']; ?>">
                                <button type="submit" name="cancelar_solicitud" class="btn btn-sm btn-danger"
                                    onclick="return confirm('¿Estás seguro de que deseas cancelar esta solicitud?')">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <!-- Modal de detalles -->
                    <div class="modal fade" id="modalDetalle<?php echo $solicitud['id_solicitud']; ?>" tabindex="-1"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Detalles de solicitud</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Libro:</strong> <?php echo htmlspecialchars($solicitud['titulo']); ?></p>
                                    <p><strong>Autor:</strong> <?php echo htmlspecialchars($solicitud['autor']); ?></p>
                                    <p><strong>Categoría:</strong>
                                        <?php echo htmlspecialchars($solicitud['categoria']); ?></p>
                                    <p><strong>Fecha solicitud:</strong>
                                        <?php echo htmlspecialchars($solicitud['fecha_solicitud']); ?></p>
                                    <p><strong>Estado:</strong> <?php echo htmlspecialchars($solicitud['estado']); ?>
                                    </p>

                                    <?php if ($solicitud['motivo']): ?>
                                    <p><strong>Motivo:</strong> <?php echo htmlspecialchars($solicitud['motivo']); ?>
                                    </p>
                                    <?php endif; ?>

                                    <?php if ($solicitud['respuesta']): ?>
                                    <p><strong>Respuesta:</strong>
                                        <?php echo htmlspecialchars($solicitud['respuesta']); ?></p>
                                    <?php endif; ?>

                                    <?php if ($solicitud['fecha_aprobacion']): ?>
                                    <p><strong>Fecha aprobación:</strong>
                                        <?php echo htmlspecialchars($solicitud['fecha_aprobacion']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            No has realizado ninguna solicitud de libros todavía.
            <a href="libros.php" class="alert-link">Buscar libros</a>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>