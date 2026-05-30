<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/conexao.php';

$id_usuario = $_SESSION['usuario_id'];
$mes = $_POST['mes'];

// Marca as transações como pagas (adiciona data de pagamento)
$sql = "UPDATE transacoes 
        SET data_pagamento = NOW() 
        WHERE categoria = 'Cartão' 
        AND usuario_id = '$id_usuario'
        AND DATE_FORMAT(data, '%Y-%m') = '$mes'
        AND data_pagamento IS NULL";
$conn->query($sql);

$_SESSION['sucesso'] = "Fatura paga com sucesso!";
header("Location: fatura.php");
exit();
?>