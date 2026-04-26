<?php
include('../conexao.php');

$id = $_GET['id'];

// Ao invés de apagar, apenas marcamos como deletado
$sql = "UPDATE usuarios SET deletado = 1 WHERE id = '$id'";

if ($conn->query($sql) === TRUE) {
    header("Location: index.php?msg=arquivado");
} else {
    echo "Erro ao arquivar: " . $conn->error;
}
?>