<?php
session_start();
include '../conexion.php';
$id = $_GET['id'];
$alerta = "";

// Validación de acceso
if (!isset($_SESSION['usuario'])) {
    header("Location: ../logout.php"); 
    exit();
}

$id_rol = $_SESSION['id_rol'];
$roles_permitidos = [1, 2]; 

if (!in_array($id_rol, $roles_permitidos)) {
    header("Location: ../logout.php?error=1"); 
    exit();
}

// Tiempo de inactividad
$tiempo_inactividad = 2700;

if (isset($_SESSION['ultimo_movimiento'])) {
    $tiempo_transcurrido = time() - $_SESSION['ultimo_movimiento'];
    if ($tiempo_transcurrido > $tiempo_inactividad) {
        header("Location: ../logout.php?error=2");
        exit();
    }
}

$_SESSION['ultimo_movimiento'] = time();

if ($id == 1 && $id_rol != 1) {
    header("Location: ../logout.php?error=1"); 
    exit();
}

$sql = "SELECT * FROM roles WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];

    // Verificación de datos duplicados
    $sql_verificar = "SELECT * FROM roles WHERE (nombre = ?) AND id != ?";
    $stmt_verificar = $conn->prepare($sql_verificar);
    $stmt_verificar->bind_param("si", $nombre, $id);
    $stmt_verificar->execute();
    $resultado_verificacion = $stmt_verificar->get_result();

    if ($resultado_verificacion->num_rows > 0) {
        $row = $resultado_verificacion->fetch_assoc();
        if ($row['nombre'] == $nombre && $nombre != $user['nombre']) {
            $alerta = "Error: El Rol ya existe en la base de datos";
        }
    } 

    if ($alerta === ""){
        $sql = "UPDATE roles SET nombre = ?, descripcion = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $nombre, $descripcion, $id);

        if ($stmt->execute()) {
            header('Location: roles_READ.php?success=2');
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Rol</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/Styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen text-gray-100">
    <!-- Barra de navegación -->
    <nav class="bg-gray-800 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-xl font-bold gradient-text">INVDrey</span>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="roles_READ.php" class="text-gray-300 hover:text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold gradient-text">Actualizar Rol</h1>
            <p class="text-gray-400">Modifique los campos que desea actualizar</p>
        </div>

        <?php if ($alerta): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: '<?php echo $alerta ?>',
                    background: '#1f2937',
                    color: '#fff',
                    confirmButtonColor: '#7f29c2',
                    timer: 3000,
                    timerProgressBar: true,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false
                });
            });
        </script>
        <?php endif; ?>

        <div class="flex justify-center">
            <div class="w-full max-w-md">
                <form method="post" action="" class="bg-gray-800 shadow-lg rounded-lg p-6 border border-gray-700">
                    <div class="mb-6">
                        <label for="nombre" class="block text-sm font-medium text-gray-300 mb-2">Nombre del Rol</label>
                        <input type="text" id="nombre" name="nombre" required
                               class="input-field w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
                               value="<?php echo isset($user['nombre']) ? htmlspecialchars($user['nombre']) : ''; ?>"
                               placeholder="Ej: Administrador">
                    </div>
                    <div class="mb-6">
                        <label for="descripcion" class="block text-sm font-medium text-gray-300 mb-2">Descripción</label>
                        <input type="text" id="descripcion" name="descripcion"
                               class="input-field w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
                               value="<?php echo isset($user['descripcion']) ? htmlspecialchars($user['descripcion']) : ''; ?>"
                               placeholder="Descripción opcional del rol">
                    </div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        Actualizar Rol
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Temporizador de inactividad
        var tiempoInactividad = 2700000; // 45 minutos
        var temporizadorInactividad;

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
                window.location.href = '../logout.php?error=2';
            });
        }

        function reiniciarTemporizador() {
            clearTimeout(temporizadorInactividad);
            temporizadorInactividad = setTimeout(cerrarSesion, tiempoInactividad);
        }

        window.onload = reiniciarTemporizador;
        document.onmousemove = reiniciarTemporizador;
        document.onkeypress = reiniciarTemporizador;
        document.onclick = reiniciarTemporizador;
        document.onscroll = reiniciarTemporizador;
    </script>
</body>
</html>