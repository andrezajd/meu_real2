<?php
include('conexao.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);

    // Verifica se o e-mail existe e não está deletado
    $res = $conn->query("SELECT id FROM usuarios WHERE email = '$email' AND deletado = 0");

    if ($res->num_rows > 0) {
        $token = bin2hex(random_bytes(20)); // Gera um código aleatório
        $expira = date("Y-m-d H:i:s", strtotime('+1 hour')); // Vale por 1 hora

        // Salva o token no banco para validar depois
        $conn->query("UPDATE usuarios SET token_recuperacao = '$token', token_expiracao = '$expira' WHERE email = '$email'");

        // SIMULAÇÃO: Em vez de enviar e-mail, mostramos o link na tela para você testar
        echo "
        <div style='font-family:sans-serif; text-align:center; padding:50px;'>
            <h2>Simulação de E-mail Enviado</h2>
            <p>Em um servidor real, o usuário receberia este link por e-mail:</p>
            <a href='redefinir_senha.php?token=$token' style='color:green; font-weight:bold;'>Clique aqui para redefinir sua senha</a>
            <br><br>
            <a href='login.php'>Voltar ao início</a>
        </div>";
    } else {
        header("Location: esqueci_senha.php?erro=nao_encontrado");
    }
}
