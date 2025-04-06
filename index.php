<?php
session_start();
include('conexion.php');

$alerta = "";
$tipo_alerta = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST["usuario"]) || empty($_POST["pass"])) {
        $alerta = "Los Campos están vacíos";
    } else {
        $usuario = $conn->real_escape_string($_POST["usuario"]);
        $clave = $conn->real_escape_string($_POST["pass"]);

        // Consulta para obtener la información del usuario, incluyendo estado, intentos de login y último intento
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

            // Verificar si han pasado 10 minutos desde el último intento fallido para reiniciar los intentos
            $tiempo_actual = time();
            $tiempo_ultimo_intento = strtotime($ultimo_intento);

            if ($ultimo_intento && ($tiempo_actual - $tiempo_ultimo_intento) >= 600) { 
                // Reiniciar intentos a 0
                $intentos = 0;
                $stmt_reset_intentos = $conn->prepare("UPDATE usuarios SET intentosLogin = 0 WHERE email = ?");
                $stmt_reset_intentos->bind_param('s', $usuario);
                $stmt_reset_intentos->execute();
            }

            // Verificar si el usuario está bloqueado
            if ($estado == 3) {
                $tipo_alerta = "danger";
                $alerta = "Usuario bloqueado, Solicite desbloqueo con un administrador";
            } else {
                // Verificar si la contraseña ingresada coincide con el hash
                if (password_verify($clave, $password_encriptada)) {
                    // Restablecer los intentos fallidos a 0 después de un inicio de sesión exitoso
                    $stmt_reset = $conn->prepare("UPDATE usuarios SET intentosLogin = 0, ultimo_intento = NULL WHERE email = ?");
                    $stmt_reset->bind_param('s', $usuario);
                    $stmt_reset->execute();

                    // La contraseña es correcta
                    $_SESSION['usuario'] = $datos->nombre;
                    $_SESSION['id_rol'] = $datos->id_rol;
                    header("location:inicio.php"); // Redirige a la página de inicio
                    exit();
                } else {
                    // Incrementar el contador de intentos fallidos y registrar el último intento
                    $intentos++;
                    $stmt_update = $conn->prepare("UPDATE usuarios SET intentosLogin = ?, ultimo_intento = NOW() WHERE email = ?");
                    $stmt_update->bind_param('is', $intentos, $usuario);
                    $stmt_update->execute();

                    if ($intentos >= 5) {
                        // Bloquear el usuario si supera los 5 intentos fallidos
                        $stmt_block = $conn->prepare("UPDATE usuarios SET estado = 3 WHERE email = ?");
                        $stmt_block->bind_param('s', $usuario);
                        $stmt_block->execute();

                        $tipo_alerta = "danger";
                        $alerta = "Usuario bloqueado tras múltiples intentos fallidos";
                    } else {
                        // Mostrar advertencias progresivas
                        if ($intentos == 4) {
                            $tipo_alerta = "warning";
                            $alerta = "Acceso Denegado. Un intento más y su cuenta será bloqueada";
                        } else {
                            if ($intentos == 1) {
                                $tipo_alerta = "danger";
                                $alerta = "Acceso Denegado.";
                            } else {
                                $tipo_alerta = "danger";
                                $alerta = "Acceso Denegado. Intentos fallidos: $intentos";
                            }
                        }
                    }
                }
            }
        } else {
            // No se encontró el usuario
            $tipo_alerta = "danger";
            $alerta = "Error, Usuario No Existente";
        }
    }
}

// Manejo de mensajes de error y éxito
if (isset($_GET['error']) || isset($_GET['success'])) {
    if (isset($_GET['error'])) {
        if ($_GET['error'] == 1) {
            $tipo_alerta = "danger";
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
    <title>Login</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('Imagenes/fondo_login.png');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 50px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
            width: 400px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-container:hover {
            transform: scale(1.1);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }

        .user-image {
            display: block;
            margin: 0 auto 20px;
            width: 120px;
        }

        .form-label {
            font-size: 1.1em;
        }

        .form-control {
            font-size: 1.1em;
        }

        .ingresar-btn {
            font-size: 1.2em;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
        }

        #alerta {
            transition: opacity 0.6s ease;
        }

        #alerta.fade-out {
            opacity: 0;
            visibility: hidden;
        }
    </style>
</head>

<body>

    <div class="login-container">


        <img src="Imagenes/usu.png" alt="Usuario" class="user-image">

        <?php if ($alerta): ?>
            <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show" role="alert" id="alerta">
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

        <form method="post" id="loginForm" action="">
            <div class="mb-4">
                <label for="usuario" class="form-label">Usuario</label>
                <input placeholder="Ingrese Su Correo" type="email" class="form-control" id="usuario" name="usuario" required>
            </div>
            <div class="mb-4">
                <label for="pass" class="form-label">Contraseña</label>
                <input placeholder="Ingrese Su Contraseña" type="password" class="form-control" id="pass" name="pass" required>
            </div>
            <button type="submit" name="btningresar" class="btn btn-primary w-100 ingresar-btn">Ingresar</button>
        </form>

        <div class="register-link">
            <p>¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a></p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>