<?php
session_start();
require_once __DIR__ . '/../conexao.php';

// Apenas admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel_acesso'] != 1) {
    header("Location: ../index.php?erro=acesso_negado");
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Verifica se o usuário está realmente na lixeira (deletado = 1)
    $check = $conn->query("SELECT deletado FROM usuarios WHERE id = $id");
    if ($check && $check->num_rows > 0) {
        $row = $check->fetch_assoc();
        if ($row['deletado'] == 1) {
            // Primeiro, remove todas as transações do usuário (opcional, mas evita órfãos)
            $conn->query("DELETE FROM transacoes WHERE usuario_id = $id");
            // Depois, exclui o usuário permanentemente
            $sql = "DELETE FROM usuarios WHERE id = $id";
            if ($conn->query($sql)) {
                header("Location: ../lixeira.php?msg=excluido");
                exit();
            } else {
                echo "Erro ao excluir: " . $conn->error;
            }
        } else {
            // Usuário não está na lixeira
            header("Location: ../lixeira.php?erro=nao_na_lixeira");
            exit();
        }
    } else {
        header("Location: ../lixeira.php?erro=usuario_nao_encontrado");
        exit();
    }
} else {
    header("Location: ../lixeira.php");
    exit();
}
?>