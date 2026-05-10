<?php
session_start();

// 1. TIMEZONE
date_default_timezone_set('America/Sao_Paulo');

// 2. CONFIGURAÇÃO DE ERROS
ini_set('display_errors', 0);
error_reporting(E_ALL);

// 3. VERIFICA LOGIN
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/conexao.php';

// SEGURANÇA
$id_usuario   = $conn->real_escape_string($_SESSION['usuario_id']);
$nome_usuario = $conn->real_escape_string($_SESSION['usuario_nome']);



function total($conn, $id, $cat)
{
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

        return $row['t']
            ? (float)$row['t']
            : 0.0;
    }

    return 0.0;
}

// TOTAIS
$valorSaldo  = total($conn, $id_usuario, 'Saldo');
$valorGastos = total($conn, $id_usuario, 'Gastos');

$faturaAtual = date('Y-m');

$resCartao = $conn->query("
    SELECT SUM(valor) as total
    FROM transacoes
    WHERE categoria='Cartão'
    AND usuario_id='$id_usuario'
    AND fatura_mes='$faturaAtual'
");

$rowCartao = $resCartao->fetch_assoc();

$valorCartao = $rowCartao['total']
    ? (float)$rowCartao['total']
    : 0;
$dadosCartao = $conn->query("
    SELECT limite_cartao
    FROM usuarios
    WHERE id = '$id_usuario'
")->fetch_assoc();

$limiteCartao = (float)$dadosCartao['limite_cartao'];

$limiteDisponivel = $limiteCartao - $valorCartao;
$valorMeta   = total($conn, $id_usuario, 'Meta');

// SALDOS
$saldoBruto      = $valorSaldo - $valorGastos;
$disponivelReal  = $saldoBruto - $valorMeta;
$saldoGrafico    = $saldoBruto - $valorMeta;

// EVITA NEGATIVO NO GRÁFICO
if ($saldoGrafico < 0) {
    $saldoGrafico = 0;
}

// DADOS DO GRÁFICO
$labels = [];
$valores = [];

// SALDO LIVRE
if ($saldoGrafico > 0) {
    $labels[] = 'Saldo Livre';
    $valores[] = $saldoGrafico;
}

// GASTOS
if ($valorGastos > 0) {
    $labels[] = 'Gastos';
    $valores[] = $valorGastos;
}

// CARTÃO
if ($valorCartao > 0) {
    $labels[] = 'Cartão';
    $valores[] = $valorCartao;
}

// META
if ($valorMeta > 0) {
    $labels[] = 'Reserva (Meta)';
    $valores[] = $valorMeta;
}

// SEM DADOS
if (empty($labels)) {
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

    <link rel="stylesheet" href="css/index.css">

</head>


<body>

    <aside class="sidebar">
        <div class="brand">
            <i class="fas fa-piggy-bank fa-2x bg-icon"></i> Meu Real
        </div>

        <nav>
            <a href="index.php" class="active">
                <i class="fas fa-home"></i> Início
            </a>

            <a href="transacoes.php">
                <i class="fas fa-exchange-alt"></i> Transações
            </a>

            <a href="perfil.php">
                <i class="fas fa-user-gear"></i> Perfil
            </a>

            <?php if (isset($_SESSION['nivel_acesso']) && $_SESSION['nivel_acesso'] == 1): ?>
                <a href="admin_painel.php" class="btn-admin">
                    <i class="fas fa-shield-halved"></i> PAINEL ADMIN
                </a>
            <?php endif; ?>

            <a href="logout.php" class="logout">
                <i class="fas fa-sign-out-alt"></i> Sair
            </a>
        </nav>
    </aside>

    <main class="main">

        <header class="header">
            <h1>
                Olá, <?php echo explode(' ', $nome_usuario)[0]; ?>
            </h1>

            <span>
                <?php echo date('d/m/Y'); ?>
            </span>
        </header>

        <section class="stats">

            <!-- DISPONÍVEL -->
            <div class="stat-card">
                <label>Disponível</label>

                <strong style="color:#10b981;">
                    R$ <?php echo number_format($disponivelReal, 2, ',', '.'); ?>
                </strong>
            </div>

            <div class="stat-card">
                <label>Gastos</label>

                <strong style="color:#ef4444;">
                    R$ <?php echo number_format($valorGastos, 2, ',', '.'); ?>
                </strong>
            </div>

            <div class="stat-card">
                <label>Cartão de Crédito</label>

                <strong style="color:#3b82f6;">
                    R$ <?php echo number_format($valorCartao, 2, ',', '.'); ?>
                </strong>

                <p style="font-size:13px; color:#64748b; margin-top:8px;">
                    Limite disponível:
                    <strong style="font-size:13px; color:#0f172a;">
                        R$ <?php echo number_format($limiteDisponivel, 2, ',', '.'); ?>
                    </strong>
                </p>
            </div>

            <div class="stat-card">
                <label>Meta / Reserva</label>

                <strong style="color:#f59e0b;">
                    R$ <?php echo number_format($valorMeta, 2, ',', '.'); ?>
                </strong>

                <?php if ($disponivelReal < 0): ?>

                    <p class="alerta-meta alerta-danger">
                        ⚠ Você está usando sua reserva
                    </p>

                <?php else: ?>

                    <p class="alerta-meta alerta-safe">
                        ✔ Reserva protegida
                    </p>

                <?php endif; ?>
            </div>

            </div>

        </section>

        <div class="grid-content">

            <div class="panel">

                <h3>
                    <i class="fas fa-plus-circle"></i>
                    Novo Lançamento
                </h3>



                <form action="salvar_gasto.php" method="POST">

                    <input
                        type="number"
                        step="0.01"
                        name="valor"
                        placeholder="Valor R$"
                        required>

                    <input
                        type="text"
                        name="descricao"
                        placeholder="O que você comprou/recebeu?"
                        required>

                    <input
                        type="date"
                        name="data"
                        value="<?php echo date('Y-m-d'); ?>"
                        required>

                    <select name="categoria" id="categoria">

                        <option value="Gastos">Gasto</option>

                        <option value="Saldo">Entrada</option>

                        <option value="Meta">Meta / Reserva</option>

                        <option value="Cartão">Cartão</option>

                    </select>

                    <button type="submit">
                        SALVAR
                    </button>

                </form>
            </div>

            <div class="panel"
                style="display:flex; flex-direction:column; align-items:center; justify-content:center;">

                <!-- <h3>Distribuição Financeira</h3> -->
                <h3 style="margin-bottom:15px;">
                    Distribuição Financeira
                </h3>

                <canvas id="myChart"></canvas>

            </div>

        </div>

        <div class="card-tabela">

            <h3>
                <i class="fas fa-history"></i>
                Últimas Movimentações
            </h3>

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


                    $lista = $conn->query("
    SELECT * 
    FROM transacoes 
    WHERE usuario_id = '$id_usuario'
    AND MONTH(data)=MONTH(CURRENT_DATE())
    AND YEAR(data)=YEAR(CURRENT_DATE())
    ORDER BY id DESC
    LIMIT 5
");

                    if ($lista && $lista->num_rows > 0) {

                        while ($row = $lista->fetch_assoc()) {

                            $cor = '#be123c';

                            if ($row['categoria'] == 'Saldo') {
                                $cor = '#10b981';
                            }

                            if ($row['categoria'] == 'Meta') {
                                $cor = '#d97706';
                            }

                            if ($row['categoria'] == 'Cartão') {
                                $cor = '#3b82f6';
                            }

                            echo "<tr>";

                            echo "<td>" .
                                date('d/m', strtotime($row['data'])) .
                                "</td>";

                            echo "<td>" .
                                $row['descricao'] .
                                "</td>";

                            echo "<td style='font-weight:bold; color:$cor;'>R$ " .
                                number_format($row['valor'], 2, ',', '.') .
                                "</td>";

                            echo "</tr>";
                        }
                    } else {

                        echo "
                        <tr>
                            <td colspan='3' style='text-align:center;'>
                                Nenhum registro.
                            </td>
                        </tr>";
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

            'Saldo Livre': '#10b981',

            'Gastos': '#be123c',

            'Cartão': '#3b82f6',

            'Reserva (Meta)': '#d97706',

            'Sem dados': '#e2e8f0'
        };

        const coresOrdenadas = labelsDoBanco.map(label =>
            mapaCores[label] || '#6366f1'
        );

        new Chart(ctx, {

            type: 'pie',

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

                plugins: {

                    legend: {


                        position: 'bottom',
                        labels: {
                            boxWidth: 15,
                            padding: 15,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    }
                }
            }
        });
    </script>


</body>

</html>