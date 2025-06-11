<?php
require_once '../config/config.php';
session_start();

// Verificar si es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header("Location: ../../index.php");
    exit;
}

// Función para exportar a CSV
function exportarACSV($datos, $cabeceras, $nombreArchivo)
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $nombreArchivo);

    $salida = fopen('php://output', 'w');
    fputcsv($salida, $cabeceras);

    foreach ($datos as $fila) {
        fputcsv($salida, $fila);
    }

    fclose($salida);
    exit;
}

// Obtener el tipo de exportación
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$descargar = isset($_GET['descargar']) ? true : false;

try {
    switch ($tipo) {
        case 'solicitudes':
            $sql = "SELECT e.nombre as estudiante, e.cedula, l.titulo as libro, 
                           s.fecha_solicitud, s.estado, p.fecha_prestamo, 
                           p.fecha_devolucion_esperada, p.fecha_devolucion_real
                    FROM solicitudes_libros s
                    JOIN estudiantes e ON s.id_estudiante = e.id_estudiante
                    JOIN libros l ON s.id_libro = l.id_libro
                    LEFT JOIN prestamos_libros p ON s.id_solicitud = p.id_solicitud
                    ORDER BY s.fecha_solicitud DESC";

            $stmt = $conn->query($sql);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $cabeceras = [
                'Estudiante',
                'Cédula',
                'Libro',
                'Fecha Solicitud',
                'Estado',
                'Fecha Préstamo',
                'Fecha Devolución Esperada',
                'Fecha Devolución Real'
            ];

            $nombreArchivo = 'solicitudes_' . date('Y-m-d') . '.csv';
            break;

        case 'estudiantes':
            $sql = "SELECT nombre, cedula, correo, carrera, telefono 
                    FROM estudiantes 
                    ORDER BY nombre";

            $stmt = $conn->query($sql);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $cabeceras = ['Nombre', 'Cédula', 'Correo', 'Carrera', 'Teléfono'];
            $nombreArchivo = 'estudiantes_' . date('Y-m-d') . '.csv';
            break;

        case 'prestamos':
            $sql = "SELECT e.nombre as estudiante, l.titulo as libro, 
                           p.fecha_prestamo, p.fecha_devolucion_esperada, 
                           p.fecha_devolucion_real, p.estado
                    FROM prestamos_libros p
                    JOIN solicitudes_libros s ON p.id_solicitud = s.id_solicitud
                    JOIN estudiantes e ON s.id_estudiante = e.id_estudiante
                    JOIN libros l ON s.id_libro = l.id_libro
                    ORDER BY p.fecha_prestamo DESC";

            $stmt = $conn->query($sql);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $cabeceras = [
                'Estudiante',
                'Libro',
                'Fecha Préstamo',
                'Fecha Devolución Esperada',
                'Fecha Devolución Real',
                'Estado'
            ];
            $nombreArchivo = 'prestamos_' . date('Y-m-d') . '.csv';
            break;

        case 'computadoras':
            $sql = "SELECT e.nombre as estudiante, c.numero_computadora, 
                           u.hora_inicio, u.hora_fin, u.estado
                    FROM uso_computadoras u
                    JOIN estudiantes e ON u.id_estudiante = e.id_estudiante
                    JOIN computadoras c ON u.id_computadora = c.id_computadora
                    ORDER BY u.hora_inicio DESC";

            $stmt = $conn->query($sql);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $cabeceras = [
                'Estudiante',
                'Número Computadora',
                'Hora Inicio',
                'Hora Fin',
                'Estado'
            ];
            $nombreArchivo = 'computadoras_' . date('Y-m-d') . '.csv';
            break;

        default:
            throw new Exception("Tipo de exportación no válido");
    }

    if ($descargar) {
        exportarACSV($datos, $cabeceras, $nombreArchivo);
    }

    // Mostrar tabla HTML
    ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Exportar Datos</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background: #f4f4f4;
    }

    .container {
        max-width: 95%;
        margin: 30px auto;
        background: #fff;
        padding: 20px;
        border-radius: 8px;
    }

    table {
        border-collapse: collapse;
        width: 100%;
    }

    th,
    td {
        border: 1px solid #ccc;
        padding: 8px 12px;
        text-align: left;
    }

    th {
        background: #0074D9;
        color: #fff;
    }

    tr:nth-child(even) {
        background: #f9f9f9;
    }

    .btn {
        display: inline-block;
        margin: 15px 0;
        padding: 10px 20px;
        background: #2ECC40;
        color: #fff;
        border: none;
        border-radius: 4px;
        text-decoration: none;
        font-size: 16px;
    }

    .btn:hover {
        background: #27ae60;
    }
    </style>
</head>

<body>
    <div class="container">
        <h2>Datos exportados: <?php echo ucfirst($tipo); ?></h2>
        <a class="btn" href="?tipo=<?php echo htmlspecialchars($tipo); ?>&descargar=1">Descargar CSV</a>
        <table>
            <thead>
                <tr>
                    <?php foreach ($cabeceras as $cabecera): ?>
                    <th><?php echo htmlspecialchars($cabecera); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($datos as $fila): ?>
                <tr>
                    <?php foreach ($fila as $valor): ?>
                    <td><?php echo htmlspecialchars($valor); ?></td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
<?php
    exit;

} catch (Exception $e) {
    $_SESSION['error'] = "Error al exportar: " . $e->getMessage();
    header("Location: admin_prestamos.php");
    exit;
}