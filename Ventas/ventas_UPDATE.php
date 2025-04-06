<?php
session_start();
include '../conexion.php';

// Acceso permitido de ciertos roles
if (!isset($_SESSION['usuario'])) {
    header("Location: ../logout.php");
    exit();
}

$id_rol = $_SESSION['id_rol'];
$roles_permitidos = [1, 2, 3];

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

$error = "";
$id_venta = $_GET['id'];
$cantidad_anterior = 0;
$precio_unitario = 0;
$total = 0;

// Consultar la venta actual
$sql_venta = "SELECT id_cliente, id_producto, cantidad, precio_unitario_producto FROM ventas WHERE id = ?";
$stmt_venta = $conn->prepare($sql_venta);
$stmt_venta->bind_param("i", $id_venta);
$stmt_venta->execute();
$result_venta = $stmt_venta->get_result();
$venta = $result_venta->fetch_assoc();

if ($venta) {
    $id_cliente = $venta['id_cliente'];
    $id_producto_anterior = $venta['id_producto'];
    $cantidad_anterior = $venta['cantidad'];
    $precio_unitario_anterior = $venta['precio_unitario_producto'];
} else {
    $error = "Error: Venta no encontrada.";
}

// Consultar clientes
$sql_clientes = "SELECT id, CONCAT(nombre, ' ', apellido) AS nombre_completo FROM clientes WHERE estado = 1";
$stmt_clientes = $conn->prepare($sql_clientes);
$stmt_clientes->execute();
$result_clientes = $stmt_clientes->get_result();

// Consultar productos
$sql_productos = "SELECT id, nombre, precio_unitario, stock FROM productos WHERE estado = 1";
$stmt_productos = $conn->prepare($sql_productos);
$stmt_productos->execute();
$result_productos = $stmt_productos->get_result();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_cliente = $_POST['id_cliente'];
    $id_producto_nuevo = $_POST['id_producto'];
    $cantidad = $_POST['cantidad'];

    if ($cantidad <= 0 || $id_cliente == "" || $id_producto_nuevo == "") {
        $error = "Error: Todos los campos son obligatorios y la cantidad debe ser mayor que 0.";
    } else {
        // Recuperar stock del producto anterior
        $sql_recuperar_stock = "UPDATE productos SET stock = stock + ? WHERE id = ?";
        $stmt_recuperar_stock = $conn->prepare($sql_recuperar_stock);
        $stmt_recuperar_stock->bind_param("ii", $cantidad_anterior, $id_producto_anterior);
        $stmt_recuperar_stock->execute();

        // Verificar el stock del nuevo producto
        $sql_stock_nuevo = "SELECT stock, precio_unitario FROM productos WHERE id = ?";
        $stmt_stock_nuevo = $conn->prepare($sql_stock_nuevo);
        $stmt_stock_nuevo->bind_param("i", $id_producto_nuevo);
        $stmt_stock_nuevo->execute();
        $result_stock_nuevo = $stmt_stock_nuevo->get_result();
        $producto_nuevo = $result_stock_nuevo->fetch_assoc();

        if ($producto_nuevo['stock'] < $cantidad) {
            $error = "Error: No hay suficiente stock para realizar la actualización. Stock disponible: " . $producto_nuevo['stock'];
            $sql_revert_stock = "UPDATE productos SET stock = stock - ? WHERE id = ?";
            $stmt_revert_stock = $conn->prepare($sql_revert_stock);
            $stmt_revert_stock->bind_param("ii", $cantidad_anterior, $id_producto_anterior);
            $stmt_revert_stock->execute();
        } else {
            $precio_unitario = $producto_nuevo['precio_unitario'];
            $total = $precio_unitario * $cantidad;
            $fecha_actualizacion = date('Y-m-d H:i:s');

            $sql_update = "UPDATE ventas SET id_cliente = ?, id_producto = ?, precio_unitario_producto = ?, total = ?, cantidad = ?, fecha_venta = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("iidisii", $id_cliente, $id_producto_nuevo, $precio_unitario, $total, $cantidad, $fecha_actualizacion, $id_venta);

            if ($stmt_update->execute()) {
                $sql_update_stock = "UPDATE productos SET stock = stock - ? WHERE id = ?";
                $stmt_update_stock = $conn->prepare($sql_update_stock);
                $stmt_update_stock->bind_param("ii", $cantidad, $id_producto_nuevo);

                if ($stmt_update_stock->execute()) {
                    header('Location: ventas_READ.php?success=1');
                } else {
                    $error = "Error al actualizar el stock del nuevo producto: " . $conn->error;
                }
            } else {
                $error = "Error al actualizar la venta: " . $conn->error;
            }
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
    <title>Actualizar Venta</title>
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
    <h1 class="text-center">Actualizar Venta</h1>
</header>
<div class="mb-3">
    <center><a href="ventas_READ.php" class="btn btn-primary btn-sm">Volver Lista Ventas</a></center>
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
                        <label for="id_cliente">Cliente:</label>
                        <select name="id_cliente" class="form-control" required>
                            <option value="">Seleccione un cliente</option>
                            <?php while ($row = $result_clientes->fetch_assoc()) { ?>
                                <option value="<?php echo $row['id']; ?>" <?php echo ($row['id'] == $id_cliente) ? 'selected' : ''; ?>>
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

                    <button type="submit" class="btn btn-success btn-block">Actualizar Venta</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>