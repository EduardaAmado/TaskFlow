<?php
require_once '../models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Cria uma nova instância do modelo User
    $userModel = new User();

    // Tenta fazer login do usuário
    if ($userModel->login($email, $password)) {
        // Redireciona ou mostra mensagem de sucesso
        header('Location: ../../public/login.php?success=1');
        exit();
    } else {
        // Redireciona ou mostra mensagem de erro
        header('Location: ../../public/login.php?error=1');
        exit();
    }
}
?>
