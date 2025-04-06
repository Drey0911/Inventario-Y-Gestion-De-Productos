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
    <title>CRUD - Menú Principal</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-size: x-large;
        }

        h1 {
            font-family: Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif;
            font-size: 55px;
        }

        h3 {
            font-size: 30px;
        }

        .list-group-item {
            display: flex;
            align-items: center;
            padding: 10px;
        }

        .list-group-item img {
            width: 54px;
            height: 54px;
            margin-right: 10px;
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

<body>
    <div class="container mt-5">
        <center>
            <h1>Gestión de Inventario</h1>
        </center>
        <center>
            <h3>Seleccione el CRUD que usted desea gestionar</h3>
        </center>

        <br>
        <div class="list-group">
        <?php if ($id_rol == 1 || $id_rol == 2 || $id_rol == 3 || $id_rol == 4 || $id_rol == 5 || $id_rol == 6): ?>
            <a href="Productos/productos_READ.php" class="list-group-item list-group-item-action">
                <img src="Imagenes/icono_1.png" alt="Gestión de Productos"> Gestión de Productos
            </a><br>
            <?php endif; ?>
            <?php if ($id_rol == 1 || $id_rol == 2 || $id_rol == 4 || $id_rol == 5 || $id_rol == 6): ?>
            <a href="Proveedores/proveedores_READ.php" class="list-group-item list-group-item-action">
                <img src="Imagenes/icono_2.png" alt="Gestión de Proveedores"> Gestión de Proveedores
            </a><br>
            <?php endif; ?>
            <?php if ($id_rol == 1 || $id_rol == 2 || $id_rol == 3 || $id_rol == 5 || $id_rol == 6): ?>
            <a href="Clientes/clientes_READ.php" class="list-group-item list-group-item-action">
                <img src="Imagenes/icono_3.png" alt="Gestión de Clientes"> Gestión de Clientes
            </a><br> 
            <?php endif; ?>
            <?php if ($id_rol == 1 || $id_rol == 2 || $id_rol == 3 || $id_rol == 5 || $id_rol == 6): ?>
            <a href="Ventas/ventas_READ.php" class="list-group-item list-group-item-action">
                <img src="Imagenes/icono_4.png" alt="Gestión de Ventas"> Gestión de Ventas
            </a><br>
            <?php endif; ?>
            <?php if ($id_rol == 1 || $id_rol == 2 || $id_rol == 4 || $id_rol == 5 || $id_rol == 6): ?>
            <a href="Compras/compras_READ.php" class="list-group-item list-group-item-action">
                <img src="Imagenes/icono_5.png" alt="Gestión de Compras"> Gestión de Compras
            </a><br>
            <?php endif; ?>
            <?php if ($id_rol == 1 || $id_rol == 2): ?>
                <a href="Usuarios/usuarios_READ.php" class="list-group-item list-group-item-action">
                <img src="Imagenes/icono_6.png" alt="Gestión de Usuarios"> Gestión de Usuarios
            </a><br>
            <?php endif; ?>
            <?php if ($id_rol == 1 || $id_rol == 2): ?>
                <a href="Roles/roles_READ.php" class="list-group-item list-group-item-action">
                <img src="Imagenes/icono_7.png" alt="Gestión de Roles"> Gestión de Roles
            </a><br>
            <?php endif; ?>
        </div>
        <br>
    </div>
</body>

<footer style="text-align: center; padding: 20px;">
    <h3>Usuario: <?php echo htmlspecialchars($usuario_nombre); ?> | Rol: <?php echo htmlspecialchars($rol_nombre); ?></h3>
    
    <form method="post" style="display: inline;">
        <button type="submit" name="cerrar_sesion" class="btn btn-danger">Cerrar Sesión</button>
    </form>

    <hr style="margin: 20px 0; border-color: #ccc;">
    <p>&copy; <span id="year"></span> By Andrey Stteven Mantilla Leon</p>
</footer>

<script>
    document.getElementById("year").textContent = new Date().getFullYear();
</script>


</html>