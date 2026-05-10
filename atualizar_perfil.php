<?php
session_start();
require_once __DIR__ . '/conexao.php';

$id = $_SESSION['usuario_id'];

$novo_nome = $_POST['nome'];

$nova_senha = $_POST['nova_senha'];

$vencimento = $_POST['vencimento_cartao'];

$fechamento = $_POST['fechamento_cartao'];

$limite = $_POST['limite_cartao'];

if (!empty($nova_senha)) {

    $sql = "
    UPDATE usuarios SET

    nome = '$novo_nome',

    senha = '$nova_senha',

    vencimento_cartao = '$vencimento',

    fechamento_cartao = '$fechamento',

    limite_cartao = '$limite'

    WHERE id = '$id'
    ";

} else {

    $sql = "
    UPDATE usuarios SET

    nome = '$novo_nome',

    vencimento_cartao = '$vencimento',

    fechamento_cartao = '$fechamento',

    limite_cartao = '$limite'

    WHERE id = '$id'
    ";
}

if ($conn->query($sql)) {

    $_SESSION['usuario_nome'] = $novo_nome;

    header("Location: perfil.php?sucesso=1");

} else {

    echo "Erro: " . $conn->error;
}
?>