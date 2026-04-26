<?php
session_start();

// 1. CORREÇÃO DO TIMEZONE
date_default_timezone_set('America/Sao_Paulo');

// 2. CONFIGURAÇÃO DE ERROS
ini_set('display_errors', 0);
error_reporting(E_ALL);

// 3. VERIFICAÇÃO DE LOGIN
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/conexao.php';

// LIMPEZA DE SEGURANÇA
$id_usuario   = $conn->real_escape_string($_SESSION['usuario_id']);
$nome_usuario = $conn->real_escape_string($_SESSION['usuario_nome']);

// 4. FUNÇÃO PARA BUSCAR TOTAIS NO BANCO
function total($conn, $id, $cat)
{
    $res = $conn->query("SELECT SUM(valor) as t FROM transacoes WHERE categoria = '$cat' AND usuario_id = '$id'");
    if ($res) {
        $row = $res->fetch_assoc();
        return $row['t'] ? (float)$row['t'] : 0.0;
    }
    return 0.0;
}

$valorSaldo  = total($conn, $id_usuario, 'Saldo');
$valorGastos = total($conn, $id_usuario, 'Gastos');
$valorCartao = total($conn, $id_usuario, 'Cartão');
$valorCofre  = total($conn, $id_usuario, 'Cofrinho');

$disponivelReal = $valorSaldo - $valorGastos;

// 5. DADOS PARA O GRÁFICO
$resG = $conn->query("SELECT categoria, SUM(valor) as total FROM transacoes WHERE usuario_id = '$id_usuario' GROUP BY categoria");
$labels = [];
$valores = [];

if ($resG && $resG->num_rows > 0) {
    while ($r = $resG->fetch_assoc()) {
        $labels[] = $r['categoria'];
        $valores[] = $r['total'];
    }
} else {
    $labels = ['Sem dados'];
    $valores = [0.1];
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
    <style>
        :root {
            --primary: #10b981;
            --success: #10b981;
            --danger: #be123c;
            --warning: #d97706;
            --bg: #f1f5f9;
            --sidebar-bg: #064e3b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            display: flex;
            background: var(--bg);
            min-height: 100vh;
        }

        .sidebar {
            width: 230px;
            background: var(--sidebar-bg);
            color: white;
            padding: 15px;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100%;
        }

        .brand {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 30px;
            color: #ffffff;
            text-align: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar nav a {
            color: #cbd5e1;
            text-decoration: none;
            padding: 10px;
            margin-bottom: 5px;
            display: block;
            border-radius: 6px;
            transition: 0.3s;
            font-size: 14px;
        }

        .sidebar nav a:hover,
        .sidebar nav a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .btn-admin {
            color: #facc15 !important;
            font-weight: bold;
            border: 1px dashed #facc15;
            margin-top: 10px;
        }

        .logout {
            margin-top: auto;
            color: #f87171 !important;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 15px !important;
        }

        .main {
            margin-left: 230px;
            flex: 1;
            padding: 25px;
            width: calc(100% - 230px);
        }

        .header {
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 24px;
            color: #1e293b;
        }

        .header span {
            font-size: 14px;
            color: #64748b;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: #064e3b;
            padding: 15px;
            border-radius: 10px;
            transition: 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-card label {
            color: #ffffff;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .stat-card strong {
            font-size: 18px;
            display: block;
            margin-top: 5px;
        }

        .grid-content {
            display: grid;
            grid-template-columns: 0.8fr 1.2fr;
            gap: 20px;
            margin-bottom: 25px;
            max-width: 1000px;
        }

        .panel {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        form input,
        form select,
        form button {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 6px;
        }

        form button {
            background: var(--sidebar-bg);
            color: white;
            border: none;
            font-weight: bold;
            cursor: pointer;
        }

        .card-tabela {
            background: white;
            padding: 20px;
            border-radius: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
        }

        #myChart {
            height: 250px !important;
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <div class="brand"><i class="fas fa-wallet"></i> Meu Real</div>
        <nav>
            <a href="index.php" class="active"><i class="fas fa-home"></i> Início</a>
            <a href="transacoes.php"><i class="fas fa-exchange-alt"></i> Transações</a>
            <a href="perfil.php"><i class="fas fa-user-gear"></i> Perfil</a>

            <?php if (isset($_SESSION['nivel_acesso']) && $_SESSION['nivel_acesso'] == 1): ?>
                <a href="admin_painel.php" class="btn-admin">
                    <i class="fas fa-shield-halved"></i> PAINEL ADMIN
                </a>
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
                <label>Disponível</label>
                <strong style="color: var(--success);">R$ <?php echo number_format($disponivelReal, 2, ',', '.'); ?></strong>
            </div>
            <div class="stat-card">
                <label>Gastos</label>
                <strong style="color: var(--danger);">R$ <?php echo number_format($valorGastos, 2, ',', '.'); ?></strong>
            </div>
            <div class="stat-card">
                <label>Cartão</label>
                <strong style="color: #3b82f6;">R$ <?php echo number_format($valorCartao, 2, ',', '.'); ?></strong>
            </div>
            <div class="stat-card">
                <label>Cofre</label>
                <strong style="color: var(--warning);">R$ <?php echo number_format($valorCofre, 2, ',', '.'); ?></strong>
            </div>
        </section>

        <div class="grid-content">
            <div class="panel">
                <h3><i class="fas fa-plus-circle"></i> Novo Lançamento</h3>
                <form action="salvar_gasto.php" method="POST">
                    <input type="text" name="descricao" placeholder="O que você comprou/recebeu?" required>
                    <div style="display: flex; gap: 10px;">
                        <input type="number" step="0.01" name="valor" placeholder="Valor R$" required>
                        <select name="categoria">
                            <option value="Gastos">Gasto</option>
                            <option value="Saldo">Entrada</option>
                            <option value="Cofrinho">Cofre</option>
                            <option value="Cartão">Cartão</option>
                        </select>
                    </div>
                    <button type="submit">SALVAR</button>
                </form>
            </div>

            <div class="panel" style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <h3>Distribuição</h3>
                <canvas id="myChart"></canvas>
            </div>
        </div>

        <div class="card-tabela">
            <h3><i class="fas fa-history"></i> Últimas Movimentações</h3>
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Descrição</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $lista = $conn->query("SELECT * FROM transacoes WHERE usuario_id = '$id_usuario' ORDER BY id DESC LIMIT 5");
                    if ($lista && $lista->num_rows > 0) {
                        while ($row = $lista->fetch_assoc()) {
                            $cor = ($row['categoria'] == 'Saldo') ? 'var(--success)' : 'var(--danger)';
                            echo "<tr>";
                            echo "<td>" . date('d/m', strtotime($row['data'])) . "</td>";
                            echo "<td>" . $row['descricao'] . "</td>";
                            echo "<td style='font-weight:bold; color:$cor;'>R$ " . number_format($row['valor'], 2, ',', '.') . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3' style='text-align:center;'>Nenhum registro.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        const ctx = document.getElementById('myChart').getContext('2d');
        const labelsDoBanco = <?php echo json_encode($labels); ?>;
        const valoresDoBanco = <?php echo json_encode($valores); ?>;
        const mapaCores = {
            'Saldo': '#10b981',
            'Gastos': '#be123c',
            'Cartão': '#3b82f6',
            'Cofrinho': '#d97706',
            'Sem dados': '#e2e8f0'
        };
        const coresOrdenadas = labelsDoBanco.map(label => mapaCores[label] || '#6366f1');

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labelsDoBanco,
                datasets: [{
                    data: valoresDoBanco,
                    backgroundColor: coresOrdenadas,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>

</html>