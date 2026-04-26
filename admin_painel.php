<?php
session_start();
require_once __DIR__ . '/conexao.php';

// SEGURANÇA: Só deixa entrar se for nível 1 (Admin)
// Se você ainda não criou a coluna nivel_acesso, comente as 3 linhas abaixo para testar
if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel_acesso'] != 1) {
    header("Location: index.php?erro=acesso_negado");
    exit();
}

// Busca apenas usuários ATIVOS (deletado = 0)
$sql = "SELECT id, nome, email, nivel_acesso FROM usuarios WHERE deletado = 0";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo - Meu Real</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --admin-dark: #1e293b;
            --admin-blue: #3b82f6;
            --bg: #f8fafc;
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

        /* Sidebar Admin */
        .sidebar {
            width: 250px;
            background: var(--admin-dark);
            color: white;
            padding: 20px;
            position: fixed;
            height: 100%;
        }

        .sidebar h2 {
            font-size: 20px;
            margin-bottom: 30px;
            color: var(--admin-blue);
            text-align: center;
        }

        .sidebar nav a {
            color: #94a3b8;
            text-decoration: none;
            padding: 12px;
            display: block;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: 0.3s;
        }

        .sidebar nav a:hover {
            background: rgba(255, 255, 255, 0.05);
            color: white;
        }

        /* Conteúdo */
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 40px;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        /* Tabela */
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #f1f5f9;
            color: #64748b;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
        }

        .badge-admin {
            background: #dbeafe;
            color: #1e40af;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        .btn-archive {
            color: #ef4444;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
            padding: 6px 12px;
            border: 1px solid #ef4444;
            border-radius: 6px;
            transition: 0.3s;
        }

        .btn-archive:hover {
            background: #ef4444;
            color: white;
        }

        .btn-lixeira {
            background: #64748b;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <aside class="sidebar">
        <h2><i class="fas fa-shield-halved"></i> Admin Real</h2>
        <nav>
            <a href="index.php"><i class="fas fa-arrow-left"></i> Voltar ao Site</a>
            <a href="admin_painel.php" style="background: rgba(255,255,255,0.1); color: white;"><i class="fas fa-users"></i> Gestão de Usuários</a>
            <a href="lixeira.php"><i class="fas fa-trash-can"></i> Lixeira</a>
        </nav>
    </aside>

    <main class="main-content">
        <header>
            <h1 style="margin-bottom: 10px;">Gestão de Usuários</h1>
            <p style="color: #64748b; margin-bottom: 30px;">Gerencie quem tem acesso ao sistema e monitore contas ativas.</p>
        </header>

        <a href="lixeira.php" class="btn-lixeira"><i class="fas fa-trash"></i> Ver Usuários Desativados</a>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Nível</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($user = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $user['id']; ?></td>
                                <td><strong><?php echo $user['nome']; ?></strong></td>
                                <td><?php echo $user['email']; ?></td>
                                <td>
                                    <?php echo ($user['nivel_acesso'] == 1) ? '<span class="badge-admin">Administrador</span>' : 'Usuário'; ?>
                                </td>
                                <td>
                                    <a href="acoes/arquivar_conta.php?id=<?php echo $user['id']; ?>"
                                        class="btn-archive"
                                        onclick="return confirm('Tem certeza que deseja enviar este usuário para a lixeira?')">
                                        Arquivar
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">Nenhum usuário ativo encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>

</html>