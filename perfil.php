<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/conexao.php';

$id_usuario = $_SESSION['usuario_id'];
$mensagem_sucesso = '';
$mensagem_erro = '';

// Processar atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $limite_cartao = (float) str_replace(',', '.', $_POST['limite_cartao']);
    $dia_fechamento = (int) $_POST['dia_fechamento'];
    $dia_vencimento = (int) $_POST['dia_vencimento'];

    if ($dia_fechamento < 1 || $dia_fechamento > 31) {
        $mensagem_erro = "Dia de fechamento inválido (1-31).";
    } elseif ($dia_vencimento < 1 || $dia_vencimento > 31) {
        $mensagem_erro = "Dia de vencimento inválido (1-31).";
    } else {
        $sql = "UPDATE usuarios SET nome = ?, email = ?, limite_cartao = ?, dia_fechamento = ?, dia_vencimento = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssdiii", $nome, $email, $limite_cartao, $dia_fechamento, $dia_vencimento, $id_usuario);
            if ($stmt->execute()) {
                $_SESSION['usuario_nome'] = $nome;
                $mensagem_sucesso = "Perfil atualizado com sucesso!";
            } else {
                $mensagem_erro = "Erro ao salvar: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $mensagem_erro = "Erro na preparação da consulta.";
        }
    }
}

// Buscar dados atuais
$sql_select = "SELECT nome, email, limite_cartao, dia_fechamento, dia_vencimento FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql_select);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $user = [
        'nome' => '',
        'email' => '',
        'limite_cartao' => 0,
        'dia_fechamento' => 10,
        'dia_vencimento' => 5
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Meu Real - Perfil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/index.css">
    <style>
        .perfil-container { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #1e293b; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; }
        .btn-salvar { background: #10b981; color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; }
        .btn-salvar:hover { background: #059669; }
        .toast-success { background: #10b981; color: white; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .toast-error { background: #ef4444; color: white; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>
<aside class="sidebar">
    <div class="brand"><i class="fas fa-piggy-bank"></i> Meu Real</div>
    <nav>
        <a href="index.php"><i class="fas fa-home"></i> Início</a>
        <a href="transacoes.php"><i class="fas fa-exchange-alt"></i> Transações</a>
        <a href="fatura.php"><i class="fas fa-credit-card"></i> Faturas</a>
        <a href="perfil.php" class="active"><i class="fas fa-user-gear"></i> Perfil</a>
        <?php if (isset($_SESSION['nivel_acesso']) && $_SESSION['nivel_acesso'] == 1): ?>
            <a href="admin_painel.php" class="btn-admin"><i class="fas fa-shield-halved"></i> PAINEL ADMIN</a>
        <?php endif; ?>
        <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </nav>
</aside>
<main class="main">
    <div class="perfil-container">
        <h1><i class="fas fa-user-edit"></i> Meu Perfil</h1>
        <?php if ($mensagem_sucesso): ?>
            <div class="toast-success"><?php echo htmlspecialchars($mensagem_sucesso); ?></div>
        <?php endif; ?>
        <?php if ($mensagem_erro): ?>
            <div class="toast-error"><?php echo htmlspecialchars($mensagem_erro); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>Nome Completo</label>
                <input type="text" name="nome" value="<?php echo htmlspecialchars($user['nome']); ?>" required>
            </div>
            <div class="form-group">
                <label>E-mail</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <h3>Configurações do Cartão</h3>
            <div class="form-group">
                <label>Limite do Cartão (R$)</label>
                <input type="number" step="0.01" name="limite_cartao" value="<?php echo number_format($user['limite_cartao'], 2, '.', ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Dia do Fechamento da Fatura</label>
                <input type="number" name="dia_fechamento" min="1" max="31" value="<?php echo $user['dia_fechamento']; ?>" required>
            </div>
            <div class="form-group">
                <label>Dia do Vencimento da Fatura</label>
                <input type="number" name="dia_vencimento" min="1" max="31" value="<?php echo $user['dia_vencimento']; ?>" required>
            </div>
            <button type="submit" class="btn-salvar">Salvar Alterações</button>
        </form>
    </div>
</main>
<script>
    setTimeout(() => {
        document.querySelectorAll('.toast-success, .toast-error').forEach(el => {
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 500);
        });
    }, 4000);
</script>
</body>
</html>