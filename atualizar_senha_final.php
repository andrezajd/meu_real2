<?php
include('conexao.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];
    $nova_senha = password_hash($_POST['nova_senha'], PASSWORD_ARGON2ID);

    // Atualiza a senha e apaga o token para ele não ser usado de novo
    $sql = "UPDATE usuarios SET senha = '$nova_senha', token_recuperacao = NULL, token_expiracao = NULL WHERE token_recuperacao = '$token'";

    if ($conn->query($sql)) {
        header("Location: login.php?msg=senha_atualizada");
    } else {
        echo "Erro ao atualizar.";
    }
}
?>