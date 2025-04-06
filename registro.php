<?php

session_start();

include('conexion.php');

$alerta = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST["nombre"]) || empty($_POST["usuario"]) || empty($_POST["pass"])) {
        $alerta = "Todos los campos son obligatorios.";
        echo "<script>window.history.replaceState(null, null, window.location.pathname);</script>";
    } else {
        $nombre = $conn->real_escape_string($_POST["nombre"]);
        $usuario = $conn->real_escape_string($_POST["usuario"]);
        $clave = $conn->real_escape_string($_POST["pass"]);
        
        // Validación de la contraseña
        if (!validar_contraseña($clave)) {
            $alerta = "La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un carácter especial.";
            echo "<script>window.history.replaceState(null, null, window.location.pathname);</script>";
        } else {
            // Verificar si el usuario ya existe
            $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email=?");
            $stmt->bind_param('s', $usuario);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $alerta = "El correo ya está registrado.";
                echo "<script>window.history.replaceState(null, null, window.location.pathname);</script>";
            } else {
                // Encriptar la contraseña
                $password_encriptada = password_hash($clave, PASSWORD_DEFAULT);

                // Insertar el nuevo usuario en la base de datos
                $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param('sss', $nombre, $usuario, $password_encriptada);

                if ($stmt->execute()) {
                    // Redirigir a la página de inicio de sesión después de un registro exitoso
                    header("location:index.php?success=2");
                    exit();
                } else {
                    $alerta = "Error al registrar el usuario. Intenta nuevamente.";
                    echo "<script>window.history.replaceState(null, null, window.location.pathname);</script>";
                }
            }
            $stmt->close();
        }
    }
}

// Función para validar la contraseña
function validar_contraseña($contraseña) {
    return preg_match('/^(?=.*[A-Z])(?=.*[0-9])(?=.*[\W_]).{8,}$/', $contraseña);
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
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

.register-container {
    background-color: rgba(255, 255, 255, 0.9);
    padding: 50px;
    border-radius: 15px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
    width: 400px; 
    transition: transform 0.3s ease, box-shadow 0.3s ease; 
}

.register-container:hover {
    transform: scale(1.1); 
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3); 
}

.form-label {
    font-size: 1.1em; 
}

.form-control {
    font-size: 1.1em; 
}

.register-btn {
    font-size: 1.2em; 
}

.login-link {
    margin-top: 15px;
    display: block;
    text-align: center;
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
    <div class="register-container">

        <form method="post" id="registerForm" action="">
            <div class="mb-4">
                <label for="nombre" class="form-label">Nombre</label>
                <input placeholder="Ingrese Su Nombre" type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="mb-4">
                <label for="usuario" class="form-label">Correo</label>
                <input placeholder="Ingrese Su Correo" type="email" class="form-control" id="usuario" name="usuario" required>
            </div>
            <div class="mb-4">
                <label for="pass" class="form-label">Contraseña</label>
                <input placeholder="Ingrese Su Contraseña" type="password" class="form-control" id="pass" name="pass" required>
            </div>
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
        }, 7000); // Tiempo antes de desvanecer
    </script>
<?php endif; ?>
            <button type="submit" name="btnregistrar" class="btn btn-primary w-100 register-btn">Registrarse</button>
        </form>
        <div class="login-link">
            <p>¿ya tienes una cuenta? <a href="index.php">Inicia sesion aquí</a></p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
