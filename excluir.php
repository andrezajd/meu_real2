<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
?>

<?php
// include('conexao.php');
require_once __DIR__ . '/conexao.php';

// Pega o ID que foi enviado pelo link
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Deleta a linha específica
    $sql = "DELETE FROM transacoes WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        header("Location: index.php");
    } else {
        echo "Erro ao excluir: " . $conn->error;
    }
}
?>