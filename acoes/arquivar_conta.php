<?php
session_start();
require_once __DIR__ . '/../conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel_acesso'] != 1) {
    header("Location: ../index.php?erro=acesso_negado");
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    // Não pode arquivar a própria conta
    if ($id == $_SESSION['usuario_id']) {
        header("Location: ../admin_painel.php?erro=nao_arquivar_proprio");
        exit();
    }
    $sql = "UPDATE usuarios SET deletado = 1, data_arquivamento = NOW() WHERE id = $id AND deletado = 0";
    if ($conn->query($sql)) {
        header("Location: ../admin_painel.php?msg=arquivado");
        exit();
    } else {
        echo "Erro ao arquivar: " . $conn->error;
    }
} else {
    header("Location: ../admin_painel.php");
    exit();
}
?>