<?php
header('Content-Type: application/javascript');
// include('conexao.php');
require_once __DIR__ . '/conexao.php';

// Busca os valores somados por categoria
$categorias = ['Gastos', 'Saldo', 'Cofrinho', 'Cartão'];
$valores = [];

foreach ($categorias as $cat) {
    $res = $conn->query("SELECT SUM(valor) as total FROM transacoes WHERE categoria = '$cat'");
    $row = $res->fetch_assoc();
    $valores[] = $row['total'] ? $row['total'] : 0;
}

$dados_js = implode(',', $valores);
