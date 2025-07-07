<?php
session_start();
include '../conexion.php';
$alerta = "";
$tipo_alerta = "";

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

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 1:
            $alerta = "Compra creada con éxito";
            $tipo_alerta = "success";
            break;
        case 2:
            $alerta = "Compra actualizada con éxito";
            $tipo_alerta = "success";
            break;
        case 3:
            $alerta = "Compra borrada con éxito";
            $tipo_alerta = "success";
            break;
    }
    echo "<script>window.history.replaceState(null, null, window.location.pathname);</script>";
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

$sql = "SELECT c.id, 
               pr.nombre AS nombre_proveedor, 
               p.nombre AS nombre_producto, 
               c.cantidad, 
               c.precio_unitario_producto, 
               c.total, 
               c.fecha_compra 
        FROM compras c 
        JOIN proveedores pr ON c.id_proveedor = pr.id 
        JOIN productos p ON c.id_producto = p.id 
        WHERE c.estado = 1";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Compras</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="../styles/Styles.css">
    <link rel="stylesheet" href="../styles/Datatable.css">
    <link rel="stylesheet" href="../styles/Sidebar.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen text-gray-100 bg-gray-900">
    <!-- Contenedor principal con sidebar y contenido -->
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="sidebar w-64 bg-gray-800 shadow-lg flex flex-col">
            <!-- Logo -->
            <div class="p-4 border-b border-gray-700">
                <div class="flex items-center space-x-2">
                    <span class="text-xl font-bold gradient-text">INVDrey</span>
                </div>
            </div>
            
            <!-- Menú de navegación -->
            <div class="flex-1 overflow-y-auto py-4">
                <nav class="space-y-1 px-4">
                    <a href="../inicio.php" class="sidebar-link flex items-center px-4 py-3 text-gray-300 hover:text-white hover:bg-gray-700 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Inicio
                    </a>
                    
                    <a href="../productos/productos_READ.php" class="sidebar-link flex items-center px-4 py-3 text-gray-300 hover:text-white hover:bg-gray-700 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        Productos
                    </a>
                    
                    <a href="../proveedores/proveedores_READ.php" class="sidebar-link flex items-center px-4 py-3 text-gray-300 hover:text-white hover:bg-gray-700 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Proveedores
                    </a>
                    
                    <a href="../Compras/compras_READ.php" class="sidebar-link flex items-center px-4 py-3 text-white bg-gray-700 rounded-lg active-link">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Compras
                    </a>
                    
                    <a href="../ventas/ventas_READ.php" class="sidebar-link flex items-center px-4 py-3 text-gray-300 hover:text-white hover:bg-gray-700 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Ventas
                    </a>
                    
                    <a href="../clientes/clientes_READ.php" class="sidebar-link flex items-center px-4 py-3 text-gray-300 hover:text-white hover:bg-gray-700 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Clientes
                    </a>
                    <?php if ($id_rol == 1 || $id_rol == 2): ?>
                        <a href="../roles/roles_READ.php" class="sidebar-link flex items-center px-4 py-3 text-gray-300 hover:text-white hover:bg-gray-700 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        </svg>
                        Roles - Admin
                    </a>
                    <?php endif; ?>
                     <?php if ($id_rol == 1 || $id_rol == 2): ?>
                        <a href="../Usuarios/usuarios_READ.php" class="sidebar-link flex items-center px-4 py-3 text-gray-300 hover:text-white hover:bg-gray-700 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Usuarios - Admin
                    </a>
                    <?php endif; ?>
                </nav>
            </div>
            
            <!-- Información de usuario -->
            <div class="p-4 border-t border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-white"><?php echo $_SESSION['usuario']; ?></p>
                        <a href="../logout.php" class="text-xs text-gray-400 hover:text-purple-400">Cerrar sesión</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="flex-1 overflow-y-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold gradient-text">Gestión de Compras</h1>
                        <p class="text-gray-400">Listado completo de compras del sistema</p>
                    </div>
                    <?php if ($id_rol == 1 || $id_rol == 2 || $id_rol == 4): ?>
                    <a href="compras_CREATE.php" class="btn-action flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 w-full sm:w-auto justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <span class="hidden sm:inline">Nueva Compra</span>
                    </a>
                    <?php endif; ?>
                </div>

                <?php if ($alerta): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: '<?php echo $tipo_alerta ?>',
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

                <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                    <div class="p-4 sm:p-6">
                        <table id="comprasTable" class="w-full display responsive nowrap" style="width:100%">
                            <thead class="bg-gray-700">
                                <tr>
                                    <th>ID</th>
                                    <th>Proveedor</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Total</th>
                                    <th>Fecha de Compra</th>
                                    <?php if ($id_rol == 1 || $id_rol == 2 || $id_rol == 4): ?>
                                    <th data-priority="1">Acciones</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id'] ?></td>
                                    <td><?php echo $row['nombre_proveedor'] ?></td>
                                    <td><?php echo $row['nombre_producto'] ?></td>
                                    <td><?php echo $row['cantidad'] ?></td>
                                    <td><?php echo $row['precio_unitario_producto'] ?></td>
                                    <td><?php echo $row['total'] ?></td>
                                    <td><?php echo $row['fecha_compra'] ?></td>
                                    <?php if ($id_rol == 1 || $id_rol == 2 || $id_rol == 4): ?>
                                    <td>
                                        <div class="flex flex-wrap gap-2">
                                            <a href="compras_UPDATE.php?id=<?php echo $row['id']; ?>" 
                                               class="btn-action flex items-center px-3 py-1 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 relative"
                                               data-tooltip="Editar">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                <span class="hidden sm:inline">Editar</span>
                                            </a>
                                            <a href="#" 
                                                class="btn-action flex items-center px-3 py-1 bg-red-600 text-white rounded-md hover:bg-red-700 relative"
                                                data-tooltip="Eliminar"
                                                onclick="confirmDelete(<?php echo $row['id']; ?>)">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                <span class="hidden sm:inline">Eliminar</span>
                                            </a>
                                        </div>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#comprasTable').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                },
                initComplete: function() {
                    $('.dataTables_length select').addClass('dark:bg-gray-700 dark:border-gray-600 dark:text-white');
                    $('.dataTables_filter input').addClass('dark:bg-gray-700 dark:border-gray-600 dark:text-white');
                }
            });
        });

        function confirmDelete(compraId) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡No podrás revertir esta acción!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#7f29c2',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                background: '#1F2937',
                color: '#FFF'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteCompra(compraId);
                }
            });
            return false;
        }

        function deleteCompra(compraId) {
            $.ajax({
                url: 'compras_DELETE.php',
                type: 'POST',
                data: { id: compraId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Compra eliminada',
                            text: response.message,
                            background: '#1f2937',
                            color: '#fff',
                            confirmButtonColor: '#7f29c2',
                            timer: 2000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });
                        
                        // Eliminar la fila de la tabla
                        $('#comprasTable').DataTable().row($('a[onclick="confirmDelete(' + compraId + ')"]').closest('tr')).remove().draw();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message,
                            background: '#1f2937',
                            color: '#fff',
                            confirmButtonColor: '#7f29c2'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al procesar la solicitud',
                        background: '#1f2937',
                        color: '#fff',
                        confirmButtonColor: '#7f29c2'
                    });
                }
            });
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