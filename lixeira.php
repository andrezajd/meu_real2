<?php
include('conexao.php');
// Buscamos apenas quem tem o status deletado = 1
$sql = "SELECT * FROM usuarios WHERE deletado = 1";
$result = $conn->query($sql);
?>

<h2>Lixeira de Usuários</h2>
<table border="1">
    <tr>
        <th>Nome</th>
        <th>E-mail</th>
        <th>Ações</th>
    </tr>
    <?php while ($user = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $user['nome']; ?></td>
            <td><?php echo $user['email']; ?></td>
            <td>
                <a href="restaurar.php?id=<?php echo $user['id']; ?>">Restaurar</a> |
                <a href="excluir_permanente.php?id=<?php echo $user['id']; ?>" onclick="return confirm('Cuidado! Isso apaga do banco para sempre.')">Excluir Permanente</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>
<br>
<a href="index.php">Voltar para a Lista Ativa</a>