<?php
session_start();
require_once __DIR__ . '/conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$descricao = trim($_POST['descricao']);
$valor = (float) str_replace(',', '.', $_POST['valor']);
$categoria = $_POST['categoria'];
$data_raw = $_POST['data'];

// Converte data para formato MySQL
$data = str_replace('T', ' ', $data_raw) . ':00';

// ========== BUSCAR TOTAIS DO MÊS ==========
$sql_totais = "
    SELECT 
        SUM(CASE WHEN categoria = 'Saldo' THEN valor ELSE 0 END) as entradas,
        SUM(CASE WHEN categoria = 'Gastos' THEN valor ELSE 0 END) as gastos_debito,
        SUM(CASE WHEN categoria = 'Cartão' THEN valor ELSE 0 END) as gastos_cartao,
        SUM(CASE WHEN categoria = 'Meta' THEN valor ELSE 0 END) as reserva
    FROM transacoes
    WHERE usuario_id = ?
      AND MONTH(data) = MONTH(CURRENT_DATE())
      AND YEAR(data) = YEAR(CURRENT_DATE())
";
$stmt_totais = $conn->prepare($sql_totais);
$stmt_totais->bind_param("i", $usuario_id);
$stmt_totais->execute();
$totais = $stmt_totais->get_result()->fetch_assoc();
$stmt_totais->close();

$entradas      = (float) $totais['entradas'];
$gastosDebito  = (float) $totais['gastos_debito'];
$gastosCartao  = (float) $totais['gastos_cartao'];
$reserva       = (float) $totais['reserva'];

// Cálculo do saldo disponível para débito (dinheiro real na conta)
$saldoDebito = $entradas - $gastosDebito;
$disponivelDebito = $saldoDebito - $reserva;   // o que pode gastar à vista sem comprometer reserva

// ========== VALIDAÇÃO POR CATEGORIA ==========

// 1) Gastos à vista (Débito)
if ($categoria == 'Gastos') {
    if ($valor > $disponivelDebito) {
        $_SESSION['erro'] = "❌ Saldo insuficiente para gasto à vista. Disponível (após reserva): R$ " . number_format($disponivelDebito,2,',','.');
        header("Location: index.php");
        exit;
    }
}

// 2) Cartão de Crédito - valida apenas limite do cartão (não afeta saldo de débito)
if ($categoria == 'Cartão') {
    // Buscar limite do cartão do usuário
    $sql_limite = "SELECT limite_cartao FROM usuarios WHERE id = ?";
    $stmt_limite = $conn->prepare($sql_limite);
    $stmt_limite->bind_param("i", $usuario_id);
    $stmt_limite->execute();
    $limite = (float) $stmt_limite->get_result()->fetch_assoc()['limite_cartao'];
    $stmt_limite->close();
    
    // Considera apenas gastos pendentes (não pagos) – já que $gastosCartao na consulta acima considera apenas pendentes (ajustado depois)
    $novoTotalCartao = $gastosCartao + $valor;
    if ($limite > 0 && $novoTotalCartao > $limite) {
        $disponivel = $limite - $gastosCartao;
        $_SESSION['erro'] = "❌ Limite do cartão excedido. Disponível: R$ " . number_format($disponivel,2,',','.');
        header("Location: index.php");
        exit;
    }
}

// 3) Entradas (Saldo) e Meta (Reserva) - sempre permitem, sem validação extra

// ========== INSERIR TRANSAÇÃO ==========
$sql_insert = "INSERT INTO transacoes (usuario_id, descricao, valor, categoria, data, status) VALUES (?, ?, ?, ?, ?, 'pendente')";
$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param("isdss", $usuario_id, $descricao, $valor, $categoria, $data);

if ($stmt_insert->execute()) {
    $_SESSION['sucesso'] = "✅ Transação salva com sucesso!";
} else {
    $_SESSION['erro'] = "❌ Erro ao salvar: " . $stmt_insert->error;
}
$stmt_insert->close();

header("Location: index.php");
exit;
?>