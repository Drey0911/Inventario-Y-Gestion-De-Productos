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
$roles_permitidos = [1, 2, 4];

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

// Obtener datos actuales de la compra
$sql = "SELECT c.id_proveedor, c.id_producto, c.cantidad, c.precio_unitario_producto, c.total, 
               p.nombre AS producto_nombre, pr.nombre AS proveedor_nombre
        FROM compras c
        JOIN productos p ON c.id_producto = p.id
        JOIN proveedores pr ON c.id_proveedor = pr.id
        WHERE c.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$compra = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_proveedor = $_POST['id_proveedor'];
    $id_producto_nuevo = $_POST['id_producto'];
    $cantidad = $_POST['cantidad'];

    if ($cantidad <= 0 || $id_proveedor == "" || $id_producto_nuevo == "") {
        $alerta = "Error: Todos los campos son obligatorios y la cantidad debe ser mayor que 0";
    } else {
        $sql_recuperar_stock = "UPDATE productos SET stock = stock - ? WHERE id = ?";
        $stmt_recuperar_stock = $conn->prepare($sql_recuperar_stock);
        $stmt_recuperar_stock->bind_param("ii", $compra['cantidad'], $compra['id_producto']);
        $stmt_recuperar_stock->execute();

        $sql_stock_nuevo = "SELECT stock, precio_unitario FROM productos WHERE id = ?";
        $stmt_stock_nuevo = $conn->prepare($sql_stock_nuevo);
        $stmt_stock_nuevo->bind_param("i", $id_producto_nuevo);
        $stmt_stock_nuevo->execute();
        $result_stock_nuevo = $stmt_stock_nuevo->get_result();
        $producto_nuevo = $result_stock_nuevo->fetch_assoc();

        $precio_unitario = $producto_nuevo['precio_unitario'];
        $total = $precio_unitario * $cantidad;
        $fecha_actualizacion = date('Y-m-d H:i:s');

        $sql_update = "UPDATE compras SET id_proveedor = ?, id_producto = ?, precio_unitario_producto = ?, total = ?, cantidad = ?, fecha_compra = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("iidddsi", $id_proveedor, $id_producto_nuevo, $precio_unitario, $total, $cantidad, $fecha_actualizacion, $id);

        if ($stmt_update->execute()) {
            $sql_update_stock = "UPDATE productos SET stock = stock + ? WHERE id = ?";
            $stmt_update_stock = $conn->prepare($sql_update_stock);
            $stmt_update_stock->bind_param("ii", $cantidad, $id_producto_nuevo);

            if ($stmt_update_stock->execute()) {
                header('Location: compras_READ.php?success=2');
                exit();
            } else {
                $alerta = "Error al actualizar el stock del nuevo producto: " . $conn->error;
            }
        } else {
            $alerta = "Error al actualizar la compra: " . $conn->error;
        }
    }
}

// Consultar proveedores
$sql_proveedores = "SELECT id, nombre AS nombre_completo FROM proveedores WHERE estado = 1";
$stmt_proveedores = $conn->prepare($sql_proveedores);
$stmt_proveedores->execute();
$result_proveedores = $stmt_proveedores->get_result();

// Consultar productos
$sql_productos = "SELECT id, nombre, precio_unitario, stock FROM productos WHERE estado = 1";
$stmt_productos = $conn->prepare($sql_productos);
$stmt_productos->execute();
$result_productos = $stmt_productos->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Compra</title>
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
                    <a href="compras_READ.php" class="text-gray-300 hover:text-white flex items-center">
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
            <h1 class="text-3xl font-bold gradient-text">Actualizar Compra</h1>
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
                        <label for="id_proveedor" class="block text-sm font-medium text-gray-300 mb-2">Proveedor</label>
                        <select name="id_proveedor" required class="input-field w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">Seleccione un proveedor</option>
                            <?php while ($row = $result_proveedores->fetch_assoc()) { ?>
                                <option value="<?php echo $row['id']; ?>" <?php echo ($row['id'] == $compra['id_proveedor']) ? 'selected' : ''; ?>>
                                    <?php echo $row['nombre_completo']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="mb-6">
                        <label for="id_producto" class="block text-sm font-medium text-gray-300 mb-2">Producto</label>
                        <select id="producto" name="id_producto" required onchange="actualizarPrecio()" class="input-field w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">Seleccione un producto</option>
                            <?php while ($row = $result_productos->fetch_assoc()) { ?>
                                <option value="<?php echo $row['id']; ?>" data-precio="<?php echo $row['precio_unitario']; ?>"
                                    <?php echo ($row['id'] == $compra['id_producto']) ? 'selected' : ''; ?>>
                                    <?php echo $row['nombre']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="mb-6">
                        <label for="cantidad" class="block text-sm font-medium text-gray-300 mb-2">Cantidad</label>
                        <input type="number" id="cantidad" name="cantidad" min="1" value="<?php echo $compra['cantidad']; ?>" oninput="calcularTotal()" required class="input-field w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div class="mb-6">
                        <label for="precio_unitario" class="block text-sm font-medium text-gray-300 mb-2">Precio Unitario</label>
                        <input type="text" id="precio_unitario" name="precio_unitario" value="<?php echo $compra['precio_unitario_producto']; ?>" readonly class="input-field w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div class="mb-6">
                        <label for="total" class="block text-sm font-medium text-gray-300 mb-2">Total</label>
                        <input type="text" id="total" name="total" value="<?php echo $compra['total']; ?>" readonly class="input-field w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        Actualizar Compra
                    </button>
                </form>
            </div>
        </div>
    </main>

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