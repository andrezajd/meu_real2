<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/conexao.php';

$id_usuario = $conn->real_escape_string($_SESSION['usuario_id']);
$nome_usuario = $conn->real_escape_string($_SESSION['usuario_nome']);

// ========== FUNÇÃO PARA TOTAL DO MÊS ==========
function total($conn, $id, $cat) {
    $res = $conn->query("
        SELECT SUM(valor) as t 
        FROM transacoes 
        WHERE categoria = '$cat'
        AND usuario_id = '$id'
        AND MONTH(data)=MONTH(CURRENT_DATE())
        AND YEAR(data)=YEAR(CURRENT_DATE())
    ");
    if ($res) {
        $row = $res->fetch_assoc();
        return $row['t'] ? (float)$row['t'] : 0.0;
    }
    return 0.0;
}

// ========== TOTAIS DO MÊS ==========
$entradas      = total($conn, $id_usuario, 'Saldo');
$gastosDebito  = total($conn, $id_usuario, 'Gastos');
$gastosCartao  = total($conn, $id_usuario, 'Cartão');
$reservaTotal  = total($conn, $id_usuario, 'Meta');

// ========== CÁLCULO UNIFICADO ==========
$saldoBruto = $entradas - ($gastosDebito + $gastosCartao);  // antes da reserva
$disponivelLivre = $saldoBruto - $reservaTotal;            // livre para gastar
$reservaConsumida = max(0, -$disponivelLivre);
$reservaRestante = max(0, $reservaTotal - $reservaConsumida);

// ========== LIMITE DO CARTÃO ==========
$sql_limite = "SELECT limite_cartao FROM usuarios WHERE id = '$id_usuario'";
$res_limite = $conn->query($sql_limite);
$dados_limite = $res_limite->fetch_assoc();
$limiteCartao = $dados_limite ? (float)$dados_limite['limite_cartao'] : 0;
$limiteDisponivel = max(0, $limiteCartao - $gastosCartao);
$disponivelCartaoSelect = $limiteDisponivel;

// ========== DADOS PARA O GRÁFICO (usando valores originais) ==========
$labels = [];
$valores = [];

if ($saldoBruto > 0) {
    $labels[] = 'Saldo';
    $valores[] = $saldoBruto;
}
if ($gastosDebito > 0) {
    $labels[] = 'Gastos';
    $valores[] = $gastosDebito;
}
if ($gastosCartao > 0) {
    $labels[] = 'Cartão';
    $valores[] = $gastosCartao;
}
if ($reservaTotal > 0) {
    $labels[] = 'Meta';
    $valores[] = $reservaTotal;
}
if (empty($labels)) {
    $labels = ['Sem dados'];
    $valores = [1];
}

// ========== MENSAGENS DE FEEDBACK ==========
if (isset($_SESSION['sucesso'])) {
    echo '<div class="toast-success">' . $_SESSION['sucesso'] . '</div>';
    unset($_SESSION['sucesso']);
}
if (isset($_SESSION['erro'])) {
    echo '<div class="toast-error">' . $_SESSION['erro'] . '</div>';
    unset($_SESSION['erro']);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Real - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>

<aside class="sidebar">
    <div class="brand">
        <i class="fas fa-piggy-bank"></i> Meu Real
    </div>
    <nav>
        <a href="index.php" class="active"><i class="fas fa-home"></i> Início</a>
        <a href="transacoes.php"><i class="fas fa-exchange-alt"></i> Transações</a>
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
        <h1>Olá, <?php echo explode(' ', $nome_usuario)[0]; ?></h1>
        <span><?php echo date('d/m/Y'); ?></span>
    </header>

    <section class="stats">
        <div class="stat-card">
            <label>Disponível (Livre)</label>
            <?php $corDisponivel = ($disponivelLivre <= 0) ? '#ef4444' : '#10b981'; ?>
            <strong style="color: <?php echo $corDisponivel; ?>;">
                R$ <?php echo number_format($disponivelLivre, 2, ',', '.'); ?>
            </strong>
            <?php if ($disponivelLivre < 0): ?>
                <p style="font-size: 11px; color: #ef4444; margin-top: 4px;">
                    ⚠️ Saldo negativo
                </p>
            <?php endif; ?>
        </div>

        <div class="stat-card">
            <label>Gastos</label>
            <strong style="color:#ef4444;">R$ <?php echo number_format($gastosDebito, 2, ',', '.'); ?></strong>
        </div>

        <div class="stat-card">
            <label>Cartão de Crédito</label>
            <strong style="color:#3b82f6;">R$ <?php echo number_format($gastosCartao, 2, ',', '.'); ?></strong>
            <p style="font-size:13px; color:#64748b; margin-top:8px;">
                Limite disponível:
                <strong>R$ <?php echo number_format($limiteDisponivel, 2, ',', '.'); ?></strong>
            </p>
        </div>

        <div class="stat-card">
            <label>Meta / Reserva</label>
            <strong style="color:#f59e0b;">R$ <?php echo number_format($reservaRestante, 2, ',', '.'); ?></strong>
            <?php if ($reservaConsumida > 0): ?>
                <p class="alerta-meta alerta-danger">
                    ⚠ Você está usando sua reserva (consumido R$ <?php echo number_format($reservaConsumida, 2, ',', '.'); ?>)
                </p>
            <?php else: ?>
                <p class="alerta-meta alerta-safe">✔ Reserva protegida</p>
            <?php endif; ?>
        </div>
    </section>

    <div class="grid-content">
        <div class="panel">
            <h3><i class="fas fa-plus-circle"></i> Novo Lançamento</h3>
            <form action="salvar_gasto.php" method="POST">
                <input type="number" step="0.01" name="valor" placeholder="Valor R$" required>
                <input type="text" name="descricao" placeholder="O que você comprou/recebeu?" required>
                <input type="datetime-local" name="data" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                <select name="categoria">
                    <option value="Gastos"> Gasto</option>
                    <option value="Saldo"> Entrada</option>
                    <option value="Meta"> Meta / Reserva</option>
                    <?php if ($disponivelCartaoSelect <= 0 && $limiteCartao > 0): ?>
                        <option value="Cartão" disabled style="color:#999;">🚫 Cartão (Limite esgotado)</option>
                    <?php else: ?>
                        <option value="Cartão"> Cartão (Disponível: R$ <?php echo number_format($disponivelCartaoSelect, 2, ',', '.'); ?>)</option>
                    <?php endif; ?>
                </select>
                <button type="submit">SALVAR</button>
            </form>
        </div>

        <div class="panel">
            <h3>Distribuição Financeira</h3>
            <canvas id="myChart" style="max-height:250px;"></canvas>
        </div>
    </div>

    <div class="card-tabela">
        <h3><i class="fas fa-history"></i> Últimas Movimentações</h3>
        <table>
            <thead>
                <tr><th>Data</th><th>Descrição</th><th>Valor</th></tr>
            </thead>
            <tbody>
                <?php
                $lista = $conn->query("
                    SELECT * FROM transacoes 
                    WHERE usuario_id = '$id_usuario'
                    AND MONTH(data)=MONTH(CURRENT_DATE())
                    ORDER BY id DESC LIMIT 5
                ");
                if ($lista && $lista->num_rows > 0):
                    while ($row = $lista->fetch_assoc()):
                        if ($row['categoria'] == 'Saldo') {
                            $cor = '#10b981';
                        } elseif ($row['categoria'] == 'Meta') {
                            $cor = '#d97706';
                        } elseif ($row['categoria'] == 'Cartão') {
                            $cor = '#3b82f6';
                        } else {
                            $cor = '#be123c';
                        }
                ?>
                    <tr>
                        <td><?php echo date('d/m', strtotime($row['data'])); ?></td>
                        <td><?php echo htmlspecialchars($row['descricao']); ?></td>
                        <td style="font-weight:bold; color:<?php echo $cor; ?>;">R$ <?php echo number_format($row['valor'], 2, ',', '.'); ?></td>
                    </tr>
                <?php
                    endwhile;
                else:
                ?>
                    <td colspan="3" style="text-align:center;">Nenhum registro.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
    const ctx = document.getElementById('myChart').getContext('2d');
    const labels = <?php echo json_encode($labels); ?>;
    const valores = <?php echo json_encode($valores); ?>;
    const cores = {
        'Saldo': '#10b981',
        'Gastos': '#be123c',
        'Cartão': '#3b82f6',
        'Meta': '#d97706',
        'Sem dados': '#e2e8f0'
    };
    const backgroundCores = labels.map(label => cores[label] || '');

    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: valores,
                backgroundColor: backgroundCores,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10, font: { size: 11 } } }
            }
        }
    });

    setTimeout(() => {
        document.querySelectorAll('.toast-success, .toast-error').forEach(el => {
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 300);
        });
    }, 4000);
</script>

</body>
</html>