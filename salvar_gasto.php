<?php
session_start();
require_once __DIR__ . '/conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$descricao = $_POST['descricao'];
$valor = (float) str_replace(',', '.', $_POST['valor']);
$categoria = $_POST['categoria'];
$data = $_POST['data'];

// ========== VERIFICAÇÃO DO CARTÃO ==========
if ($categoria == 'Cartão') {
    $mesAtual = date('Y-m');
    
    // Buscar limite do usuário
    $sql_limite = "SELECT limite_cartao FROM usuarios WHERE id = $usuario_id";
    $res_limite = $conn->query($sql_limite);
    $limite = $res_limite->fetch_assoc();
    $limite_cartao = (float)($limite['limite_cartao'] ?? 0);
    
    // Buscar gastos já feitos no cartão neste mês
    $sql_gastos = "SELECT SUM(valor) as total 
                   FROM transacoes 
                   WHERE categoria = 'Cartão' 
                   AND usuario_id = $usuario_id
                   AND DATE_FORMAT(data, '%Y-%m') = '$mesAtual'";
    $res_gastos = $conn->query($sql_gastos);
    $gastos = $res_gastos->fetch_assoc();
    $total_gasto = (float)($gastos['total'] ?? 0);
    
    // Calcular novo total
    $novo_total = $total_gasto + $valor;
    
    // VERIFICAR SE VAI ESTOURAR O LIMITE
    if ($novo_total > $limite_cartao && $limite_cartao > 0) {
        $disponivel = $limite_cartao - $total_gasto;
        $_SESSION['erro'] = "❌ NÃO FOI POSSÍVEL SALVAR!<br>
                             Limite do cartão: R$ " . number_format($limite_cartao, 2, ',', '.') . "<br>
                             Gasto atual: R$ " . number_format($total_gasto, 2, ',', '.') . "<br>
                             Você tentou adicionar: R$ " . number_format($valor, 2, ',', '.') . "<br>
                             Disponível: R$ " . number_format($disponivel, 2, ',', '.');
        
        header("Location: index.php");
        exit(); // PARA A EXECUÇÃO AQUI - NÃO SALVA
    }
}

// ========== SE CHEGOU AQUI, PODE SALVAR ==========
$sql = "INSERT INTO transacoes (usuario_id, descricao, valor, categoria, data) 
        VALUES ('$usuario_id', '$descricao', '$valor', '$categoria', '$data')";

if ($conn->query($sql)) {
    $_SESSION['sucesso'] = "✅ Transação salva!";
} else {
    $_SESSION['erro'] = "❌ Erro: " . $conn->error;
}

header("Location: index.php");
exit();
?>