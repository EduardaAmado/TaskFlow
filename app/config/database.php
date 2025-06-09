<?php
$host = 'localhost'; // ou o endereço do seu servidor de banco de dados
$db = 'db_taskflow'; // nome do seu banco de dados
$user = 'root'; // seu usuário do banco de dados
$pass = ''; // sua senha do banco de dados

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database $db :" . $e->getMessage());
}
?>
