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

function totalCategoria($conn, $id, $categoria, $status = null) {
    $sql = "SELECT SUM(valor) as t 
            FROM transacoes 
            WHERE categoria = ? AND usuario_id = ? 
            AND MONTH(data) = MONTH(CURRENT_DATE()) 
            AND YEAR(data) = YEAR(CURRENT_DATE())";
    if ($status) {
        $sql .= " AND status = ?";
    } elseif ($categoria === 'Cartão') {
        // Para cartão, considerar apenas pendentes ou sem status
        $sql .= " AND (status IS NULL OR status = 'pendente')";
    }
    $stmt = $conn->prepare($sql);
    if ($status) {
        $stmt->bind_param("sis", $categoria, $id, $status);
    } else {
        $stmt->bind_param("si", $categoria, $id);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return $row['t'] ? (float)$row['t'] : 0.0;
}

$entradas     = totalCategoria($conn, $id_usuario, 'Saldo');
$gastosDebito = totalCategoria($conn, $id_usuario, 'Gastos');
$gastosCartao = totalCategoria($conn, $id_usuario, 'Cartão');
$reservaTotal = totalCategoria($conn, $id_usuario, 'Meta');

$saldoDebito = $entradas - $gastosDebito;
$disponivelDebito = $saldoDebito - $reservaTotal;

$sql_limite = "SELECT limite_cartao FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql_limite);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$limite = (float) $stmt->get_result()->fetch_assoc()['limite_cartao'];
$stmt->close();
$limiteDisponivel = max(0, $limite - $gastosCartao);

$labels = [];
$valores = [];
if ($entradas > 0) { $labels[] = 'Entradas'; $valores[] = $entradas; }
if ($gastosDebito > 0) { $labels[] = 'Gastos Débito'; $valores[] = $gastosDebito; }
if ($gastosCartao > 0) { $labels[] = 'Gastos Cartão'; $valores[] = $gastosCartao; }
if ($reservaTotal > 0) { $labels[] = 'Meta/Reserva'; $valores[] = $reservaTotal; }
if (empty($labels)) { $labels = ['Sem dados']; $valores = [1]; }

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
    <div class="brand"><i class="fas fa-piggy-bank"></i> Meu Real</div>
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
            <label>Disponível para Débito</label>
            <strong style="color: <?php echo ($disponivelDebito <= 0 ? '#ef4444' : '#10b981'); ?>;">
                R$ <?php echo number_format($disponivelDebito, 2, ',', '.'); ?>
            </strong>
            <p style="font-size:12px;">Após separar a reserva</p>
        </div>

        <div class="stat-card">
            <label>Limite do Cartão</label>
            <strong style="color:#3b82f6;">R$ <?php echo number_format($limiteDisponivel, 2, ',', '.'); ?></strong>
            <p style="font-size:12px;">De R$ <?php echo number_format($limite, 2, ',', '.'); ?></p>
        </div>

        <div class="stat-card">
            <label>Gastos (Débito)</label>
            <strong style="color:#ef4444;">R$ <?php echo number_format($gastosDebito, 2, ',', '.'); ?></strong>
            <p style="font-size:12px;">Total gasto à vista no mês</p>
        </div>

        <div class="stat-card">
            <label>Reserva (Meta)</label>
            <strong style="color:#f59e0b;">R$ <?php echo number_format($reservaTotal, 2, ',', '.'); ?></strong>
            <?php if ($saldoDebito - $reservaTotal < 0): ?>
                <p class="alerta-meta alerta-danger">⚠️ Reserva comprometida</p>
            <?php else: ?>
                <p class="alerta-meta alerta-safe">✔ Reserva separada</p>
            <?php endif; ?>
        </div>
    </section>

    <div class="grid-content">
        <div class="panel">
            <h3><i class="fas fa-plus-circle"></i> Novo Lançamento</h3>
            <form action="salvar_gasto.php" method="POST">
                <input type="number" step="0.01" name="valor" placeholder="Valor R$" required>
                <input type="text" name="descricao" placeholder="Descrição" required>
                <input type="datetime-local" name="data" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                <select name="categoria">
                    <option value="Gastos">Gasto à Vista (Débito)</option>
                    <option value="Saldo">Entrada (Saldo)</option>
                    <option value="Meta">Meta / Reserva</option>
                    <option value="Cartão">Cartão de Crédito</option>
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
        <table style="width:100%">
            <thead><tr><th>Data</th><th>Descrição</th><th>Valor</th><th>Categoria</th></tr></thead>
            <tbody>
                <?php
                $stmt = $conn->prepare("SELECT * FROM transacoes WHERE usuario_id = ? ORDER BY data DESC, id DESC LIMIT 5");
                $stmt->bind_param("i", $id_usuario);
                $stmt->execute();
                $lista = $stmt->get_result();
                if ($lista && $lista->num_rows > 0):
                    while ($row = $lista->fetch_assoc()):
                        switch ($row['categoria']) {
                            case 'Saldo': $cor = '#10b981'; break;
                            case 'Meta': $cor = '#d97706'; break;
                            case 'Cartão': $cor = '#3b82f6'; break;
                            default: $cor = '#be123c';
                        }
                ?>
                    <tr>
                        <td><?php echo date('d/m H:i', strtotime($row['data'])); ?></td>
                        <td><?php echo htmlspecialchars($row['descricao']); ?></td>
                        <td style="color:<?php echo $cor; ?>;">R$ <?php echo number_format($row['valor'], 2, ',', '.'); ?></td>
                        <td><?php echo $row['categoria']; ?></td>
                    </tr>
                <?php
                    endwhile;
                else:
                ?>
                    <tr><td colspan="4" style="text-align:center;">Nenhum registro ainda.</td></tr>
                <?php endif; ?>
            </tbody>
         </table>
    </div>
</main>

<script>
    const ctx = document.getElementById('myChart').getContext('2d');
    const labels = <?php echo json_encode($labels); ?>;
    const valores = <?php echo json_encode($valores); ?>;
    const cores = {'Entradas':'#10b981','Gastos Débito':'#be123c','Gastos Cartão':'#3b82f6','Meta/Reserva':'#d97706','Sem dados':'#e2e8f0'};
    const backgroundCores = labels.map(label => cores[label] || '#ccc');
    new Chart(ctx, {
        type: 'pie',
        data: { labels: labels, datasets: [{ data: valores, backgroundColor: backgroundCores, borderWidth: 0 }] },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'bottom' } } }
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