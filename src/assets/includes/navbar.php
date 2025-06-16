<link rel="stylesheet" href="../src/assets/css/styles.css">

<body>
    <div id="globalSpinner" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(255,255,255,0.7);z-index:99999;align-items:center;justify-content:center;">
        <div class="loading-spinner"></div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-dark" role="navigation" aria-label="Navegación principal">
        <div class="container">
            <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'administrador'): ?>
                <!-- Vista para administradores -->
                <div class="d-flex align-items-center">
                    <img src="../src/assets/img/logoUnachi.jpg" alt="Logo Biblioteca CRUBA" class="logoUniv">
                    <span class="navbar-brand d-flex align-items-center">
                        <i class="bi bi-book-half me-2" aria-hidden="true"></i>
                        <span>Biblioteca CRUBA</span>
                    </span>
                </div>

                <div class="d-flex align-items-center">
                    <ul class="navbar-nav flex-row gap-3">
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center" href="./admin.php" aria-label="Panel de administración">
                                <i class="bi bi-speedometer2 me-2" aria-hidden="true"></i>
                                <span>Admin</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center" href="./admin_prestamos.php" aria-label="Panel de préstamos">
                                <i class="bi bi-journal-text me-2" aria-hidden="true"></i>
                                <span>Panel</span>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menú de usuario">
                                <i class="bi bi-person-circle me-2" aria-hidden="true"></i>
                                <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="logout.php" aria-label="Cerrar sesión"><i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i> Cerrar sesión</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>

            <?php else: ?>
                <!-- Vista para estudiantes/no logueados -->
                <div class="d-flex align-items-center">
                    <img src="../src/assets/img/logoUnachi.jpg" alt="Logo Biblioteca CRUBA" class="logoUniv">
                    <a class="navbar-brand d-flex align-items-center" href="dashboard.php" aria-label="Inicio">
                        <i class="bi bi-book-half me-2" aria-hidden="true"></i>
                        <span>Biblioteca CRUBA</span>
                    </a>
                </div>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center" href="dashboard.php" title="Ir al inicio">
                                <i class="bi bi-house-door me-2"></i>
                                <span>Inicio</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center" href="libros.php" title="Ver catálogo de libros">
                                <i class="bi bi-book me-2"></i>
                                <span>Libros</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center" href="computadoras.php" title="Acceder a computadoras">
                                <i class="bi bi-pc me-2"></i>
                                <span>Computadoras</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center" href="solicitudes.php" title="Ver mis solicitudes">
                                <i class="bi bi-journal-text me-2"></i>
                                <span>Mis solicitudes</span>
                            </a>
                        </li>
                    </ul>

                    <?php if (isset($_SESSION['user_name'])): ?>
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item dropdown">

                                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button"
                                    data-bs-toggle="dropdown" aria-expanded="false" id="perfilDropdown" aria-label="Menú de perfil">
                                    <?php
                                    $foto_estudiante = '';
                                    try {
                                        if (isset($_SESSION['user_id'])) {
                                            require_once '../config/config.php';
                                            $stmt = $conn->prepare("SELECT foto FROM estudiantes WHERE id_estudiante = ?");
                                            $stmt->execute([$_SESSION['user_id']]);
                                            $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);
                                            if ($estudiante && !empty($estudiante['foto'])) {
                                                $foto_estudiante = $estudiante['foto'];
                                            }
                                        }
                                    } catch (PDOException $e) {
                                        error_log("Error al obtener foto de perfil: " . $e->getMessage());
                                        $foto_estudiante = '../src/assets/img/user-default.png';
                                    }
                                    ?>
                                    <?php if (!empty($foto_estudiante)): ?>
                                        <img src="<?= htmlspecialchars($foto_estudiante ?: '../src/assets/img/user-default.png'); ?>"
                                            class="rounded-circle me-2"
                                            width="30" height="30" alt="Foto de perfil de <?= htmlspecialchars($_SESSION['user_name']) ?>"
                                            style="object-fit: cover;"
                                            onerror="this.src='../src/assets/img/user-default.png';">

                                    <?php else: ?>
                                        <i class="bi bi-person-circle me-2" aria-hidden="true"></i>
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="perfilDropdown">
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center py-2" href="#" aria-label="Ver mi perfil">
                                            <i class="bi bi-person me-2" aria-hidden="true"></i>
                                            <span>Mi perfil</span>
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center py-2 text-danger" href="logout.php" aria-label="Cerrar sesión">
                                            <i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>
                                            <span>Cerrar sesión</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar spinner al hacer clic en enlaces
        const links = document.querySelectorAll('a:not([data-bs-toggle])');
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                // Solo mostrar para enlaces internos
                if (link.href && link.href.indexOf(window.location.host) !== -1) {
                    document.getElementById('globalSpinner').style.display = 'flex';
                }
            });
        });

        // Mostrar spinner al hacer clic en botones de acción
        const actionButtons = document.querySelectorAll('button[data-bs-toggle="modal"]');
        actionButtons.forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('globalSpinner').style.display = 'flex';
                setTimeout(() => {
                    document.getElementById('globalSpinner').style.display = 'none';
                }, 500);
            });
        });

        // Ocultar spinner al cargar la página
        window.addEventListener('DOMContentLoaded', () => {
            document.getElementById('globalSpinner').style.display = 'none';
        });
    </script>
</body>