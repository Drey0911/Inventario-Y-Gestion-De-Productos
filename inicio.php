<?php
session_start();
include('conexion.php');

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php"); 
    exit();
}

// Tiempo de inactividad
$tiempo_inactividad = 2700; 

if (isset($_SESSION['ultimo_movimiento'])) {
    $tiempo_transcurrido = time() - $_SESSION['ultimo_movimiento'];

    if ($tiempo_transcurrido > $tiempo_inactividad) {
        header("Location: logout.php?error=2");
        exit();
    }
}

$_SESSION['ultimo_movimiento'] = time();

$usuario_nombre = $_SESSION['usuario'];
$id_rol = $_SESSION['id_rol'];

// Cerrar sesión
if (isset($_POST['cerrar_sesion'])) {
    header("Location: logout.php?success=1");
}

// Mostrar el rol del usuario
$sql_rol = "SELECT nombre FROM roles WHERE id = ?";
$stmt = $conn->prepare($sql_rol);
$stmt->bind_param("i", $id_rol);
$stmt->execute();
$result_rol = $stmt->get_result();
$rol = $result_rol->fetch_assoc();
$rol_nombre = $rol['nombre'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Principal - Gestión de Inventario</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/Styles.css">
    <link rel="stylesheet" href="styles/Card.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="text-gray-100">
    <!-- Barra de navegación superior -->
    <nav class="bg-gray-800 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-xl font-bold gradient-text">INVDrey</span>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-300"><?php echo htmlspecialchars($usuario_nombre); ?></span>
                    <span class="px-3 py-1 bg-purple-600 text-white text-xs font-medium rounded-full"><?php echo htmlspecialchars($rol_nombre); ?></span>
                    <form method="post">
                        <button type="submit" name="cerrar_sesion" class="flex items-center text-gray-300 hover:text-purple-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 gradient-text">Gestión de Inventario</h1>
            <p class="text-xl text-gray-300">Seleccione el módulo que desea gestionar</p>
        </div>

        <!-- Tarjetas de menú -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if ($id_rol == 1 || $id_rol == 2 || $id_rol == 3 || $id_rol == 4 || $id_rol == 5 || $id_rol == 6): ?>
            <a href="Productos/productos_READ.php" class="card-hover bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-700 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-purple-500 bg-opacity-20 rounded-full flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Productos</h3>
                <p class="text-gray-400">Gestión completa de inventario de productos</p>
            </a>
            <?php endif; ?>

            <?php if ($id_rol == 1 || $id_rol == 2 || $id_rol == 4 || $id_rol == 5 || $id_rol == 6): ?>
            <a href="Proveedores/proveedores_READ.php" class="card-hover bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-700 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-purple-500 bg-opacity-20 rounded-full flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Proveedores</h3>
                <p class="text-gray-400">Administración de proveedores y compras</p>
            </a>
            <?php endif; ?>

            <?php if ($id_rol == 1 || $id_rol == 2 || $id_rol == 3 || $id_rol == 5 || $id_rol == 6): ?>
            <a href="Clientes/clientes_READ.php" class="card-hover bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-700 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-purple-500 bg-opacity-20 rounded-full flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Clientes</h3>
                <p class="text-gray-400">Gestión de clientes y relaciones</p>
            </a>
            <?php endif; ?>

            <?php if ($id_rol == 1 || $id_rol == 2 || $id_rol == 3 || $id_rol == 5 || $id_rol == 6): ?>
            <a href="Ventas/ventas_READ.php" class="card-hover bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-700 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-purple-500 bg-opacity-20 rounded-full flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Ventas</h3>
                <p class="text-gray-400">Registro y seguimiento de ventas</p>
            </a>
            <?php endif; ?>

            <?php if ($id_rol == 1 || $id_rol == 2 || $id_rol == 4 || $id_rol == 5 || $id_rol == 6): ?>
            <a href="Compras/compras_READ.php" class="card-hover bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-700 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-purple-500 bg-opacity-20 rounded-full flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Compras</h3>
                <p class="text-gray-400">Control de compras y pedidos</p>
            </a>
            <?php endif; ?>

            <?php if ($id_rol == 1 || $id_rol == 2): ?>
            <a href="Usuarios/usuarios_READ.php" class="card-hover bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-700 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-purple-500 bg-opacity-20 rounded-full flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Usuarios</h3>
                <p class="text-gray-400">Administración de usuarios del sistema</p>
            </a>
            <?php endif; ?>

            <?php if ($id_rol == 1 || $id_rol == 2): ?>
            <a href="Roles/roles_READ.php" class="card-hover bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-700 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-purple-500 bg-opacity-20 rounded-full flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Roles</h3>
                <p class="text-gray-400">Gestión de roles y permisos</p>
            </a>
            <?php endif; ?>
        </div>
    </main>

    <!-- Pie de página -->
    <footer class="bg-gray-800 py-6 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-gray-400">&copy; <?php echo date('Y'); ?> By Andrey Stteven Mantilla Leon. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script>
        // Definir el tiempo máximo de inactividad en milisegundos
        var tiempoInactividad = 2700000; // 45 minutos

        // Variable para almacenar el temporizador
        var temporizadorInactividad;

        // Función que redirige a logout.php cuando el tiempo de inactividad ha pasado
        function cerrarSesion() {
            Swal.fire({
                title: 'Sesión expirada',
                text: 'Tu sesión ha expirado por inactividad',
                icon: 'warning',
                confirmButtonColor: '#7f29c2',
                background: '#1f2937',
                color: '#fff',
                confirmButtonText: 'Entendido'
            }).then((result) => {
                window.location.href = 'logout.php?error=2';
            });
        }

        // Función para reiniciar el temporizador
        function reiniciarTemporizador() {
            // Limpiar el temporizador anterior
            clearTimeout(temporizadorInactividad);
            // Iniciar un nuevo temporizador
            temporizadorInactividad = setTimeout(cerrarSesion, tiempoInactividad);
        }

        // Detectar eventos de actividad del usuario
        window.onload = reiniciarTemporizador;
        document.onmousemove = reiniciarTemporizador;
        document.onkeypress = reiniciarTemporizador;
        document.onclick = reiniciarTemporizador;
        document.onscroll = reiniciarTemporizador;
    </script>
</body>
</html>