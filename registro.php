<?php
session_start();
include('conexion.php');

$alerta = "";
$tipo_alerta = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["nombre"]) || empty($_POST["usuario"]) || empty($_POST["pass"])) {
        $alerta = "Todos los campos son obligatorios.";
        $tipo_alerta = "error";
        echo "<script>window.history.replaceState(null, null, window.location.pathname);</script>";
    } else {
        $nombre = $conn->real_escape_string($_POST["nombre"]);
        $usuario = $conn->real_escape_string($_POST["usuario"]);
        $clave = $conn->real_escape_string($_POST["pass"]);
        
        if (!validar_contraseña($clave)) {
            $alerta = "La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un carácter especial.";
            $tipo_alerta = "error";
            echo "<script>window.history.replaceState(null, null, window.location.pathname);</script>";
        } else {
            $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email=?");
            $stmt->bind_param('s', $usuario);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $alerta = "El correo ya está registrado.";
                $tipo_alerta = "error";
                echo "<script>window.history.replaceState(null, null, window.location.pathname);</script>";
            } else {
                $password_encriptada = password_hash($clave, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param('sss', $nombre, $usuario, $password_encriptada);

                if ($stmt->execute()) {
                    header("location:index.php?success=2");
                    exit();
                } else {
                    $alerta = "Error al registrar el usuario. Intenta nuevamente.";
                    $tipo_alerta = "error";
                    echo "<script>window.history.replaceState(null, null, window.location.pathname);</script>";
                }
            }
            $stmt->close();
        }
    }
}

function validar_contraseña($contraseña) {
    return preg_match('/^(?=.*[A-Z])(?=.*[0-9])(?=.*[\W_]).{8,}$/', $contraseña);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INVDrey - Registro</title>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-white">Crear cuenta</h2>
                <p class="text-gray-400 mt-2">Completa el formulario para registrarte</p>
            </div>

            <form method="post" id="registerForm" class="space-y-6">
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-300 mb-1">Nombre completo</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input type="text" id="nombre" name="nombre" required 
                               class="input-field pl-10 w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
                               placeholder="Tu nombre completo">
                    </div>
                </div>

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
               placeholder="••••••••"
               oninput="checkPasswordStrength(this.value)">
        <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" onclick="togglePassword('pass')">
            <svg id="eye-icon-pass" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 hover:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
        </button>
    </div>
    <div class="password-strength">
        <div id="password-strength-fill" class="password-strength-fill"></div>
    </div>
    <p id="password-requirements" class="text-xs text-gray-400 mt-2">
        La contraseña debe tener al menos 8 caracteres, incluyendo una mayúscula, un número y un carácter especial.
    </p>
</div>

                <div>
                    <button type="submit" name="btnregistrar" 
                            class="btn-primary w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        Registrarse
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-400">
                    ¿Ya tienes una cuenta? 
                    <a href="index.php" class="font-medium text-purple-400 hover:text-purple-300">Inicia sesión aquí</a>
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

        function checkPasswordStrength(password) {
            const strengthFill = document.getElementById('password-strength-fill');
            let strength = 0;
            
            // validacion de la contraseña, letras, numeros y caracteres como tambien la longitud
            if (password.length >= 8) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[\W_]/.test(password)) strength += 1;
            
            // actualizacion de la fuerza de la contraseña
            const width = (strength / 4) * 100;
            strengthFill.style.width = `${width}%`;
            
            // Cambio del color basado en la contraseña
            if (strength <= 1) {
                strengthFill.style.backgroundColor = '#ef4444'; // red
            } else if (strength <= 2) {
                strengthFill.style.backgroundColor = '#f59e0b'; // amber
            } else if (strength <= 3) {
                strengthFill.style.backgroundColor = '#3b82f6'; // blue
            } else {
                strengthFill.style.backgroundColor = '#10b981'; // emerald
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
                timer: 5000,
                timerProgressBar: true
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>