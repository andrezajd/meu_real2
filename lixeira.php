<?php
session_start();
require_once __DIR__ . '/conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel_acesso'] != 1) {
    header("Location: index.php?erro=acesso_negado");
    exit();
}

// Buscar usuários com deletado = 1, incluindo data_arquivamento
$sql = "SELECT id, nome, email, nivel_acesso, data_arquivamento FROM usuarios WHERE deletado = 1 ORDER BY data_arquivamento DESC";
$result = $conn->query($sql);
$total_lixeira = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lixeira - Admin Meu Real</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/lixeira.css">
</head>
<body>
<aside class="sidebar">
    <h2><i class="fas fa-shield-halved"></i> <span>Admin Real</span></h2>
    <nav>
        <a href="index.php"><i class="fas fa-arrow-left"></i> <span>Voltar ao Site</span></a>
        <a href="admin_painel.php"><i class="fas fa-users"></i> <span>Gestão de Usuários</span></a>
        <a href="lixeira.php" class="active"><i class="fas fa-trash-can"></i> <span>Lixeira</span></a>
    </nav>
</aside>
<main class="main-content">
    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] == 'restaurado'): ?>
            <div class="toast-success"><i class="fas fa-check-circle"></i> Usuário restaurado com sucesso!</div>
        <?php elseif ($_GET['msg'] == 'excluido'): ?>
            <div class="toast-success"><i class="fas fa-check-circle"></i> Usuário excluído permanentemente!</div>
        <?php endif; ?>
    <?php endif; ?>

    <header>
        <h1><i class="fas fa-trash-alt"></i> Lixeira de Usuários</h1>
        <p>Usuários arquivados ficam aqui por <strong>30 dias</strong>. Após esse período, podem ser excluídos automaticamente.</p>
    </header>

    <!-- Aviso de 30 dias -->
    <div class="aviso-30dias">
        <i class="fas fa-hourglass-half"></i>
        <div>
            <strong>⚠️ Atenção:</strong> Usuários arquivados permanecem na lixeira por <strong>30 dias</strong>.
            Durante esse período, você pode restaurá-los. Após 30 dias, recomendamos a exclusão permanente.
            <br><small>O sistema mantém o registro de quando o usuário foi arquivado.</small>
        </div>
    </div>

    <div class="stats-cards">
        <div class="stat-card">
            <h3><i class="fas fa-trash-alt"></i> Usuários na Lixeira</h3>
            <div class="number"><?php echo $total_lixeira; ?></div>
        </div>
    </div>

    <a href="admin_painel.php" class="btn-voltar"><i class="fas fa-arrow-left"></i> Voltar para a Lista Ativa</a>

    <div class="table-container">
        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Nível</th>
                        <th>Arquivado em</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $result->fetch_assoc()): 
                        $dataArquivo = $user['data_arquivamento'];
                        $diasArquivado = $dataArquivo ? floor((time() - strtotime($dataArquivo)) / 86400) : 0;
                        $diasRestantes = max(0, 30 - $diasArquivado);
                        $expiracaoProxima = ($diasRestantes <= 7);
                    ?>
                        <tr>
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
                                <?php if ($dataArquivo): ?>
                                    <?php echo date('d/m/Y', strtotime($dataArquivo)); ?>
                                    <?php if ($expiracaoProxima): ?>
                                        <span class="badge-expira-em-breve">Expira em <?php echo $diasRestantes; ?> dias</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    Data não registrada
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="acoes/restaurar.php?id=<?php echo $user['id']; ?>" 
                                   class="btn-restaurar"
                                   onclick="return confirm('Restaurar este usuário? Ele voltará para a lista ativa.')">
                                    <i class="fas fa-undo-alt"></i> Restaurar
                                </a>
                                <a href="acoes/excluir_usuario.php?id=<?php echo $user['id']; ?>" 
                                   class="btn-excluir"
                                   onclick="return confirm('ATENÇÃO! Isso irá excluir o usuário PERMANENTEMENTE. Esta ação não pode ser desfeita. Tem certeza?')">
                                    <i class="fas fa-trash-alt"></i> Excluir Permanente
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-trash-alt"></i>
                <p>Lixeira vazia. Nenhum usuário arquivado.</p>
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