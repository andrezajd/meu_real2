<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/conexao.php';

$id_usuario = $_SESSION['usuario_id'];
$nome_usuario = $_SESSION['usuario_nome'];

// Buscar limite do cartão
$sql = "SELECT limite_cartao, fechamento_cartao FROM usuarios WHERE id = '$id_usuario'";
$res = $conn->query($sql);
$usuario = $res->fetch_assoc();
$limiteCartao = $usuario ? (float)$usuario['limite_cartao'] : 0;
$fechamentoCartao = $usuario ? (int)$usuario['fechamento_cartao'] : 10;

// Buscar gastos do cartão por mês
$sql = "SELECT 
            DATE_FORMAT(data, '%Y-%m') as mes,
            SUM(valor) as total,
            COUNT(*) as qtd
        FROM transacoes 
        WHERE categoria = 'Cartão' 
        AND usuario_id = '$id_usuario'
        GROUP BY DATE_FORMAT(data, '%Y-%m')
        ORDER BY mes DESC
        LIMIT 6";
$res = $conn->query($sql);
$faturas = [];
while ($row = $res->fetch_assoc()) {
    $faturas[] = $row;
}

// Fatura atual (mês corrente)
$mesAtual = date('Y-m');
$sqlAtual = "SELECT SUM(valor) as total FROM transacoes 
             WHERE categoria = 'Cartão' 
             AND usuario_id = '$id_usuario'
             AND DATE_FORMAT(data, '%Y-%m') = '$mesAtual'";
$resAtual = $conn->query($sqlAtual);
$faturaAtual = $resAtual->fetch_assoc();
$totalFaturaAtual = $faturaAtual['total'] ? (float)$faturaAtual['total'] : 0;

$limiteDisponivel = max(0, $limiteCartao - $totalFaturaAtual);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Faturas - Meu Real</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/index.css">
   
       
</head>
<body>

<aside class="sidebar">
    <div class="brand">
        <i class="fas fa-piggy-bank"></i> Meu Real
    </div>
    <nav>
        <a href="index.php"><i class="fas fa-home"></i> Início</a>
        <a href="transacoes.php"><i class="fas fa-exchange-alt"></i> Transações</a>
        <a href="fatura.php" class="active"><i class="fas fa-credit-card"></i> Faturas</a>
        <a href="perfil.php"><i class="fas fa-user-gear"></i> Perfil</a>
        <?php if (isset($_SESSION['nivel_acesso']) && $_SESSION['nivel_acesso'] == 1): ?>
            <a href="admin_painel.php" class="btn-admin"><i class="fas fa-shield-halved"></i> PAINEL ADMIN</a>
        <?php endif; ?>
        <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </nav>
</aside>

<main class="main">
    <div class="fatura-container">
        <header class="header">
            <h1><i class="fas fa-credit-card"></i> Faturas do Cartão</h1>
            <span><?php echo date('d/m/Y'); ?></span>
        </header>

        <!-- Cards de informação -->
        <div class="info-cards">
            <div class="info-card">
                <h3><i class="fas fa-chart-line"></i> Limite Total</h3>
                <div class="valor">R$ <?php echo number_format($limiteCartao, 2, ',', '.'); ?></div>
            </div>
            <div class="info-card">
                <h3><i class="fas fa-credit-card"></i> Disponível</h3>
                <div class="valor">R$ <?php echo number_format($limiteDisponivel, 2, ',', '.'); ?></div>
            </div>
            <div class="info-card">
                <h3><i class="fas fa-calendar-alt"></i> Fechamento</h3>
                <div class="valor">Dia <?php echo $fechamentoCartao; ?></div>
            </div>
        </div>

        <!-- Fatura atual -->
        <div class="fatura-card">
            <div class="fatura-header">
                <span class="fatura-mes">
                    <i class="fas fa-calendar"></i> Fatura Atual - <?php echo date('F/Y'); ?>
                </span>
                <span class="status-pendente">PENDENTE</span>
            </div>
            <div class="fatura-total">
                R$ <?php echo number_format($totalFaturaAtual, 2, ',', '.'); ?>
            </div>
            <?php if ($totalFaturaAtual > 0): ?>
                <form method="POST" action="pagar_fatura.php" onsubmit="return confirm('Pagar fatura de R$ <?php echo number_format($totalFaturaAtual, 2, ',', '.'); ?>?')">
                    <input type="hidden" name="mes" value="<?php echo $mesAtual; ?>">
                    <button type="submit" class="btn-pagar">
                        <i class="fas fa-check-circle"></i> Pagar Fatura
                    </button>
                </form>
            <?php else: ?>
                <p style="color: #10b981; margin-top: 10px;">✓ Nenhum gasto no cartão este mês</p>
            <?php endif; ?>
        </div>

        <!-- Histórico de faturas -->
        <h3 style="margin: 30px 0 15px 0;"><i class="fas fa-history"></i> Histórico</h3>
        
        <?php if (count($faturas) > 0): ?>
            <?php foreach ($faturas as $fatura): ?>
                <div class="fatura-card">
                    <div class="fatura-header">
                        <span class="fatura-mes">
                            <i class="fas fa-calendar"></i> 
                            <?php echo date('F/Y', strtotime($fatura['mes'] . '-01')); ?>
                        </span>
                    </div>
                    <div class="fatura-total">
                        R$ <?php echo number_format($fatura['total'], 2, ',', '.'); ?>
                    </div>
                    <small><?php echo $fatura['qtd']; ?> transações</small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; color: #64748b;">Nenhuma fatura encontrada</p>
        <?php endif; ?>
    </div>
</main>

</body>
</html>