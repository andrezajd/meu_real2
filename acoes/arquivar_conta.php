<?php
// acoes/arquivar_conta.php
session_start();
require_once __DIR__ . '/../conexao.php'; // O ../ volta para a pasta anterior

if (!isset($_GET['id'])) {
    header("Location: ../index.php");
    exit();
}

$id = $conn->real_escape_string($_GET['id']);

// SQL que faz o "Soft Delete" (manda para a lixeira)
$sql = "UPDATE usuarios SET deletado = 1 WHERE id = '$id'";

if ($conn->query($sql) === TRUE) {
    // Se o próprio usuário se deletou, encerra a sessão dele
    if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == $id) {
        session_destroy();
        header("Location: ../login.php?msg=conta_desativada");
    } else {
        // Se foi um admin que deletou, volta para o painel de admin
        header("Location: ../index.php?msg=arquivado");
    }
} else {
    echo "Erro ao processar: " . $conn->error;
}