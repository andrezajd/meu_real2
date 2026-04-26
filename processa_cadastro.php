<?php
session_start();
include('conexao.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome  = mysqli_real_escape_string($conn, $_POST['nome']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // 1. VERIFICAÇÃO: O e-mail já existe?
    $checkEmail = $conn->query("SELECT id FROM usuarios WHERE email = '$email'");

    if ($checkEmail->num_rows > 0) {
        // Se encontrou alguém, para tudo e volta para a tela de cadastro com um erro
        header("Location: cadastro.php?erro=email_existe");
        exit();
    }

    // 2. CRIPTOGRAFIA
    $senha_pura = $_POST['senha'];
    $senha_cripto = password_hash($senha_pura, PASSWORD_ARGON2ID);

    // 3. INSERÇÃO (Só acontece se o e-mail for novo)
    $sql = "INSERT INTO usuarios (nome, email, senha) VALUES ('$nome', '$email', '$senha_cripto')";

    if ($conn->query($sql) === TRUE) {
        header("Location: login.php?msg=sucesso");
        exit();
    } else {
        // Caso ocorra algum outro erro no banco
        echo "Erro ao cadastrar: " . $conn->error;
    }

    $conn->close();
}
