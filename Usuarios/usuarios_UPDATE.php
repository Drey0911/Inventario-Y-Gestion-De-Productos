<?php
session_start();
include '../conexion.php';

$id = $_GET['id'];

// Acceso permitido de ciertos roles
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

// Tiempo de inactividad de la sesion
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

$alerta = "";

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
    
            // Solo mostrar error si el valor nuevo ya existe en otro registro y no es igual al valor actual
            if ($row['email'] == $email && $email != $user['email']) {
                $alerta .= "Error: El correo ya existe en la base de datos.<br>";
            }
        } 

    if ($alerta === ""){
    // Validar campos
    if (empty($nombre) || empty($email) || empty($rol_id)) {
        $alerta = "Todos los campos son obligatorios.";
    } elseif (empty($rol_id)) {
        $alerta = "Debes seleccionar un rol.";
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

        // Cerrar la sentencia
        $stmt->close();
    }
}
}

// Función para validar la contraseña
function validar_contraseña($contraseña) {
    return preg_match('/^(?=.*[A-Z])(?=.*[0-9])(?=.*[\W_]).{8,}$/', $contraseña);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
    <title>Actualizar Usuario</title>
    <style>
        body {
            font-size: 20px;
        }

        h1 {
            font-family: Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif;
            font-size: 55px;
        }

        h3 {
            font-size: 30px;
        }

        #alerta {
            transition: opacity 0.6s ease;
            
        }

        #alerta.fade-out {
            opacity: 0;
            visibility: hidden;
        }
    </style>
        <script>
        // Definir el tiempo máximo de inactividad en milisegundos
        var tiempoInactividad = 2700000; // 45 minutos

        // Variable para almacenar el temporizador
        var temporizadorInactividad;

        // Función que redirige a logout.php cuando el tiempo de inactividad ha pasado
        function cerrarSesion() {
            window.location.href = 'logout.php?error=2'; // Redirigir a logout.php con error de sesión expirada
        }

        // Función para reiniciar el temporizador
        function reiniciarTemporizador() {
            // Limpiar el temporizador anterior
            clearTimeout(temporizadorInactividad);
            // Iniciar un nuevo temporizador
            temporizadorInactividad = setTimeout(cerrarSesion, tiempoInactividad);
        }

        // Detectar eventos de actividad del usuario
        window.onload = reiniciarTemporizador; // Al cargar la página
        document.onmousemove = reiniciarTemporizador; // Al mover el mouse
        document.onkeypress = reiniciarTemporizador; // Al pulsar una tecla
        document.onclick = reiniciarTemporizador; // Al hacer clic
        document.onscroll = reiniciarTemporizador; // Al hacer scroll
    </script>
</head>

<header>
    <br>
    <h1 class="text-center">Actualizar Usuario</h1>
</header>
<div class="mb-3">
    <center><a href="usuarios_READ.php" class="btn btn-primary btn-sm">Volver Lista Usuarios</a></center>
</div>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-10 col-sm-12"> <!-- tamaño -->
                <form method="post" action="" class="mt-1 p-4 border rounded bg-light">
                <?php if ($alerta): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="alerta">
                            <?php echo $alerta; ?>
                        </div>
                        <script>

                            setTimeout(function() {
                                var alerta = document.getElementById("alerta");
                                if (alerta) {
                                    alerta.style.opacity = '0'; // Transición a invisible
                                    setTimeout(function() {
                                        alerta.style.display = 'none'; // Ocultar después de la transición
                                    }, 600); // Tiempo que dura la animación de desvanecimiento
                                }
                            }, 2500); // Tiempo antes de desvanecer
                        </script>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="nombre">Nombre:</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" placeholder ="Ingrese El Nombre" value="<?php echo isset($user['nombre']) ? htmlspecialchars($user['nombre']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="text" class="form-control" id="email" name="email" placeholder ="Ingrese El Correo" value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña:</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Ingrese Una Nueva Contraseña (Opcional)">
                    </div>
                    <div class="form-group">
                        <label for="id_rol">Rol:</label>
                        <select class="form-control" id="id_rol" name="id_rol" required>
                            <option value="">Seleccione un rol</option>
                            <?php
                            // Obtener los roles de la base de datos
                            $sql_roles = "SELECT id, nombre FROM roles WHERE roles.estado = 1";
                            $result_roles = $conn->query($sql_roles);
                            while ($rol = $result_roles->fetch_assoc()) {

                                $selected = ($rol['id'] == $user['id_rol']) ? 'selected' : '';
                                echo "<option value='{$rol['id']}' $selected>{$rol['nombre']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">Actualizar</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>