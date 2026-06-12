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

// ========== BUSCAR DADOS DO CARTÃO DO USUÁRIO ==========
$sql_dados_cartao = "SELECT limite_cartao, dia_fechamento, dia_vencimento FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql_dados_cartao);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$dados_cartao = $stmt->get_result()->fetch_assoc();
$stmt->close();

$limite_cartao = (float) ($dados_cartao['limite_cartao'] ?? 0);
$dia_fechamento = (int) ($dados_cartao['dia_fechamento'] ?? 10);
$dia_vencimento = (int) ($dados_cartao['dia_vencimento'] ?? 5);

// ========== FATURA ATUAL (apenas transações pendentes) ==========
$mesAtual = date('Y-m');
$sqlAtual = "SELECT SUM(valor) as total FROM transacoes 
             WHERE categoria = 'Cartão' 
             AND usuario_id = ? 
             AND DATE_FORMAT(data, '%Y-%m') = ?
             AND (status IS NULL OR status = 'pendente')";
$stmt = $conn->prepare($sqlAtual);
$stmt->bind_param("is", $id_usuario, $mesAtual);
$stmt->execute();
$faturaAtual = $stmt->get_result()->fetch_assoc();
$stmt->close();
$totalFaturaAtual = (float) ($faturaAtual['total'] ?? 0);

$limiteDisponivel = max(0, $limite_cartao - $totalFaturaAtual);

// ========== HISTÓRICO DE FATURAS PENDENTES (meses anteriores) ==========
$sqlHistorico = "SELECT 
                    DATE_FORMAT(data, '%Y-%m') as mes,
                    SUM(valor) as total,
                    COUNT(*) as qtd
                FROM transacoes 
                WHERE categoria = 'Cartão' 
                AND usuario_id = ?
                AND (status IS NULL OR status = 'pendente')
                GROUP BY DATE_FORMAT(data, '%Y-%m')
                ORDER BY mes DESC
                LIMIT 6";
$stmt = $conn->prepare($sqlHistorico);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$faturas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
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
    <div class="brand"><i class="fas fa-piggy-bank"></i> Meu Real</div>
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

        <div class="info-cards">
            <div class="info-card">
                <h3><i class="fas fa-chart-line"></i> Limite Total</h3>
                <div class="valor">R$ <?php echo number_format($limite_cartao, 2, ',', '.'); ?></div>
            </div>
            <div class="info-card">
                <h3><i class="fas fa-credit-card"></i> Disponível</h3>
                <div class="valor">R$ <?php echo number_format($limiteDisponivel, 2, ',', '.'); ?></div>
            </div>
            <div class="info-card">
                <h3><i class="fas fa-calendar-alt"></i> Fechamento</h3>
                <div class="valor">Dia <?php echo $dia_fechamento; ?></div>
            </div>
            <div class="info-card">
                <h3><i class="fas fa-calendar-check"></i> Vencimento</h3>
                <div class="valor">Dia <?php echo $dia_vencimento; ?></div>
            </div>
        </div>

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
                    <button type="submit" class="btn-pagar"><i class="fas fa-check-circle"></i> Pagar Fatura</button>
                </form>
            <?php else: ?>
                <p style="color: #10b981; margin-top: 10px;">✓ Nenhum gasto pendente no cartão este mês</p>
            <?php endif; ?>
        </div>

        <h3 style="margin: 30px 0 15px 0;"><i class="fas fa-history"></i> Faturas Pendentes (Meses Anteriores)</h3>
        <?php if (count($faturas) > 0): ?>
            <?php foreach ($faturas as $fatura): 
                if ($fatura['mes'] == $mesAtual) continue;
            ?>
                <div class="fatura-card">
                    <div class="fatura-header">
                        <span class="fatura-mes"><i class="fas fa-calendar"></i> <?php echo date('F/Y', strtotime($fatura['mes'] . '-01')); ?></span>
                        <span class="status-pendente">PENDENTE</span>
                    </div>
                    <div class="fatura-total">R$ <?php echo number_format($fatura['total'], 2, ',', '.'); ?></div>
                    <form method="POST" action="pagar_fatura.php" style="margin-top: 10px;" onsubmit="return confirm('Pagar fatura de R$ <?php echo number_format($fatura['total'], 2, ',', '.'); ?>?')">
                        <input type="hidden" name="mes" value="<?php echo $fatura['mes']; ?>">
                        <button type="submit" class="btn-pagar" style="background: #2563eb;"><i class="fas fa-check-circle"></i> Pagar esta fatura</button>
                    </form>
                    <small><?php echo $fatura['qtd']; ?> transações</small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; color: #64748b;">Nenhuma fatura pendente de meses anteriores</p>
        <?php endif; ?>
    </div>
</main>

<style>
    .info-cards { display: flex; gap: 20px; margin-bottom: 30px; flex-wrap: wrap; }
    .info-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); flex: 1; min-width: 150px; text-align: center; }
    .info-card h3 { font-size: 14px; color: #64748b; margin-bottom: 10px; }
    .info-card .valor { font-size: 24px; font-weight: bold; color: #1e293b; }
    .fatura-card { background: #fff; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .fatura-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
    .fatura-mes { font-weight: bold; color: #1e293b; }
    .status-pendente { background: #f59e0b; color: #fff; padding: 4px 12px; border-radius: 20px; font-size: 12px; }
    .fatura-total { font-size: 28px; font-weight: bold; color: #ef4444; margin: 15px 0; }
    .btn-pagar { background: #10b981; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: bold; }
    .btn-pagar:hover { background: #059669; }
</style>

<script>
    setTimeout(() => {
        document.querySelectorAll('.toast-success, .toast-error').forEach(el => {
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 300);
        });
    }, 4000);
</script>
</body>
</html>