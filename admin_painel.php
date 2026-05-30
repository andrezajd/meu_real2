<?php
session_start();
require_once __DIR__ . '/conexao.php';

// SEGURANÇA: Só deixa entrar se for nível 1 (Admin)
if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel_acesso'] != 1) {
    header("Location: index.php?erro=acesso_negado");
    exit();
}

// Busca apenas usuários ATIVOS (deletado = 0)
$sql = "SELECT id, nome, email, nivel_acesso FROM usuarios WHERE deletado = 0 ORDER BY id DESC";
$result = $conn->query($sql);

// Conta estatísticas
$total_usuarios = $result->num_rows;
$total_admin = 0;
$total_comuns = 0;

if ($total_usuarios > 0) {
    $result->data_seek(0);
    while ($user = $result->fetch_assoc()) {
        if ($user['nivel_acesso'] == 1) {
            $total_admin++;
        } else {
            $total_comuns++;
        }
    }
    $result->data_seek(0);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Meu Real</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin_painel.css">
    <link rel="stylesheet" href="css/index.css">
    
</head>
<body>

<aside class="sidebar">
    <h2><i class="fas fa-shield-halved"></i> <span>Admin Real</span></h2>
    <nav>
        <a href="index.php"><i class="fas fa-arrow-left"></i> <span>Voltar ao Site</span></a>
        <a href="admin_painel.php" class="active"><i class="fas fa-users"></i> <span>Gestão de Usuários</span></a>
        <a href="lixeira.php"><i class="fas fa-trash-can"></i> <span>Lixeira</span></a>
    </nav>
</aside>

<main class="main-content">
    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] == 'arquivado'): ?>
            <div class="toast-success"><i class="fas fa-check-circle"></i> Usuário arquivado com sucesso!</div>
        <?php elseif ($_GET['msg'] == 'restaurado'): ?>
            <div class="toast-success"><i class="fas fa-check-circle"></i> Usuário restaurado com sucesso!</div>
        <?php endif; ?>
    <?php endif; ?>

    <header>
        <h1><i class="fas fa-users"></i> Gestão de Usuários</h1>
        <p>Gerencie quem tem acesso ao sistema e monitore contas ativas.</p>
    </header>

    <!-- Cards de estatísticas -->
    <div class="stats-cards">
        <div class="stat-card">
            <h3><i class="fas fa-users"></i> Total de Usuários</h3>
            <div class="number"><?php echo $total_usuarios; ?></div>
        </div>
        <div class="stat-card">
            <h3><i class="fas fa-user-shield"></i> Administradores</h3>
            <div class="number"><?php echo $total_admin; ?></div>
        </div>
        <div class="stat-card">
            <h3><i class="fas fa-user"></i> Usuários Comuns</h3>
            <div class="number"><?php echo $total_comuns; ?></div>
        </div>
    </div>

    <a href="lixeira.php" class="btn-lixeira"><i class="fas fa-trash-alt"></i> Ver Usuários Desativados</a>

    <div class="table-container">
        <h3><i class="fas fa-user-check"></i> Lista de Usuários Ativos</h3>
        
        <?php if ($total_usuarios > 0): ?>
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
                    <?php while ($user = $result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $user['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($user['nome']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <?php if ($user['nivel_acesso'] == 1): ?>
                                    <span class="badge-admin"><i class="fas fa-shield-alt"></i> Administrador</span>
                                <?php else: ?>
                                    <span class="badge-user"><i class="fas fa-user"></i> Usuário</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['id'] != $_SESSION['usuario_id']): ?>
                                    <a href="acoes/arquivar_conta.php?id=<?php echo $user['id']; ?>"
                                        class="btn-archive"
                                        onclick="return confirm('Tem certeza que deseja enviar este usuário para a lixeira?')">
                                        <i class="fas fa-archive"></i> Arquivar
                                    </a>
                                <?php else: ?>
                                    <span style="color: #94a3b8; font-size: 12px;"><i class="fas fa-user-check"></i> (Você)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <p>Nenhum usuário ativo encontrado.</p>
                <small style="color: var(--gray-400);">Cadastre novos usuários para aparecerem aqui.</small>
            </div>
        <?php endif; ?>
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