<?php
include('conexao.php');
$token = $_GET['token'] ?? '';

// Valida se o token existe e ainda é válido
$res = $conn->query("SELECT * FROM usuarios WHERE token_recuperacao = '$token' AND token_expiracao > NOW()");
$usuario = $res->fetch_assoc();

if (!$usuario) {
    die("Link inválido ou expirado!");
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Nova Senha - Meu Real</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/redefinir_senha.css">
</head>

<body>
    <div class="container">
        <h3>Crie uma nova senha</h3>
        <form action="atualizar_senha_final.php" method="POST">
            <input type="hidden" name="token" value="<?php echo $token; ?>">
            <input type="password" name="nova_senha" placeholder="Nova senha" required>
            <button type="submit">ATUALIZAR SENHA</button>
        </form>
    </div>
</body>

</html>