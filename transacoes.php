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

// Processar exclusão
if (isset($_GET['excluir']) && is_numeric($_GET['excluir'])) {
    $id_excluir = $_GET['excluir'];
    
    $stmt = $conn->prepare("DELETE FROM transacoes WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $id_excluir, $id_usuario);
    
    if ($stmt->execute()) {
        $_SESSION['sucesso'] = "✅ Transação excluída com sucesso!";
    } else {
        $_SESSION['erro'] = "❌ Erro ao excluir transação.";
    }
    $stmt->close();
    
    header("Location: transacoes.php");
    exit();
}

// Buscar totais do mês
$stmt = $conn->prepare("
    SELECT 
        SUM(CASE WHEN categoria = 'Saldo' THEN valor ELSE 0 END) as total_entradas,
        SUM(CASE WHEN categoria = 'Gastos' THEN valor ELSE 0 END) as total_gastos,
        SUM(CASE WHEN categoria = 'Cartão' AND (status IS NULL OR status != 'pago') THEN valor ELSE 0 END) as total_cartao,
        SUM(CASE WHEN categoria = 'Meta' THEN valor ELSE 0 END) as total_meta
    FROM transacoes
    WHERE usuario_id = ?
    AND MONTH(data) = MONTH(CURRENT_DATE())
    AND YEAR(data) = YEAR(CURRENT_DATE())
");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$totais = $stmt->get_result()->fetch_assoc();
$stmt->close();

$totalEntradas = (float)($totais['total_entradas'] ?? 0);
$totalGastos = (float)($totais['total_gastos'] ?? 0);
$totalCartao = (float)($totais['total_cartao'] ?? 0);
$totalMeta = (float)($totais['total_meta'] ?? 0);

// ========== CÁLCULO UNIFICADO (igual ao index.php) ==========
$saldoBruto = $totalEntradas - ($totalGastos + $totalCartao);
$disponivelLivre = $saldoBruto - $totalMeta;

// Buscar limite do cartão
$stmt = $conn->prepare("SELECT limite_cartao FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$limite = $stmt->get_result()->fetch_assoc();
$limiteCartao = (float)($limite['limite_cartao'] ?? 0);
$stmt->close();

$limiteDisponivel = max(0, $limiteCartao - $totalCartao);

// Buscar todas as transações do mês
$stmt = $conn->prepare("
    SELECT id, data, descricao, categoria, valor, status, fatura_mes
    FROM transacoes
    WHERE usuario_id = ?
    AND MONTH(data) = MONTH(CURRENT_DATE())
    AND YEAR(data) = YEAR(CURRENT_DATE())
    ORDER BY data DESC, id DESC
");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$transacoes = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transações - Meu Real</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>

<aside class="sidebar">
    <div class="brand">
        <i class="fas fa-piggy-bank fa-2x bg-icon"></i> Meu Real
    </div>
    <nav>
        <a href="index.php"><i class="fas fa-home"></i> Início</a>
        <a href="transacoes.php" class="active"><i class="fas fa-exchange-alt"></i> Transações</a>
        <a href="fatura.php"><i class="fas fa-credit-card"></i> Faturas</a>
        <a href="perfil.php"><i class="fas fa-user-gear"></i> Perfil</a>
        <?php if (isset($_SESSION['nivel_acesso']) && $_SESSION['nivel_acesso'] == 1): ?>
            <a href="admin_painel.php" class="btn-admin"><i class="fas fa-shield-halved"></i> PAINEL ADMIN</a>
        <?php endif; ?>
        <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </nav>
</aside>

<main class="main">
    <header class="header">
        <h1><i class="fas fa-exchange-alt"></i> Transações</h1>
        <span><?php echo date('d/m/Y'); ?></span>
    </header>

    <?php
    if (isset($_SESSION['sucesso'])) {
        echo '<div style="background:#10b981; color:white; padding:12px; border-radius:8px; margin-bottom:20px;">
                <i class="fas fa-check-circle"></i> ' . $_SESSION['sucesso'] . '
              </div>';
        unset($_SESSION['sucesso']);
    }
    if (isset($_SESSION['erro'])) {
        echo '<div style="background:#ef4444; color:white; padding:12px; border-radius:8px; margin-bottom:20px;">
                <i class="fas fa-exclamation-triangle"></i> ' . $_SESSION['erro'] . '
              </div>';
        unset($_SESSION['erro']);
    }
    ?>

    <!-- Cards de resumo -->
    <div class="resumo-card">
        <h3 style="margin: 0 0 10px 0;"><i class="fas fa-chart-line"></i> Resumo do Mês</h3>
        <div class="resumo-grid">
            <div class="resumo-item">
                <div class="label"><i class="fas fa-arrow-down"></i> Entradas</div>
                <div class="valor">R$ <?php echo number_format($totalEntradas, 2, ',', '.'); ?></div>
            </div>
            <div class="resumo-item">
                <div class="label"><i class="fas fa-arrow-up"></i> Gastos</div>
                <div class="valor">R$ <?php echo number_format($totalGastos, 2, ',', '.'); ?></div>
            </div>
            <div class="resumo-item">
                <div class="label"><i class="fas fa-credit-card"></i> Cartão</div>
                <div class="valor">R$ <?php echo number_format($totalCartao, 2, ',', '.'); ?></div>
            </div>
            <!-- SALDO BRUTO TOTAL (adicional) -->
            <div class="resumo-item">
                <div class="label"><i class="fas fa-chart-simple"></i> Saldo bruto total</div>
                <div class="valor">R$ <?php echo number_format($saldoBruto, 2, ',', '.'); ?></div>
            </div>
            <!-- DISPONÍVEL LIVRE (igual ao dashboard) -->
            <div class="resumo-item">
                <div class="label"><i class="fas fa-piggy-bank"></i> Disponível (Livre)</div>
                <div class="valor">R$ <?php echo number_format($disponivelLivre, 2, ',', '.'); ?></div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filters-bar">
        <div class="filter-group">
            <label><i class="fas fa-filter"></i> Categoria:</label>
            <select id="filtroCategoria" onchange="filtrarTabela()">
                <option value="todas">Todas</option>
                <option value="Saldo">Entradas</option>
                <option value="Gastos">Gastos</option>
                <option value="Cartão">Cartão</option>
                <option value="Meta">Meta/Reserva</option>
            </select>
        </div>
        <div class="filter-group">
            <label><i class="fas fa-search"></i> Buscar:</label>
            <input type="text" id="filtroBusca" placeholder="Descrição..." onkeyup="filtrarTabela()">
        </div>
    </div>

    <!-- Tabela de transações -->
    <div class="card-tabela">
        <h3 style="margin-bottom: 20px;"><i class="fas fa-list-ul"></i> Histórico de Transações</h3>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 12px; text-align: left;">Data</th>
                        <th style="padding: 12px; text-align: left;">Descrição</th>
                        <th style="padding: 12px; text-align: left;">Categoria</th>
                        <th style="padding: 12px; text-align: right;">Valor</th>
                        <th style="padding: 12px; text-align: center;">Ações</th>
                    </tr>
                </thead>
                <tbody id="tabelaTransacoes">
                    <?php if ($transacoes && $transacoes->num_rows > 0): ?>
                        <?php while ($row = $transacoes->fetch_assoc()): 
                            $classeBadge = '';
                            switch($row['categoria']) {
                                case 'Saldo': $classeBadge = 'badge-saldo'; break;
                                case 'Gastos': $classeBadge = 'badge-gasto'; break;
                                case 'Cartão': $classeBadge = 'badge-cartao'; break;
                                case 'Meta': $classeBadge = 'badge-meta'; break;
                            }
                        ?>
                            <tr class="linha-transacao" data-categoria="<?php echo $row['categoria']; ?>" data-descricao="<?php echo strtolower($row['descricao']); ?>">
                                <td style="padding: 12px; border-bottom: 1px solid #e2e8f0;">
                                    <?php echo date('d/m/Y', strtotime($row['data'])); ?>
                                </td>
                                <td style="padding: 12px; border-bottom: 1px solid #e2e8f0;">
                                    <?php echo htmlspecialchars($row['descricao']); ?>
                                    <?php if ($row['categoria'] == 'Cartão' && $row['fatura_mes']): ?>
                                        <small style="color:#64748b; display: block;">Fatura: <?php echo date('F/Y', strtotime($row['fatura_mes'] . '-01')); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px; border-bottom: 1px solid #e2e8f0;">
                                    <span class="<?php echo $classeBadge; ?>"><?php echo $row['categoria']; ?></span>
                                </td>
                                <td style="padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: right; font-weight: bold; color: <?php echo $row['categoria'] == 'Saldo' ? '#10b981' : ($row['categoria'] == 'Meta' ? '#d97706' : '#ef4444'); ?>;">
                                    R$ <?php echo number_format($row['valor'], 2, ',', '.'); ?>
                                </td>
                                <td style="padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: center;">
                                    <a href="?excluir=<?php echo $row['id']; ?>" 
                                       onclick="return confirm('Tem certeza que deseja excluir esta transação?')"
                                       class="btn-excluir"
                                       title="Excluir">
                                        <i class="fas fa-trash-alt" style="color: #ef4444;"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="padding: 40px; text-align: center; color: #64748b;">
                                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>
                                Nenhuma transação encontrada neste mês.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
function filtrarTabela() {
    const categoria = document.getElementById('filtroCategoria').value;
    const busca = document.getElementById('filtroBusca').value.toLowerCase();
    const linhas = document.querySelectorAll('.linha-transacao');
    
    linhas.forEach(linha => {
        const categoriaLinha = linha.getAttribute('data-categoria');
        const descricaoLinha = linha.getAttribute('data-descricao');
        
        let mostrar = true;
        
        if (categoria !== 'todas' && categoriaLinha !== categoria) {
            mostrar = false;
        }
        
        if (busca && !descricaoLinha.includes(busca)) {
            mostrar = false;
        }
        
        linha.style.display = mostrar ? '' : 'none';
    });
}
</script>

</body>
</html>