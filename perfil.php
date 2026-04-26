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
    <style>
        :root {
            --primary: #10b981;
            --bg: #f1f5f9;
            --sidebar-bg: #064e3b;
            --danger: #ef4444;
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

        /* Sidebar */
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
            padding: 12px;
            margin-bottom: 5px;
            display: block;
            border-radius: 6px;
            transition: 0.3s;
            font-size: 14px;
        }

        .sidebar nav a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .logout {
            margin-top: auto;
            color: #f87171 !important;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 15px !important;
        }

        /* Main Content */
        .main {
            margin-left: 230px;
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .header {
            margin-bottom: 30px;
            width: 100%;
            max-width: 500px;
        }

        .header h1 {
            font-size: 24px;
            color: #1e293b;
        }

        .panel {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 500px;
            transition: all 0.3s ease;
        }

        .panel:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        h3 {
            margin-bottom: 20px;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 14px;
            font-weight: bold;
            color: #64748b;
            margin-bottom: 8px;
            margin-top: 15px;
        }

        input {
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: 0.3s;
        }

        input:focus {
            border-color: var(--primary);
        }

        .btn-save {
            margin-top: 25px;
            padding: 14px;
            background: var(--sidebar-bg);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-save:hover {
            background: #0b6e54;
        }

        /* Estilo da Zona de Perigo */
        .danger-zone {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px dashed #e2e8f0;
        }

        .danger-zone h4 {
            color: var(--danger);
            font-size: 16px;
            margin-bottom: 10px;
        }

        .danger-zone p {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 15px;
        }

        .btn-delete {
            color: var(--danger);
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: 0.3s;
        }

        .btn-delete:hover {
            text-decoration: underline;
            opacity: 0.8;
        }
    </style>
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