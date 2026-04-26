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
    <style>
        /* Reutilizando o CSS do Index */
        :root {
            --primary: #10b981;
            --bg: #f0fdf4;
            --sidebar-bg: #064e3b;
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

        .sidebar {
            width: 230px;
            background: var(--sidebar-bg);
            color: white;
            padding: 15px;
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
            display: block;
            border-radius: 6px;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .sidebar nav a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .main {
            margin-left: 230px;
            flex: 1;
            padding: 40px;
        }

        .card-tabela {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        h2 {
            margin-bottom: 20px;
            color: #1e293b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 15px;
            color: #64748b;
            border-bottom: 2px solid #f1f5f9;
            text-transform: uppercase;
            font-size: 12px;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .valor-entrada {
            color: #10b981;
            font-weight: bold;
        }

        .valor-saida {
            color: #be123c;
            font-weight: bold;
        }
    </style>
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