<?php
session_start();
include '../conexion.php';

// Acceso de roles
if (!isset($_SESSION['usuario'])) {
    header("Location: ../logout.php");
    exit();
}

$id_rol = $_SESSION['id_rol'];
$roles_permitidos = [1, 2, 3, 4, 5, 6];

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
$alerta = "";

if (isset($_GET['success'])) {

    switch ($_GET['success']) {
        case 1:
            $alerta = "Proveedor creado con éxito";
            break;
        case 2:
            $alerta = "Proveedor actualizado con éxito";
            break;
        case 3:
            $alerta = "Proveedor borrado con éxito";
            break;
    }

    // Redirigir a la página sin el parámetro success
    echo "<script>window.history.replaceState(null, null, window.location.pathname);</script>";
}

// Usar Prepared Statements
$sql = "SELECT * FROM proveedores WHERE proveedores.estado = 1";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Proveedores</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
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
    <center>
        <h1>Lista de Proveedores</h1>
    </center><br>
</header>
<?php if ($alerta): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert" id="alerta">
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


<body class="container mt-5">
    <div class="mb-3">
    <?php if ($id_rol == 1 || $id_rol == 2 || $id_rol == 5): ?>
        <a href="proveedores_CREATE.php" class="btn btn-success btn-sm">Añadir Nuevo Proveedor<a>
            <?php endif; ?>
                <a href="../inicio.php" class="btn btn-primary btn-sm">Volver Al Inicio<a>
    </div>
    <table id="proveedoresTable" class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>NIT</th>
                <th>Correo</th>
                <th>Telefono</th>
                <th>Ciudad</th>
                <?php if ($id_rol == 1 || $id_rol == 2 || $id_rol == 5): ?>
                <th>Acciones</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id'] ?></td>
                    <td><?php echo $row['nombre'] ?></td>
                    <td><?php echo $row['NIT'] ?></td>
                    <td><?php echo $row['correo'] ?></td>
                    <td><?php echo $row['telefono'] ?></td>
                    <td><?php echo $row['ciudad'] ?></td>
                    <?php if ($id_rol == 1 || $id_rol == 2 || $id_rol == 5): ?>
                    <td>
                        <a href="proveedores_UPDATE.php?id=<?php echo $row['id']; ?> " class="btn btn-warning btn-sm">Editar<a>
                                <a href="proveedores_DELETE.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estas seguro de borrar este proveedor?')">Borrar<a>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#proveedoresTable').DataTable();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>