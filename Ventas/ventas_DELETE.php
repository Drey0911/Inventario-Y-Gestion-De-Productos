<?php
session_start();
include '../conexion.php';

// Verificar si es una petición AJAX
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if (!$isAjax) {
    // Si no es AJAX, redirigir o manejar como antes
    header("Location: ventas_READ.php");
    exit();
}

header('Content-Type: application/json');

// Validar acceso
if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit();
}

$id_rol = $_SESSION['id_rol'];
$roles_permitidos = [1, 2, 3]; 

if (!in_array($id_rol, $roles_permitidos)) {
    echo json_encode(['success' => false, 'message' => 'Permisos insuficientes']);
    exit();
}

// Obtener ID de la venta a eliminar
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit();
}

try {
    // Primero verificamos si la venta existe
    $check = $conn->prepare("SELECT id FROM ventas WHERE id = ? AND estado = 1");
    $check->bind_param("i", $id);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'La venta no existe o ya fue eliminada']);
        exit();
    }
    
    // Eliminar la venta (cambiar estado a 0)
    $stmt = $conn->prepare("UPDATE ventas SET estado = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Verificar si realmente se eliminó
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Venta eliminada con éxito']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo eliminar la venta']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al ejecutar la consulta']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($check)) $check->close();
    $conn->close();
}
?>