<?php
require_once '../config/config.php';

class DashboardController
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getEstudianteInfo($id_estudiante)
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT e.*, f.nombre as nombre_facultad, es.nombre as nombre_escuela 
                FROM estudiantes e
                LEFT JOIN facultades f ON e.id_facultad = f.id_facultad
                LEFT JOIN escuelas es ON e.id_escuela = es.id_escuela
                WHERE e.id_estudiante = :id
            ");
            $stmt->bindParam(':id', $id_estudiante);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener información del estudiante: " . $e->getMessage());
        }
    }

    public function getFacultadInfo($id_facultad)
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM facultades WHERE id_facultad = :id");
            $stmt->bindParam(':id', $id_facultad);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener información de la facultad: " . $e->getMessage());
        }
    }

    public function getEscuelaInfo($id_escuela)
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM escuelas WHERE id_escuela = :id");
            $stmt->bindParam(':id', $id_escuela);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener información de la escuela: " . $e->getMessage());
        }
    }

    public function registrarSalidaBiblioteca($id_estudiante)
    {
        try {
            $fecha_actual = date('Y-m-d');
            $hora_actual = date('H:i:s');

            // Verificar si hay una entrada sin salida
            $stmt = $this->conn->prepare("SELECT id_asistencia FROM asistencia_biblioteca 
                                        WHERE id_estudiante = :id_estudiante 
                                        AND fecha = :fecha 
                                        AND hora_salida IS NULL");
            $stmt->bindParam(':id_estudiante', $id_estudiante);
            $stmt->bindParam(':fecha', $fecha_actual);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                throw new Exception("No hay registro de entrada activo para hoy.");
            }

            // Registrar salida de biblioteca
            $stmt = $this->conn->prepare("UPDATE asistencia_biblioteca 
                                        SET hora_salida = :hora_salida 
                                        WHERE id_estudiante = :id_estudiante 
                                        AND fecha = :fecha 
                                        AND hora_salida IS NULL");
            $stmt->bindParam(':hora_salida', $hora_actual);
            $stmt->bindParam(':id_estudiante', $id_estudiante);
            $stmt->bindParam(':fecha', $fecha_actual);
            $stmt->execute();

            // Registrar salida de computadoras si está usando una
            $stmt = $this->conn->prepare("UPDATE uso_computadoras 
                                        SET hora_fin = :hora_fin 
                                        WHERE id_estudiante = :id_estudiante 
                                        AND fecha = :fecha 
                                        AND hora_fin IS NULL");
            $stmt->bindParam(':hora_fin', $hora_actual);
            $stmt->bindParam(':id_estudiante', $id_estudiante);
            $stmt->bindParam(':fecha', $fecha_actual);
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            throw new Exception("Error al registrar salida: " . $e->getMessage());
        }
    }

    public function getAsistenciaHoy($id_estudiante)
    {
        try {
            $fecha_actual = date('Y-m-d');
            $stmt = $this->conn->prepare("SELECT * FROM asistencia_biblioteca 
                                        WHERE id_estudiante = :id_estudiante 
                                        AND fecha = :fecha");
            $stmt->bindParam(':id_estudiante', $id_estudiante);
            $stmt->bindParam(':fecha', $fecha_actual);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener asistencia: " . $e->getMessage());
        }
    }

    public function getUsoComputadorasHoy($id_estudiante)
    {
        try {
            $fecha_actual = date('Y-m-d');
            $stmt = $this->conn->prepare("SELECT * FROM uso_computadoras 
                                        WHERE id_estudiante = :id_estudiante 
                                        AND fecha = :fecha");
            $stmt->bindParam(':id_estudiante', $id_estudiante);
            $stmt->bindParam(':fecha', $fecha_actual);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener uso de computadoras: " . $e->getMessage());
        }
    }

    public function getEstadisticasFacultad($id_facultad)
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    COUNT(DISTINCT e.id_estudiante) as total_estudiantes,
                    COUNT(DISTINCT es.id_escuela) as total_escuelas,
                    COUNT(DISTINCT a.id_asistencia) as total_visitas
                FROM facultades f
                LEFT JOIN escuelas es ON f.id_facultad = es.id_facultad
                LEFT JOIN estudiantes e ON es.id_escuela = e.id_escuela
                LEFT JOIN asistencia_biblioteca a ON e.id_estudiante = a.id_estudiante
                WHERE f.id_facultad = :id_facultad
            ");
            $stmt->bindParam(':id_facultad', $id_facultad);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener estadísticas de la facultad: " . $e->getMessage());
        }
    }

    public function getEstadisticasEscuela($id_escuela)
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    COUNT(DISTINCT e.id_estudiante) as total_estudiantes,
                    COUNT(DISTINCT a.id_asistencia) as total_visitas,
                    COUNT(DISTINCT u.id_uso) as total_uso_computadoras
                FROM escuelas es
                LEFT JOIN estudiantes e ON es.id_escuela = e.id_escuela
                LEFT JOIN asistencia_biblioteca a ON e.id_estudiante = a.id_estudiante
                LEFT JOIN uso_computadoras u ON e.id_estudiante = u.id_estudiante
                WHERE es.id_escuela = :id_escuela
            ");
            $stmt->bindParam(':id_escuela', $id_escuela);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener estadísticas de la escuela: " . $e->getMessage());
        }
    }
}