<?php
session_start();
require_once __DIR__ . '/conexao.php';

// Verifica login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $conn->real_escape_string($_SESSION['usuario_id']);

$descricao = $conn->real_escape_string($_POST['descricao']);

$valor = $conn->real_escape_string($_POST['valor']);

$categoria = $conn->real_escape_string($_POST['categoria']);

$data = $conn->real_escape_string($_POST['data']);

// BUSCA CONFIGURAÇÕES DO CARTÃO
$dadosUsuario = $conn->query("
    SELECT fechamento_cartao
    FROM usuarios
    WHERE id = '$id_usuario'
")->fetch_assoc();

$fechamento = (int)$dadosUsuario['fechamento_cartao'];

// DATA DA COMPRA
$dataCompra = new DateTime($data);

$diaCompra = (int)$dataCompra->format('d');

// DEFINE A FATURA
if ($categoria == 'Cartão') {

    // Se comprou depois do fechamento
    // joga pra próxima fatura
    if ($diaCompra > $fechamento) {

        $dataCompra->modify('+1 month');
    }

    $fatura_mes = $dataCompra->format('Y-m');

} else {

    $fatura_mes = null;
}

// INSERT
$sql = "
INSERT INTO transacoes
(usuario_id, descricao, valor, categoria, data, fatura_mes)

VALUES
(
'$id_usuario',
'$descricao',
'$valor',
'$categoria',
'$data',
" . ($fatura_mes ? "'$fatura_mes'" : "NULL") . "
)
";

// SALVA
if ($conn->query($sql)) {

    header("Location: index.php?sucesso=1");
    exit();

} else {

    echo 'Erro ao salvar: ' . $conn->error;
}
?>