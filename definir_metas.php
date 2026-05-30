<?php
// Lógica simples para o cálculo
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $salario = $_POST['salario'];
    $meta = $_POST['meta'];
    $sobra = $salario - $meta;

    if ($sobra < 0) {
        $mensagem = "Cuidado! Sua meta é maior que seu salário.";
        $cor = "text-red-500";
    } else {
        $mensagem = "Boa! Você terá R$ " . number_format($sobra, 2, ',', '.') . " para passar o mês.";
        $cor = "text-green-500";
    }
}
?>

<!-- Interface da Caixinha -->
<div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-600">
    <h3 class="text-xl font-bold mb-4">💰 Meta de Economia</h3>

    <form method="POST">
        <label>Qual o seu salário este mês?</label>
        <input type="number" name="salario" step="0.01" class="w-full p-2 border rounded mb-4" required>

        <label>Quanto você quer guardar (Meta)?</label>
        <input type="number" name="meta" step="0.01" class="w-full p-2 border rounded mb-4" required>

        <button type="submit" class="bg-green-700 text-white px-4 py-2 rounded hover:bg-green-800">
            Definir Meta
        </button>
    </form>

    <?php if (isset($mensagem)): ?>
        <div class="mt-4 p-3 bg-gray-100 rounded">
            <p class="font-semibold <?php echo $cor; ?>"><?php echo $mensagem; ?></p>
        </div>
    <?php endif; ?>
</div>