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

//Manejo de verificacion de datos duplicados unicos
$sql_verificar = "SELECT * FROM proveedores WHERE (nombre = ? OR NIT = ? OR correo = ? OR telefono = ?)";
$stmt_verificar = $conn->prepare($sql_verificar);
$stmt_verificar->bind_param("ssss", $nombre, $NIT, $correo, $telefono);
$stmt_verificar->execute();
$resultado_verificacion = $stmt_verificar->get_result();

if ($resultado_verificacion->num_rows > 0) {
    // Verificar cuál campo está duplicado
    $row = $resultado_verificacion->fetch_assoc();
    
    if ($row['nombre'] == $nombre) {
        $alerta= "Error: Este proveedor ya existe en la base de datos.<br>";
    }
    if ($row['NIT'] == $NIT) {
        $alerta= "Error: Este NIT ya existe en la base de datos.<br>";
    }
    if ($row['correo'] == $correo) {
        $alerta= "Error: Este correo ya existe en la base de datos.<br>";
    }
    if ($row['telefono'] == $telefono) {
        $alerta= "Error: Este telefono ya existe en la base de datos.<br>";
    }
} else {

    // Usar Prepared Statements
    $sql = "INSERT INTO proveedores (nombre, NIT, correo, telefono, ciudad) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $nombre, $NIT, $correo, $telefono, $ciudad);

    if ($stmt->execute()) {
        header('Location: proveedores_READ.php?success=1');
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Creación De Proveedores</title>
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
    <h1 class="text-center">Añadir Nuevo Proveedor</h1>
</header>
<div class="mb-3">
<center><a href="proveedores_READ.php" class="btn btn-primary btn-sm">Volver Lista Proveedores<a></center>
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
                        <input type="text" class="form-control" id="nombre" name="nombre" placeholder ="Ingrese El Nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="NIT">NIT:</label>
                        <input type="number" class="form-control" id="NIT" name="NIT" placeholder ="Ingrese EL NIT" required>
                    </div>
                    <div class="form-group">
                        <label for="correo">Correo:</label>
                        <input type="email" class="form-control" id="correo" name="correo" placeholder ="Ingrese El Correo" required>
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono:</label>
                        <input type="number" class="form-control" id="telefono" name="telefono" placeholder ="Ingrese El Telefono (Opcional)" placeholder="Opcional">
                    </div>
                    <div class="form-group">
                        <label for="ciudad">Ciudad:</label>
                        <input type="text" class="form-control" id="ciudad" name="ciudad" placeholder ="Ingrese La Ciudad" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">Añadir</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
