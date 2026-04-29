<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/conexao.php';

$id_usuario   = $conn->real_escape_string($_SESSION['usuario_id']);
$nome_usuario = $conn->real_escape_string($_SESSION['usuario_nome']);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Meu Real - Meu Perfil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/perfil.css">
</head>

<body>

    <aside class="sidebar">
        <div class="brand"><i class="fas fa-wallet"></i> Meu Real</div>
        <nav>
            <a href="index.php"><i class="fas fa-home"></i> Início</a>
            <a href="transacoes.php"><i class="fas fa-exchange-alt"></i> Transações</a>
            <a href="perfil.php" style="background: rgba(255,255,255,0.1); color: white;"><i class="fas fa-user-gear"></i> Perfil</a>
            <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </nav>
    </aside>

    <main class="main">
        <header class="header">
            <h1>Configurações de Perfil</h1>
        </header>

        <div class="panel">
            <h3><i class="fas fa-user-circle"></i> Meus Dados</h3>
            <form action="atualizar_perfil.php" method="POST">
                <label>Nome Completo:</label>
                <input type="text" name="nome" value="<?php echo htmlspecialchars($nome_usuario); ?>" required>

                <label>Nova Senha:</label>
                <input type="password" name="nova_senha" placeholder="Deixe em branco para manter a atual">

                <button type="submit" class="btn-save">SALVAR ALTERAÇÕES</button>
            </form>

            <div class="danger-zone">
                <h4><i class="fas fa-exclamation-triangle"></i> Zona de Perigo</h4>
                <p>Ao desativar sua conta, seus dados serão arquivados e você será deslogado.</p>

                <a href="acoes/arquivar_conta.php?id=<?php echo $id_usuario; ?>"
                    class="btn-delete"
                    onclick="return confirm('Sua conta será desativada e enviada para a lixeira do administrador. Tem certeza?')">
                    <i class="fas fa-user-slash"></i> Desativar minha conta
                </a>
            </div>
        </div>
    </main>

</body>

</html>