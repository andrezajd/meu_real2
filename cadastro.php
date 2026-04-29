<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Real - Criar Conta</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/cadastro.css">
</head>

<body>

    <div class="container">
        <div class="brand-header">
            <i class="fas fa-wallet"></i>
            <h2>Criar Conta</h2>
            <p style="font-size: 14px; color: #64748b;">Comece a organizar suas finanças hoje mesmo.</p>
        </div>
        <?php if (isset($_GET['erro']) && $_GET['erro'] == 'email_existe'): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; border: 1px solid #f87171; font-size: 14px; font-weight: 600;">
                <i class="fas fa-exclamation-triangle"></i> Ops! Este e-mail já está cadastrado.
                <br><span style="font-weight: normal; font-size: 12px;">Tente usar outro ou faça login.</span>
            </div>
        <?php endif; ?>
        <form action="processa_cadastro.php" method="POST">
            <div class="form-group">
                <label>Nome Completo</label>
                <input type="text" name="nome" placeholder="Ex: João Silva" required>
            </div>

            <div class="form-group">
                <label>E-mail</label>
                <input type="email" name="email" placeholder="seu@email.com" required>
            </div>

            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="senha" placeholder="Crie uma senha forte" required>
            </div>

            <button type="submit">CRIAR MINHA CONTA</button>
        </form>

        <div class="footer-link">
            Já tem uma conta? <a href="login.php">Entrar agora</a>
        </div>
    </div>

</body>

</html>