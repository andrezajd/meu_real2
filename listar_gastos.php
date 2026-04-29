<?php
// include 'conexao.php';
require_once __DIR__ . '/conexao.php';

// Busca todos os gastos do banco de dados
$sql = "SELECT * FROM transacoes ORDER BY data DESC";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Meus Gastos</title>
    <link rel="stylesheet" href="styles/listar_gastos.css">
</head>

<body>

    <h2 style="text-align:center;">Histórico de Gastos</h2>

    <table>
        <thead>
            <tr>
                <th>Descrição</th>
                <th>Valor</th>
                <th>Categoria</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($resultado->num_rows > 0) {
                while ($linha = $resultado->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $linha['descricao'] . "</td>";
                    echo "<td>R$ " . number_format($linha['valor'], 2, ',', '.') . "</td>";
                    echo "<td>" . $linha['categoria'] . "</td>";
                    echo "<td>" . date('d/m/Y', strtotime($linha['data'])) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>Nenhum gasto encontrado.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <p style="text-align:center;"><a href="index.php">Voltar para o Início</a></p>

</body>

</html>