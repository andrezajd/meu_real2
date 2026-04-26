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
    <style>
        /* Use o mesmo estilo CSS do seu Login/Cadastro que criamos antes */
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
            font-family: 'Segoe UI', sans-serif;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            width: 350px;
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
        <h3>Crie uma nova senha</h3>
        <form action="atualizar_senha_final.php" method="POST">
            <input type="hidden" name="token" value="<?php echo $token; ?>">
            <input type="password" name="nova_senha" placeholder="Nova senha" required>
            <button type="submit">ATUALIZAR SENHA</button>
        </form>
    </div>
</body>

</html>