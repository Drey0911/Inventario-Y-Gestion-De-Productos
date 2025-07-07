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

// Obtener datos del usuario
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $rol_id = $_POST['id_rol'];

    // Verificar datos duplicados
    $sql_verificar = "SELECT * FROM usuarios WHERE (email = ?) AND id != ?";
    $stmt_verificar = $conn->prepare($sql_verificar);
    $stmt_verificar->bind_param("si", $email, $id);
    $stmt_verificar->execute();
    $resultado_verificacion = $stmt_verificar->get_result();
    
    if ($resultado_verificacion->num_rows > 0) {
        $row = $resultado_verificacion->fetch_assoc();
        if ($row['email'] == $email && $email != $user['email']) {
            $alerta = "Error: El correo ya existe en la base de datos";
        }
    } 

    if ($alerta === ""){
        // Validar campos
        if (empty($nombre) || empty($email) || empty($rol_id)) {
            $alerta = "Todos los campos son obligatorios.";
        } elseif (!empty($password) && !validar_contraseña($password)) {
            $alerta = "La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un carácter especial.";
        } else {
            // Actualizar datos del usuario
            if (!empty($password)) {
                // Encriptar la nueva contraseña
                $password_encriptada = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, email=?, password=?, id_rol=? WHERE id = ?");
                $stmt->bind_param('sssii', $nombre, $email, $password_encriptada, $rol_id, $id);
            } else {
                $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, email=?, id_rol=? WHERE id = ?");
                $stmt->bind_param('ssii', $nombre, $email, $rol_id, $id);
            }

            // Ejecutar la sentencia
            if ($stmt->execute()) {
                header('Location: usuarios_READ.php?success=2');
                exit(); 
            } else {
                $alerta = "Error al actualizar el usuario: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

function validar_contraseña($contraseña) {
    return preg_match('/^(?=.*[A-Z])(?=.*[0-9])(?=.*[\W_]).{8,}$/', $contraseña);
}
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Usuario</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../styles/Styles.css">
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
                    <a href="usuarios_READ.php" class="text-gray-300 hover:text-white flex items-center">
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
            <h1 class="text-3xl font-bold gradient-text">Actualizar Usuario</h1>
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
                        <label for="nombre" class="block text-sm font-medium text-gray-300 mb-2">Nombre</label>
                        <input type="text" id="nombre" name="nombre" required
                               class="input-field w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
                               value="<?php echo isset($user['nombre']) ? htmlspecialchars($user['nombre']) : ''; ?>"
                               placeholder="Nombre completo">
                    </div>
                    <div class="mb-6">
                        <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                        <input type="email" id="email" name="email" required
                               class="input-field w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
                               value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>"
                               placeholder="Correo electrónico">
                    </div>
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-300 mb-2">Contraseña (Opcional)</label>
                        <input type="password" id="password" name="password"
                               class="input-field w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
                               placeholder="Dejar en blanco para no cambiar">
                        <p class="text-xs text-gray-400 mt-1">Debe contener al menos una mayúscula, un número y un carácter especial</p>
                    </div>
                    <div class="mb-6">
                        <label for="id_rol" class="block text-sm font-medium text-gray-300 mb-2">Rol</label>
                        <select id="id_rol" name="id_rol" required
                               class="input-field w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">Seleccione un rol</option>
                            <?php
                            $sql = "SELECT id, nombre FROM roles WHERE estado = 1";
                            $result = $conn->query($sql);
                            while ($row = $result->fetch_assoc()) {
                                $selected = ($row['id'] == $user['id_rol']) ? 'selected' : '';
                                echo "<option value='" . $row['id'] . "' $selected>" . $row['nombre'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        Actualizar Usuario
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