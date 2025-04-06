<?php
session_start();
include '../conexion.php';
$id = $_GET['id'];
$alerta = "";

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

$tiempo_inactividad = 2700;

if (isset($_SESSION['ultimo_movimiento'])) {
    $tiempo_transcurrido = time() - $_SESSION['ultimo_movimiento'];

    if ($tiempo_transcurrido > $tiempo_inactividad) {
        header("Location: ../logout.php?error=2");
        exit();
    }
}

$_SESSION['ultimo_movimiento'] = time();

// Obtener datos actuales del cliente
$sql = "SELECT * FROM clientes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $DNI = $_POST['DNI'];
    $ciudad = $_POST['ciudad'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $direccion = $_POST['direccion'];

    // Verificar datos duplicados
    $sql_verificar = "SELECT * FROM clientes WHERE (DNI = ? OR correo = ? OR (telefono = ? AND telefono != '')) AND id != ?";
    $stmt_verificar = $conn->prepare($sql_verificar);
    $stmt_verificar->bind_param("sssi", $DNI, $correo, $telefono, $id);
    $stmt_verificar->execute();
    $resultado_verificacion = $stmt_verificar->get_result();

    if ($resultado_verificacion->num_rows > 0) {
        $row = $resultado_verificacion->fetch_assoc();

        // Solo mostrar error si el valor nuevo ya existe en otro registro y no es igual al valor actual
        if ($row['DNI'] == $DNI && $DNI != $user['DNI']) {
            $alerta .= "Error: El DNI ya existe en la base de datos.<br>";
        }
        if ($row['correo'] == $correo && $correo != $user['correo']) {
            $alerta .= "Error: El correo ya existe en la base de datos.<br>";
        }
        if ($row['telefono'] == $telefono && $telefono != $user['telefono'] && $telefono != "") {
            $alerta .= "Error: El teléfono ya existe en la base de datos.<br>";
        }
    } 
    
    if ($alerta === "") {
        // Realizar la actualización solo si no hay duplicados
        $sql_update = "UPDATE clientes SET nombre = ?, correo = ?, apellido = ?, DNI = ?, ciudad = ?, telefono = ?, direccion = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sssssssi", $nombre, $correo, $apellido, $DNI, $ciudad, $telefono, $direccion, $id);

        if ($stmt_update->execute()) {
            header('Location: clientes_READ.php?success=2');
            exit();
        } else {
            echo "Error: " . $stmt_update->error;
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
    <title>Actualizar Cliente</title>
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
    <h1 class="text-center">Actualizar Cliente</h1>
</header>
<div class="mb-3">
    <center><a href="clientes_READ.php" class="btn btn-primary btn-sm">Volver Lista Clientes<a></center>
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
                        <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ingrese El Nombre" value="<?php echo isset($user['nombre']) ? htmlspecialchars($user['nombre']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="apellido">Apellido:</label>
                        <input type="text" class="form-control" id="apellido" name="apellido" placeholder="Ingrese El Apellido (Opcional)" value="<?php echo isset($user['apellido']) ? htmlspecialchars($user['apellido']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="DNI">DNI:</label>
                        <input type="number" class="form-control" id="DNI" name="DNI" placeholder="Ingrese El DNI" value="<?php echo isset($user['DNI']) ? htmlspecialchars($user['DNI']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="ciudad">Ciudad:</label>
                        <input type="text" class="form-control" id="ciudad" name="ciudad" placeholder="Ingrese La Ciudad" value="<?php echo isset($user['ciudad']) ? htmlspecialchars($user['ciudad']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono:</label>
                        <input type="number" class="form-control" id="telefono" name="telefono" placeholder="Ingrese El Telefono" value="<?php echo isset($user['telefono']) ? htmlspecialchars($user['telefono']) : ''; ?>" placeholder="Opcional">
                    </div>
                    <div class="form-group">
                        <label for="correo">Correo:</label>
                        <input type="email" class="form-control" id="correo" name="correo" placeholder="Ingrese El Correo" value="<?php echo isset($user['correo']) ? htmlspecialchars($user['correo']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="direccion">Dirección:</label>
                        <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Ingrese La Direccion" value="<?php echo isset($user['direccion']) ? htmlspecialchars($user['direccion']) : ''; ?>" required>
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