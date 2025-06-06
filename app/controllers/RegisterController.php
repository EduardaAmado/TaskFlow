<?php
require_once '../models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Cria uma nova instância do modelo User
    $userModel = new User();

    // Tenta registrar o usuário
    if ($userModel->register($firstName, $lastName, $email, $password)) {
        // Redireciona ou mostra mensagem de sucesso
        header('Location: ../../public/register.php?success=1');
        exit();
    } else {
        // Redireciona ou mostra mensagem de erro
        header('Location: ../../public/register.php?error=1');
        exit();
    }
}
?>
