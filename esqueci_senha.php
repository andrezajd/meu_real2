<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Recuperar Senha - Meu Real</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Use o mesmo CSS que passamos para o login.php aqui */
        :root {
            --primary: #10b981;
            --dark: #064e3b;
            --bg: #f1f5f9;
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
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 380px;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .brand-header i {
            color: var(--primary);
            font-size: 40px;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            background: var(--dark);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
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