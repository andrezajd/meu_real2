<?php

$host = "127.0.0.1"; // Pode usar localhost também
$usuario = "root";
$senha = "";     // VOCÊ PRECISA DESSA SENHA AQUI
$banco = "controle_de_gastos";


// Criando a conexão usando o estilo que o seu index.php espera
$conn = new mysqli($host, $usuario, $senha, $banco);

// Verificando a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Ajuste para caracteres especiais (acentos)
$conn->set_charset("utf8mb4");
?>