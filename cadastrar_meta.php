<?php
session_start();
require_once __DIR__ . '/conexao.php';

// 1. VERIFICAÇÃO DE LOGIN
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['usuario_id'];
$mes_atual  = date('Y-m'); // Formato: 2026-05
$mensagem   = "";

// 2. PROCESSAMENTO DO FORMULÁRIO
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $salario = (float)$_POST['salario_mes'];
    $meta    = (float)$_POST['valor_meta'];

    // Verifica se já existe meta para este mês para evitar erro de duplicidade
    $check = $conn->query("SELECT id FROM metas WHERE id_usuario = '$id_usuario' AND mes_referencia = '$mes_atual'");

    if ($check->num_rows > 0) {
        $mensagem = "<div style='color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>
                        ⚠️ Você já definiu uma meta para este mês!
                     </div>";
    } else {
        $sql = "INSERT INTO metas (id_usuario, salario_mes, valor_meta, mes_referencia) 
                VALUES ('$id_usuario', '$salario', '$meta', '$mes_atual')";

        if ($conn->query($sql)) {
            // Sucesso! Redireciona para o dashboard
            header("Location: index.php?sucesso=meta_definida");
            exit();
        } else {
            $mensagem = "<div style='color: #721c24; background: #f8d7da; padding: 10px; border-radius: 5px;'>
                            ❌ Erro ao salvar: " . $conn->error . "
                         </div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Definir Minha Caixinha - Meu Real</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

      <link rel="stylesheet" href="css/cadastrar_meta.css">
    
</head>

<body>

    <div class="card">
        <div class="header-form">
            <i class="fas fa-piggy-bank"></i>
            <h2>Meta Inteligente</h2>
            <p>Planeje seu mês de <?php echo date('F / Y'); ?></p>
        </div>

        <?php echo $mensagem; ?>

        <form method="POST">
            <label>Qual o seu salário este mês?</label>
            <input type="number" step="0.01" name="salario_mes" placeholder="Ex: 2500.00" required autofocus>

            <label>Quanto deseja guardar (Meta)?</label>
            <input type="number" step="0.01" name="valor_meta" placeholder="Ex: 500.00" required>

            <button type="submit" class="btn-salvar">DEFINIR CAIXINHA</button>
        </form>

        <div class="info-calculo">
            O sistema calculará automaticamente o quanto resta para seus gastos após a reserva da meta.
        </div>

        <a href="index.php" class="voltar">← Voltar ao início</a>
    </div>

</body>

</html>