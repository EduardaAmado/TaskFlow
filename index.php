<?php
session_start();

// Verifica se o usuário está logado.
if (isset($_SESSION['user_id'])) {
    // Se estiver logado, redireciona para o dashboard
    header("Location: /TaskFlow/public/dashboard.php");
    exit;
} else {
    // Se não estiver logado, redireciona para a página de login
    header("Location: /TaskFlow/public/asse/login.php");
    exit;
}
?>
