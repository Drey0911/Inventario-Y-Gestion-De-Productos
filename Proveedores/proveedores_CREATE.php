<?php
session_start();
include '../conexion.php';
$alerta = "";

// Acceso de roles
if (!isset($_SESSION['usuario'])) {
    header("Location: ../logout.php");
    exit();
}

$id_rol = $_SESSION['id_rol'];
$roles_permitidos = [1, 2, 5];

if (!in_array($id_rol, $roles_permitidos)) {
    header("Location: ../logout.php?error=1");
    exit();
}

// Tiempo de inactividad de la sesión
$tiempo_inactividad = 2700;

if (isset($_SESSION['ultimo_movimiento'])) {
    $tiempo_transcurrido = time() - $_SESSION['ultimo_movimiento'];
    if ($tiempo_transcurrido > $tiempo_inactividad) {
        header("Location: ../logout.php?error=2");
        exit();
    }
}

$_SESSION['ultimo_movimiento'] = time();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $NIT = $_POST['NIT'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $ciudad = $_POST['ciudad'];

    // Manejo de verificación de datos duplicados únicos
    $sql_verificar = "SELECT * FROM proveedores WHERE (NIT = ? OR correo = ? OR (telefono = ? AND telefono != ''))";
    $stmt_verificar = $conn->prepare($sql_verificar);
    $stmt_verificar->bind_param("sss", $NIT, $correo, $telefono);
    $stmt_verificar->execute();
    $resultado_verificacion = $stmt_verificar->get_result();
    
    if ($resultado_verificacion->num_rows > 0) {
        $row = $resultado_verificacion->fetch_assoc();
        
        if ($row['NIT'] == $NIT) {
            $alerta = "Error: El NIT ya existe en la base de datos";
        }
        if ($row['correo'] == $correo) {
            $alerta = "Error: El correo ya existe en la base de datos";
        }
        if ($row['telefono'] == $telefono && $telefono != "") {
            $alerta = "Error: El teléfono ya existe en la base de datos";
        }
    } else {
        $sql = "INSERT INTO proveedores (nombre, NIT, correo, telefono, ciudad) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $nombre, $NIT, $correo, $telefono, $ciudad);

        if ($stmt->execute()) {
            header('Location: proveedores_READ.php?success=1');
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nuevo Proveedor</title>
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
                    <a href="proveedores_READ.php" class="text-gray-300 hover:text-white flex items-center">
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
            <h1 class="text-3xl font-bold gradient-text">Crear Nuevo Proveedor</h1>
            <p class="text-gray-400">Complete el formulario para registrar un nuevo proveedor</p>
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
                        <label for="nombre" class="block text-sm font-medium text-gray-300 mb-2">Nombre</label>
                        <input type="text" id="nombre" name="nombre" required
                               class="input-field w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
                               placeholder="Ingrese el nombre">
                    </div>
                    <div class="mb-6">
                        <label for="NIT" class="block text-sm font-medium text-gray-300 mb-2">NIT</label>
                        <input type="text" id="NIT" name="NIT" required
                               class="input-field w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
                               placeholder="Ingrese el NIT">
                    </div>
                    <div class="mb-6">
                        <label for="correo" class="block text-sm font-medium text-gray-300 mb-2">Correo</label>
                        <input type="email" id="correo" name="correo" required
                               class="input-field w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
                               placeholder="Ingrese el correo">
                    </div>
                    <div class="mb-6">
                        <label for="telefono" class="block text-sm font-medium text-gray-300 mb-2">Teléfono</label>
                        <input type="text" id="telefono" name="telefono"
                               class="input-field w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
                               placeholder="Ingrese el teléfono (opcional)">
                    </div>
                    <div class="mb-6">
                        <label for="ciudad" class="block text-sm font-medium text-gray-300 mb-2">Ciudad</label>
                        <input type="text" id="ciudad" name="ciudad" required
                               class="input-field w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
                               placeholder="Ingrese la ciudad">
                    </div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        Registrar Proveedor
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