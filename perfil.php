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

// Buscar dados do usuário
$sql = "SELECT nome, email, limite_cartao, fechamento_cartao FROM usuarios WHERE id = '$id_usuario'";
$res = $conn->query($sql);
$usuario = $res->fetch_assoc();

// Processar atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao']) && $_POST['acao'] === 'perfil') {
        $nome = $conn->real_escape_string($_POST['nome']);
        $email = $conn->real_escape_string($_POST['email']);
        $limite_cartao = (float) $_POST['limite_cartao'];
        $fechamento_cartao = (int) $_POST['fechamento_cartao'];
        
        $sql = "UPDATE usuarios SET nome='$nome', email='$email', limite_cartao='$limite_cartao', fechamento_cartao='$fechamento_cartao' WHERE id='$id_usuario'";
        if ($conn->query($sql)) {
            $_SESSION['sucesso'] = "Perfil atualizado com sucesso!";
            $_SESSION['usuario_nome'] = $nome;
        } else {
            $_SESSION['erro'] = "Erro ao atualizar: " . $conn->error;
        }
        header("Location: perfil.php");
        exit();
    }
    
    if (isset($_POST['acao']) && $_POST['acao'] === 'senha') {
        $senha = $_POST['senha'];
        $confirmar = $_POST['confirmar_senha'];
        
        if (empty($senha) && empty($confirmar)) {
            $_SESSION['erro'] = "Digite uma nova senha";
        } elseif ($senha === $confirmar && strlen($senha) >= 6) {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET senha='$senha_hash' WHERE id='$id_usuario'";
            if ($conn->query($sql)) {
                $_SESSION['sucesso'] = "Senha alterada com sucesso!";
            } else {
                $_SESSION['erro'] = "Erro ao alterar senha.";
            }
        } else {
            $_SESSION['erro'] = "Senhas não coincidem ou são muito curtas (mínimo 6 caracteres).";
        }
        header("Location: perfil.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Meu Real</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/perfil.css">
    <link rel="stylesheet" href="css/index.css">
   
</head>
<body>

<aside class="sidebar">
    <div class="brand">
        <i class="fas fa-piggy-bank"></i>
        <span>Meu Real</span>
    </div>
    <nav>
        <a href="index.php"><i class="fas fa-home"></i><span>Início</span></a>
        <a href="transacoes.php"><i class="fas fa-exchange-alt"></i><span>Transações</span></a>
        <a href="fatura.php"><i class="fas fa-credit-card"></i><span>Faturas</span></a>
        <a href="perfil.php" class="active"><i class="fas fa-user-gear"></i><span>Perfil</span></a>
        <?php if (isset($_SESSION['nivel_acesso']) && $_SESSION['nivel_acesso'] == 1): ?>
            <a href="admin_painel.php" class="btn-admin"><i class="fas fa-shield-halved"></i><span>PAINEL ADMIN</span></a>
        <?php endif; ?>
        <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i><span>Sair</span></a>
    </nav>
</aside>

<main class="main">
    <div class="perfil-container">
        <?php if (isset($_SESSION['sucesso'])): ?>
            <div class="toast-success"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['sucesso']; unset($_SESSION['sucesso']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['erro'])): ?>
            <div class="toast-error"><i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['erro']; unset($_SESSION['erro']); ?></div>
        <?php endif; ?>

        <header class="header">
            <h1><i class="fas fa-user-circle"></i> Configurações de Perfil</h1>
            <span><?php echo date('d/m/Y'); ?></span>
        </header>

        <!-- Meus Dados -->
        <div class="perfil-card">
            <h2><i class="fas fa-id-card"></i> Meus Dados</h2>
            <form method="POST">
                <input type="hidden" name="acao" value="perfil">
                
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Nome Completo</label>
                    <input type="text" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> E-mail</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                </div>
                
                <div class="row-2">
                    <div class="form-group">
                        <label><i class="fas fa-credit-card"></i> Limite do Cartão</label>
                        <input type="number" step="0.01" name="limite_cartao" value="<?php echo $usuario['limite_cartao']; ?>" placeholder="R$ 0,00">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt"></i> Fechamento da Fatura</label>
                        <input type="number" name="fechamento_cartao" value="<?php echo $usuario['fechamento_cartao']; ?>" min="1" max="31" placeholder="Dia">
                    </div>
                </div>
                
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> SALVAR ALTERAÇÕES</button>
            </form>
        </div>

        <!-- Alterar Senha -->
        <div class="perfil-card">
            <h2><i class="fas fa-lock"></i> Segurança</h2>
            <form method="POST">
                <input type="hidden" name="acao" value="senha">
                
                <div class="form-group">
                    <label><i class="fas fa-key"></i> Nova Senha</label>
                    <input type="password" name="senha" placeholder="Digite a nova senha">
                    <small>Deixe em branco para manter a atual</small>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-check-circle"></i> Confirmar Senha</label>
                    <input type="password" name="confirmar_senha" placeholder="Confirme a nova senha">
                </div>
                
                <button type="submit" class="btn-save"><i class="fas fa-sync-alt"></i> ALTERAR SENHA</button>
            </form>
        </div>

        <!-- Zona de Perigo -->
        <div class="perfil-card">
            <h2><i class="fas fa-exclamation-triangle"></i> Zona de Perigo</h2>
            <p>Ao excluir sua conta, todos os seus dados financeiros serão permanentemente removidos. Esta ação não pode ser desfeita.</p>
            <a href="excluir.php" onclick="return confirm('ATENÇÃO: Tem certeza que deseja excluir sua conta? Todos os seus dados serão perdidos para sempre!')">
                <button type="button" class="btn-save btn-danger"><i class="fas fa-trash-alt"></i> EXCLUIR MINHA CONTA</button>
            </a>
        </div>
    </div>
</main>

<script>
setTimeout(() => {
    document.querySelectorAll('.toast-success, .toast-error').forEach(toast => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    });
}, 4000);
</script>

</body>
</html>