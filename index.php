<?php
session_start();

// Verifica se o usuário está logado.
// Ajuste 'user_id' para a chave de sessão que você usa ao fazer login.
if (isset($_SESSION['user_id'])) {
    // Se estiver logado, redireciona para o dashboard
    header("Location: /TaskFlow/frontend/dashboard.php");
    exit;
} else {
    // Se não estiver logado, redireciona para a página de login
    header("Location: /TaskFlow/frontend/login.php");
    exit;
}
?>
