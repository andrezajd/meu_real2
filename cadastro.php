<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Real - Criar Conta</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            /* Verde das métricas */
            --dark: #064e3b;
            /* Verde da Sidebar */
            --bg: #f1f5f9;
            /* Fundo cinza do site */
            --text: #1e293b;
        }

        body {
            background: var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .brand-header i {
            color: var(--primary);
            font-size: 40px;
            margin-bottom: 10px;
        }

        .brand-header h2 {
            color: var(--dark);
            margin: 0;
            font-size: 24px;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            box-sizing: border-box;
            /* Garante que o padding não estique o input */
        }

        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        button {
            width: 100%;
            padding: 12px;
            background: var(--dark);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        button:hover {
            background: #0b6e54;
            transform: translateY(-1px);
        }

        .footer-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #64748b;
        }

        .footer-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: bold;
        }
    </style>
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