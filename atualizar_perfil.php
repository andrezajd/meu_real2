<?php
session_start();
// include('conexao.php');
require_once __DIR__ . '/conexao.php';

$id = $_SESSION['usuario_id'];
$novo_nome = $_POST['nome'];
$nova_senha = $_POST['nova_senha'];

if (!empty($nova_senha)) {
    // Se digitou senha, atualiza nome e senha
    $sql = "UPDATE usuarios SET nome = '$novo_nome', senha = '$nova_senha' WHERE id = '$id'";
} else {
    // Se não digitou senha, atualiza só o nome
    $sql = "UPDATE usuarios SET nome = '$novo_nome' WHERE id = '$id'";
}

if ($conn->query($sql)) {
    $_SESSION['usuario_nome'] = $novo_nome; // Atualiza o nome na sessão também!
    header("Location: perfil.php?sucesso=1");
}
