<?php
session_start();
include('conexion.php');

$alerta = "";
$tipo_alerta = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["usuario"]) || empty($_POST["pass"])) {
        $alerta = "Los Campos están vacíos";
        $tipo_alerta = "error";
    } else {
        $usuario = $conn->real_escape_string($_POST["usuario"]);
        $clave = $conn->real_escape_string($_POST["pass"]);

        $stmt = $conn->prepare("SELECT id_rol, nombre, password, intentosLogin, estado, ultimo_intento FROM usuarios WHERE email=?");
        $stmt->bind_param('s', $usuario);
        $stmt->execute();
        $sql = $stmt->get_result();

        if ($sql->num_rows > 0) {
            $datos = $sql->fetch_object();
            $password_encriptada = $datos->password;
            $intentos = $datos->intentosLogin;
            $estado = $datos->estado;
            $ultimo_intento = $datos->ultimo_intento;

            $tiempo_actual = time();
            $tiempo_ultimo_intento = strtotime($ultimo_intento);

            if ($ultimo_intento && ($tiempo_actual - $tiempo_ultimo_intento) >= 600) { 
                $intentos = 0;
                $stmt_reset_intentos = $conn->prepare("UPDATE usuarios SET intentosLogin = 0 WHERE email = ?");
                $stmt_reset_intentos->bind_param('s', $usuario);
                $stmt_reset_intentos->execute();
            }

            if ($estado == 3) {
                $tipo_alerta = "error";
                $alerta = "Usuario bloqueado, Solicite desbloqueo con un administrador";
            } else {
                if (password_verify($clave, $password_encriptada)) {
                    $stmt_reset = $conn->prepare("UPDATE usuarios SET intentosLogin = 0, ultimo_intento = NULL WHERE email = ?");
                    $stmt_reset->bind_param('s', $usuario);
                    $stmt_reset->execute();

                    $_SESSION['usuario'] = $datos->nombre;
                    $_SESSION['id_rol'] = $datos->id_rol;
                    header("location:inicio.php");
                    exit();
                } else {
                    $intentos++;
                    $stmt_update = $conn->prepare("UPDATE usuarios SET intentosLogin = ?, ultimo_intento = NOW() WHERE email = ?");
                    $stmt_update->bind_param('is', $intentos, $usuario);
                    $stmt_update->execute();

                    if ($intentos >= 5) {
                        $stmt_block = $conn->prepare("UPDATE usuarios SET estado = 3 WHERE email = ?");
                        $stmt_block->bind_param('s', $usuario);
                        $stmt_block->execute();

                        $tipo_alerta = "error";
                        $alerta = "Usuario bloqueado tras múltiples intentos fallidos";
                    } else {
                        if ($intentos == 4) {
                            $tipo_alerta = "warning";
                            $alerta = "Acceso Denegado. Un intento más y su cuenta será bloqueada";
                        } else {
                            if ($intentos == 1) {
                                $tipo_alerta = "error";
                                $alerta = "Acceso Denegado.";
                            } else {
                                $tipo_alerta = "error";
                                $alerta = "Acceso Denegado. Intentos fallidos: $intentos";
                            }
                        }
                    }
                }
            }
        } else {
            $tipo_alerta = "error";
            $alerta = "Error, Usuario No Existente";
        }
    }
}

if (isset($_GET['error']) || isset($_GET['success'])) {
    if (isset($_GET['error'])) {
        if ($_GET['error'] == 1) {
            $tipo_alerta = "error";
            $alerta = "Permiso Denegado";
        } else if ($_GET['error'] == 2) {
            $tipo_alerta = "warning";
            $alerta = "Tu Sesion Expiro";
        }
    }

    if (isset($_GET['success'])) {
        if ($_GET['success'] == 2) {
            $tipo_alerta = "success";
            $alerta = "Usuario Creado";
        } else if ($_GET['success'] == 1) {
            $tipo_alerta = "success";
            $alerta = "Sesión Cerrada con éxito";
        }
    }
    echo "<script>window.history.replaceState(null, null, window.location.pathname);</script>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INVDrey - Inicio De Sesion</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/Styles.css">
    <link rel="stylesheet" href="styles/Sesion.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-gray-800 rounded-xl shadow-2xl overflow-hidden transition-all duration-300 hover:shadow-purple-500/20">
        <div class="p-8">
            <div class="text-center mb-8">
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-gray-700 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-white">Bienvenido</h2>
                <p class="text-gray-400 mt-2">Inicia sesión para continuar</p>
            </div>

            <form method="post" id="loginForm" class="space-y-6">
                <div>
                    <label for="usuario" class="block text-sm font-medium text-gray-300 mb-1">Correo electrónico</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input type="email" id="usuario" name="usuario" required 
                               class="input-field pl-10 w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
                               placeholder="tucorreo@ejemplo.com">
                    </div>
                </div>

                <div>
    <label for="pass" class="block text-sm font-medium text-gray-300 mb-1">Contraseña</label>
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>
        <input type="password" id="pass" name="pass" required 
               class="input-field pl-10 pr-10 w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
               placeholder="••••••••">
        <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" onclick="togglePassword('pass')">
            <svg id="eye-icon-pass" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 hover:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
        </button>
    </div>
</div>

                <div>
                    <button type="submit" name="btningresar" 
                            class="btn-primary w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        Iniciar sesión
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-400">
                    ¿No tienes una cuenta? 
                    <a href="registro.php" class="font-medium text-purple-400 hover:text-purple-300">Regístrate aquí</a>
                </p>
            </div>
        </div>
    </div>
<script>
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const eyeIcon = document.getElementById(`eye-icon-${inputId}`);
        
        if (input.type === "password") {
            input.type = "text";
            eyeIcon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
            `;
        } else {
            input.type = "password";
            eyeIcon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            `;
        }
    }
</script>
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
</body>
</html>