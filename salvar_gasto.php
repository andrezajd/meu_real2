<?php
session_start();
// include('conexao.php');
require_once __DIR__ . '/conexao.php';

// Limpa tudo que vem do formulário e da sessão
$id_usuario = $conn->real_escape_string($_SESSION['usuario_id']);
$descricao  = $conn->real_escape_string($_POST['descricao']);
$valor      = $conn->real_escape_string($_POST['valor']);
$categoria  = $conn->real_escape_string($_POST['categoria']);
$data_hoje  = date('Y-m-d');

$sql = "INSERT INTO transacoes (usuario_id, descricao, valor, categoria, data) 
        VALUES ('$id_usuario', '$descricao', '$valor', '$categoria', '$data_hoje')";

if ($conn->query($sql)) {
    header("Location: index.php?sucesso=1");
} else {
    echo "Erro ao salvar: " . $conn->error;
}
