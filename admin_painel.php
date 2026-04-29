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
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/admin_painel.css">
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