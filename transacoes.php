<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// include('conexao.php');
require_once __DIR__ . '/conexao.php';


// LIMPEZA DE SEGURANÇA (Dentro do PHP e antes de usar nas queries)
$id_usuario   = $conn->real_escape_string($_SESSION['usuario_id']);
$nome_usuario = $conn->real_escape_string($_SESSION['usuario_nome']);

// BUSCA AMPLIADA: Sem o LIMIT 5 para mostrar tudo
$lista = $conn->query("SELECT * FROM transacoes WHERE usuario_id = '$id_usuario' ORDER BY data DESC");
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Meu Real - Extrato Completo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/transacoes.css">
</head>

<body>

    <aside class="sidebar">
        <div class="brand"><i class="fas fa-wallet"></i> Meu Real</div>
        <nav>
            <a href="index.php"><i class="fas fa-home"></i> Início</a>
            <a href="transacoes.php" style="background: rgba(255,255,255,0.1); color: white;"><i class="fas fa-exchange-alt"></i> Transações</a>
            <a href="perfil.php"><i class="fas fa-user-gear"></i> Perfil</a>
            <a href="logout.php" style="margin-top: 20px; color: #f87171;"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </nav>
    </aside>

    <main class="main">
        <div class="card-tabela">
            <h2><i class="fas fa-list"></i> Histórico de Transações</h2>
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Descrição</th>
                        <th>Categoria</th>
                        <th>Valor</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $lista->fetch_assoc()): ?>
                        <?php $classeCor = ($row['categoria'] == 'Saldo') ? 'valor-entrada' : 'valor-saida'; ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($row['data'])); ?></td>
                            <td><strong><?php echo $row['descricao']; ?></strong></td>
                            <td><?php echo $row['categoria']; ?></td>
                            <td class="<?php echo $classeCor; ?>">
                                R$ <?php echo number_format($row['valor'], 2, ',', '.'); ?>
                            </td>
                            <td>
                                <a href="excluir.php?id=<?php echo $row['id']; ?>" style="color: #be123c;"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>

</html>