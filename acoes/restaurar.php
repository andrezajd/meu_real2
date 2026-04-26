<?php
include('../conexao.php');

$id = $_GET['id'];

// Voltamos o status para 0
$sql = "UPDATE usuarios SET deletado = 0 WHERE id = '$id'";

if ($conn->query($sql) === TRUE) {
    header("Location: index.php?msg=restaurado");
} else {
    echo "Erro ao restaurar: " . $conn->error;
}
?>