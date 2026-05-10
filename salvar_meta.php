<?php
session_start();
include 'conexao.php'; // Usa o teu ficheiro de conexão atual

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $id_usuario = $_SESSION['user_id']; // Pega o ID de quem está logado
    $salario = $_POST['salario'];
    $meta = $_POST['meta'];
    $mes = date('M / Y'); // Regista o mês atual automaticamente

    $sql = "INSERT INTO metas (id_usuario, salario_recebido, valor_meta, mes_referencia) 
            VALUES ('$id_usuario', '$salario', '$meta', '$mes')";

    if (mysqli_query($conn, $sql)) {
        header("Location: dashboard.php?sucesso=meta_definida");
    } else {
        echo "Erro ao integrar: " . mysqli_error($conn);
    }
}
