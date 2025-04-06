<?php
session_start();
include '../conexion.php';
$id = $_GET['id'];
$alerta = "";

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

    // Verificar datos duplicados
    $sql_verificar = "SELECT * FROM roles WHERE (nombre = ?) AND id != ?";
    $stmt_verificar = $conn->prepare($sql_verificar);
    $stmt_verificar->bind_param("si", $nombre, $id);
    $stmt_verificar->execute();
    $resultado_verificacion = $stmt_verificar->get_result();

    if ($resultado_verificacion->num_rows > 0) {
        $row = $resultado_verificacion->fetch_assoc();

        // Solo mostrar error si el valor nuevo ya existe en otro registro y no es igual al valor actual
        if ($row['nombre'] == $nombre && $nombre != $user['nombre']) {
            $alerta .= "Error: El Rol ya existe en la base de datos.<br>";
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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css"> 
    <title>Actualizar Producto</title>
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
    <h1 class="text-center">Actualizar Rol</h1>
</header>
<div class="mb-3">
<center><a href="roles_READ.php" class="btn btn-primary btn-sm">Volver Lista Roles<a></center>
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
        // Function to hide the alert
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
                        <label for="descripcion">Descripcion:</label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion" value="<?php echo isset($user['descripcion']) ? htmlspecialchars($user['descripcion']) : ''; ?>" placeholder ="Ingrese La Descripcion (Opcional)">
                        <br>
                    <button type="submit" class="btn btn-success btn-block">Actualizar</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
