<?php
require_once "../config/config.php";

class SolicitudesController
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getSolicitudesEstudiante($id_estudiante)
    {
        try {
            $stmt = $this->conn->prepare("SELECT s.*, l.titulo, l.autor, c.nombre as categoria 
                                        FROM solicitudes_libros s
                                        JOIN libros l ON s.id_libro = l.id_libro
                                        JOIN categorias_libros c ON l.id_categoria = c.id_categoria
                                        WHERE s.id_estudiante = :id_estudiante
                                        ORDER BY s.fecha_solicitud DESC");
            $stmt->bindParam(':id_estudiante', $id_estudiante);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener solicitudes: " . $e->getMessage());
        }
    }

    public function getDetalleSolicitud($id_solicitud, $id_estudiante)
    {
        try {
            $stmt = $this->conn->prepare("SELECT s.*, l.titulo, l.autor, c.nombre as categoria 
                                        FROM solicitudes_libros s
                                        JOIN libros l ON s.id_libro = l.id_libro
                                        JOIN categorias_libros c ON l.id_categoria = c.id_categoria
                                        WHERE s.id_solicitud = :id_solicitud 
                                        AND s.id_estudiante = :id_estudiante");
            $stmt->bindParam(':id_solicitud', $id_solicitud);
            $stmt->bindParam(':id_estudiante', $id_estudiante);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener detalle de solicitud: " . $e->getMessage());
        }
    }

    public function cancelarSolicitud($id_solicitud, $id_estudiante)
    {
        try {
            // Verificar que la solicitud pertenece al estudiante y está pendiente
            $stmt = $this->conn->prepare("SELECT * FROM solicitudes_libros 
                                        WHERE id_solicitud = :id_solicitud 
                                        AND id_estudiante = :id_estudiante 
                                        AND estado = 'Pendiente'");
            $stmt->bindParam(':id_solicitud', $id_solicitud);
            $stmt->bindParam(':id_estudiante', $id_estudiante);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                throw new Exception("No se puede cancelar esta solicitud.");
            }

            // Actualizar estado de la solicitud
            $update = $this->conn->prepare("UPDATE solicitudes_libros 
                                        SET estado = 'Cancelada' 
                                        WHERE id_solicitud = :id_solicitud");
            $update->bindParam(':id_solicitud', $id_solicitud);
            $update->execute();

            // Devolver el libro al inventario
            $stmt = $this->conn->prepare("SELECT id_libro FROM solicitudes_libros WHERE id_solicitud = :id_solicitud");
            $stmt->bindParam(':id_solicitud', $id_solicitud);
            $stmt->execute();
            $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);

            $update = $this->conn->prepare("UPDATE libros 
                                        SET cantidad_disponible = cantidad_disponible + 1 
                                        WHERE id_libro = :id_libro");
            $update->bindParam(':id_libro', $solicitud['id_libro']);
            $update->execute();

            return true;
        } catch (PDOException $e) {
            throw new Exception("Error al cancelar solicitud: " . $e->getMessage());
        }
    }

    public function getEstadoBadgeClass($estado)
    {
        switch ($estado) {
            case 'Aprobada':
                return 'bg-success';
            case 'Rechazada':
                return 'bg-danger';
            case 'Entregado':
                return 'bg-primary';
            case 'Devuelto':
                return 'bg-secondary';
            case 'Cancelada':
                return 'bg-dark';
            default:
                return 'bg-warning text-dark';
        }
    }
}