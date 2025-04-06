<?php
session_start(); 

$_SESSION = array(); 

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

if (isset($_GET['error'])) {
    if ($_GET['error'] == 2) {

        session_destroy();
        header("Location: index.php?error=2"); 
        exit();
    } else {
        session_destroy();
        header("Location: index.php?error=1"); 
        exit();
    }
} else {
    if (isset($_GET['success'])) {
        session_destroy();
        header("Location: index.php?success=1");
        exit();
    } else {
        session_destroy();
        header("Location: index.php");
        exit();
    }
}
?>
