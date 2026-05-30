<?php
session_start();
require_once __DIR__ . '/../conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel_acesso'] != 1) {
    header("Location: ../index.php?erro=acesso_negado");
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    // Restaura apenas se estiver deletado = 1
    $sql = "UPDATE usuarios SET deletado = 0, data_arquivamento = NULL WHERE id = $id AND deletado = 1";
    if ($conn->query($sql)) {
        header("Location: ../lixeira.php?msg=restaurado");
        exit();
    } else {
        echo "Erro ao restaurar: " . $conn->error;
    }
} else {
    header("Location: ../lixeira.php");
    exit();
}
?>