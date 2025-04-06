<?php
session_start();
include '../conexion.php';

// Acceso de roles
if (!isset($_SESSION['usuario'])) {
    header("Location: ../logout.php");
    exit();
}

$id_rol = $_SESSION['id_rol'];
$roles_permitidos = [1, 2, 4];

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

$error = "";
$id_compra = $_GET['id'];
$cantidad_anterior = 0;
$precio_unitario = 0;
$total = 0;

// Consultar la compra actual
$sql_compra = "SELECT id_proveedor, id_producto, cantidad, precio_unitario_producto FROM compras WHERE id = ?";
$stmt_compra = $conn->prepare($sql_compra);
$stmt_compra->bind_param("i", $id_compra);
$stmt_compra->execute();
$result_compra = $stmt_compra->get_result();
$compra = $result_compra->fetch_assoc();
$stmt_compra->close();

if ($compra) {
    $id_proveedor = $compra['id_proveedor'];
    $id_producto_anterior = $compra['id_producto'];
    $cantidad_anterior = $compra['cantidad'];
    $precio_unitario_anterior = $compra['precio_unitario_producto'];
} else {
    $error = "Error: compra no encontrada.";
}

// Consultar proveedores
$sql_proveedores = "SELECT id, nombre AS nombre_completo FROM proveedores WHERE estado = 1";
$result_proveedores = $conn->query($sql_proveedores);

// Consultar productos
$sql_productos = "SELECT id, nombre, precio_unitario, stock FROM productos WHERE estado = 1";
$result_productos = $conn->query($sql_productos);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_proveedor = $_POST['id_proveedor'];
    $id_producto_nuevo = $_POST['id_producto'];
    $cantidad = $_POST['cantidad'];

    if ($cantidad <= 0 || $id_proveedor == "" || $id_producto_nuevo == "") {
        $error = "Error: Todos los campos son obligatorios y la cantidad debe ser mayor que 0.";
    } else {
        $sql_recuperar_stock = "UPDATE productos SET stock = stock - ? WHERE id = ?";
        $stmt_recuperar_stock = $conn->prepare($sql_recuperar_stock);
        $stmt_recuperar_stock->bind_param("ii", $cantidad_anterior, $id_producto_anterior);
        $stmt_recuperar_stock->execute();
        $stmt_recuperar_stock->close();

        $sql_stock_nuevo = "SELECT stock, precio_unitario FROM productos WHERE id = ?";
        $stmt_stock_nuevo = $conn->prepare($sql_stock_nuevo);
        $stmt_stock_nuevo->bind_param("i", $id_producto_nuevo);
        $stmt_stock_nuevo->execute();
        $result_stock_nuevo = $stmt_stock_nuevo->get_result();
        $producto_nuevo = $result_stock_nuevo->fetch_assoc();
        $stmt_stock_nuevo->close();

        $precio_unitario = $producto_nuevo['precio_unitario'];
        $total = $precio_unitario * $cantidad;
        $fecha_actualizacion = date('Y-m-d H:i:s');

        $sql_update = "UPDATE compras SET id_proveedor = ?, id_producto = ?, precio_unitario_producto = ?, total = ?, cantidad = ?, fecha_compra = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("iidddsi", $id_proveedor, $id_producto_nuevo, $precio_unitario, $total, $cantidad, $fecha_actualizacion, $id_compra);

        if ($stmt_update->execute()) {
            $sql_update_stock = "UPDATE productos SET stock = stock + ? WHERE id = ?";
            $stmt_update_stock = $conn->prepare($sql_update_stock);
            $stmt_update_stock->bind_param("ii", $cantidad, $id_producto_nuevo);

            if ($stmt_update_stock->execute()) {
                header('Location: compras_READ.php?success=1');
            } else {
                $error = "Error al actualizar el stock del nuevo producto: " . $conn->error;
            }
            $stmt_update_stock->close();
        } else {
            $error = "Error al actualizar la compra: " . $conn->error;
        }
        $stmt_update->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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
    <title>Actualizar compra</title>
    <script>
        function actualizarPrecio() {
            var selectProducto = document.getElementById("producto");
            var precioUnitarioInput = document.getElementById("precio_unitario");
            var totalInput = document.getElementById("total");
            var cantidadInput = document.getElementById("cantidad");

            var precioUnitario = selectProducto.options[selectProducto.selectedIndex].getAttribute("data-precio");
            precioUnitarioInput.value = precioUnitario;

            var cantidad = cantidadInput.value;
            if (cantidad > 0) {
                totalInput.value = (precioUnitario * cantidad).toFixed(2);
            } else {
                totalInput.value = 0;
            }
        }

        function calcularTotal() {
            var precioUnitario = document.getElementById("precio_unitario").value;
            var cantidad = document.getElementById("cantidad").value;
            var totalInput = document.getElementById("total");

            if (cantidad > 0) {
                totalInput.value = (precioUnitario * cantidad).toFixed(2);
            } else {
                totalInput.value = 0;
            }
        }
    </script>
</head>
<header>
    <br>
    <h1 class="text-center">Actualizar Compra</h1>
</header>
<div class="mb-3">
    <center><a href="compras_READ.php" class="btn btn-primary btn-sm">Volver Lista Compras</a></center>
</div>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-10 col-sm-12">
                <form method="post" action="" class="mt-1 p-4 border rounded bg-light">
                <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="alerta">
                            <?php echo $error; ?>
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
                        <label for="id_proveedor">Proveedor:</label>
                        <select name="id_proveedor" class="form-control" required>
                            <option value="">Seleccione un proveedor</option>
                            <?php while ($row = $result_proveedores->fetch_assoc()) { ?>
                                <option value="<?php echo $row['id']; ?>" <?php echo ($row['id'] == $id_proveedor) ? 'selected' : ''; ?>>
                                    <?php echo $row['nombre_completo']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="id_producto">Producto:</label>
                        <select id="producto" name="id_producto" class="form-control" onchange="actualizarPrecio()" required>
                            <option value="">Seleccione un producto</option>
                            <?php while ($row = $result_productos->fetch_assoc()) { ?>
                                <option value="<?php echo $row['id']; ?>" data-precio="<?php echo $row['precio_unitario']; ?>"
                                    <?php echo ($row['id'] == $id_producto_anterior) ? 'selected' : ''; ?>>
                                    <?php echo $row['nombre']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="cantidad">Cantidad:</label>
                        <input type="number" id="cantidad" name="cantidad" class="form-control" min="1" value="<?php echo $cantidad_anterior; ?>" oninput="calcularTotal()" required>
                    </div>

                    <div class="form-group">
                        <label for="precio_unitario">Precio Unitario:</label>
                        <input type="text" id="precio_unitario" name="precio_unitario" class="form-control" value="<?php echo $precio_unitario_anterior; ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="total">Total:</label>
                        <input type="text" id="total" name="total" class="form-control" value="<?php echo $precio_unitario_anterior * $cantidad_anterior; ?>" readonly>
                    </div>

                    <button type="submit" class="btn btn-success btn-block">Actualizar Compra</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>