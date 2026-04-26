<?php
session_start();

// include('conexao.php');
require_once __DIR__ . '/conexao.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. LIMPEZA DE SEGURANÇA NO E-MAIL
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $senha_digitada = $_POST['senha']; // Senha "limpa", sem MD5 aqui

    // 2. BUSCAMOS O USUÁRIO PELO E-MAIL
    // $sql = "SELECT * FROM usuarios WHERE email = '$email'";
    $sql = "SELECT * FROM usuarios WHERE email = '$email' AND deletado = 0 LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $usuario = $result->fetch_assoc();

        // 3. VERIFICAÇÃO MODERNA (password_verify)
        // Esta função sabe comparar a senha pura com o hash seguro do banco
        if (password_verify($senha_digitada, $usuario['senha'])) {

            // Cria a sessão para o usuário
            // $_SESSION['usuario_id'] = $usuario['id'];
            // $_SESSION['usuario_nome'] = $usuario['nome'];
            // Dentro do seu processa_login.php, onde você cria as sessões:
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['nivel_acesso'] = $usuario['nivel_acesso']; // ADICIONE ESTA LINHA

            // ESTA É A LINHA QUE ESTÁ FALTANDO:
            $_SESSION['nivel_acesso'] = $usuario['nivel_acesso'];

            // Sucesso! Vai para o index
            header("Location: /meu_real2/index.php");
            exit();
        } else {
            // Senha não bateu com o hash
            echo "<script>alert('Senha incorreta!'); window.location='login.php';</script>";
        }
    } else {
        // E-mail não existe na tabela
        echo "<script>alert('Usuário não encontrado!'); window.location='login.php';</script>";
    }
}
