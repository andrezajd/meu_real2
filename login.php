<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Meu Real</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/login.css">
    <div class="container">
        <div class="brand-header">
            <i class="fas fa-wallet"></i>
            <h2>Meu Real</h2>
            <p style="font-size: 14px; color: #64748b;">Faça login para gerenciar suas finanças</p>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'sucesso'): ?>
            <div class="alert-sucesso">
                <i class="fas fa-check-circle"></i> Conta criada! Pode logar agora.
            </div>
        <?php endif; ?>

        <form action="processa_login.php" method="POST">
            <div class="form-group">
                <label>E-mail</label>
                <input type="email" name="email" required placeholder="Digite seu e-mail">
            </div>
            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="senha" required placeholder="Digite sua senha">
            </div>
            <div style="text-align: right; margin-bottom: 15px;">
                <a href="esqueci_senha.php" style="font-size: 12px; color: #64748b; text-decoration: none;">Esqueceu a senha?</a>
            </div>
            <button type="submit">ENTRAR</button>
        </form>

        <div class="footer-link">
            Ainda não tem conta? <a href="cadastro.php">Criar agora</a>
        </div>
    </div>



    </body>

</html>