<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Recuperar Senha - Meu Real</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/esqueci_senha.css">
</head>

<body>
    <div class="container">
        <div class="brand-header">
            <i class="fas fa-lock-open"></i>
            <h2>Recuperar Senha</h2>
            <p style="font-size: 14px; color: #64748b;">Insira seu e-mail para redefinir sua senha.</p>
        </div>

        <form action="processa_recuperacao.php" method="POST">
            <input type="email" name="email" placeholder="Seu e-mail cadastrado" required>
            <button type="submit">ENVIAR LINK DE RECUPERAÇÃO</button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="login.php" style="color: var(--primary); text-decoration: none; font-size: 14px;">Voltar para o Login</a>
        </div>
    </div>
</body>

</html>