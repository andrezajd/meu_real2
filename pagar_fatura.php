<?php
session_start();
require_once __DIR__ . '/conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$mes = $_POST['mes'] ?? '';

if (empty($mes)) {
    $_SESSION['erro'] = "Mês da fatura não informado.";
    header("Location: fatura.php");
    exit();
}

// ========== 1) BUSCAR TOTAL DA FATURA PENDENTE ==========
$sql_fatura = "SELECT SUM(valor) as total 
               FROM transacoes 
               WHERE categoria = 'Cartão' 
               AND usuario_id = ? 
               AND DATE_FORMAT(data, '%Y-%m') = ?
               AND (status IS NULL OR status = 'pendente')";
$stmt = $conn->prepare($sql_fatura);
$stmt->bind_param("is", $usuario_id, $mes);
$stmt->execute();
$result = $stmt->get_result();
$fatura = $result->fetch_assoc();
$totalFatura = (float)($fatura['total'] ?? 0);
$stmt->close();

if ($totalFatura <= 0) {
    $_SESSION['erro'] = "Não há fatura pendente para este mês.";
    header("Location: fatura.php");
    exit();
}

// ========== 2) VERIFICAR SALDO DISPONÍVEL PARA DÉBITO ==========
$sql_totais = "
    SELECT 
        SUM(CASE WHEN categoria = 'Saldo' THEN valor ELSE 0 END) as entradas,
        SUM(CASE WHEN categoria = 'Gastos' THEN valor ELSE 0 END) as gastos_debito,
        SUM(CASE WHEN categoria = 'Meta' THEN valor ELSE 0 END) as reserva
    FROM transacoes
    WHERE usuario_id = ?
    AND MONTH(data) = MONTH(CURRENT_DATE())
    AND YEAR(data) = YEAR(CURRENT_DATE())
";
$stmt = $conn->prepare($sql_totais);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$totais = $stmt->get_result()->fetch_assoc();
$stmt->close();

$entradas = (float)($totais['entradas'] ?? 0);
$gastosDebito = (float)($totais['gastos_debito'] ?? 0);
$reserva = (float)($totais['reserva'] ?? 0);

$saldoDebito = $entradas - $gastosDebito;
$disponivelDebito = $saldoDebito - $reserva;

if ($totalFatura > $disponivelDebito) {
    $_SESSION['erro'] = "❌ Saldo insuficiente. Disponível em débito: R$ " . number_format($disponivelDebito,2,',','.');
    header("Location: fatura.php");
    exit();
}

// ========== 3) MARCAR TRANSAÇÕES COMO PAGAS ==========
$sql_update = "UPDATE transacoes 
               SET status = 'pago' 
               WHERE categoria = 'Cartão' 
               AND usuario_id = ? 
               AND DATE_FORMAT(data, '%Y-%m') = ?
               AND (status IS NULL OR status = 'pendente')";
$stmt = $conn->prepare($sql_update);
$stmt->bind_param("is", $usuario_id, $mes);
if (!$stmt->execute()) {
    $_SESSION['erro'] = "Erro ao marcar transações como pagas: " . $stmt->error;
    header("Location: fatura.php");
    exit();
}
$stmt->close();

// ========== 4) REGISTRAR PAGAMENTO COMO GASTO À VISTA ==========
$descricao = "Pagamento fatura cartão - " . date('F/Y', strtotime($mes . '-01'));
$dataAtual = date('Y-m-d H:i:s');

$sql_insert = "INSERT INTO transacoes (usuario_id, descricao, valor, categoria, data, status) 
               VALUES (?, ?, ?, 'Gastos', ?, 'pendente')";
$stmt = $conn->prepare($sql_insert);
$stmt->bind_param("isds", $usuario_id, $descricao, $totalFatura, $dataAtual);
if (!$stmt->execute()) {
    $_SESSION['erro'] = "Erro ao registrar pagamento: " . $stmt->error;
    header("Location: fatura.php");
    exit();
}
$stmt->close();

$_SESSION['sucesso'] = "✅ Fatura de R$ " . number_format($totalFatura,2,',','.') . " paga com sucesso!";

header("Location: fatura.php");
exit();
?>