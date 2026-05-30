# meu_real2<img width="1921" height="1370" alt="Meu Real - Dashboard novo" src="https://github.com/user-attachments/assets/b85316de-2dfe-4802-a1a0-e8c2b345f5f3" />

Site para controle de gastos pessoal.

#  Projeto Meu Real

Sistema de gestão financeira pessoal leve, seguro e portátil, desenvolvido para facilitar o controle de gastos e a organização financeira do utilizador comum.



<img width="1921" height="1604" alt="MeuReal" src="https://github.com/user-attachments/assets/a8893791-9195-4728-bac1-b9d7502339d0" />

<img width="1921" height="1111" alt="MeuReal - Extrato Completo" src="https://github.com/user-attachments/assets/3500eb8a-214c-4e1e-be39-d7c99e2b0d35" />

<img width="1921" height="1500" alt="MeuReal - Meu Perfil" src="https://github.com/user-attachments/assets/9810c8df-e16d-4bed-99d8-f4fdddefc35e" />


---

##  Segurança e Autenticação
* **Criptografia de Senhas:** Implementação do algoritmo `Argon2id` via `password_hash`, o padrão mais robusto contra ataques de força bruta.
* **Níveis de Permissão:** Sistema hierárquico diferenciando **Utilizadores Comuns** de **Administradores** (Nível 1).
* **Proteção de Rotas:** Validação de `session_id` em todas as páginas internas para impedir acessos não autorizados.
* **Soft Delete:** Contas "eliminadas" são arquivadas para auditoria, mas perdem acesso imediato ao login.

##  Funcionalidades Principais
* **Registo Inteligente:** Validação em tempo real de e-mails duplicados para garantir a integridade do banco de dados.
* **Recuperação de Senha:** Fluxo completo de "Esqueci minha senha" com geração de **tokens temporários** seguros.
* **Painel Administrativo:** Interface exclusiva para gestão de utilizadores ativos e acesso à lixeira de contas arquivadas.
* **Feedback Visual:** Alertas dinâmicos para login, logout, erros de acesso e sucesso em operações.

##  Interface (UI/UX)
* **Paleta de Cores:** Focada em seriedade e clareza, utilizando Verde Escuro (`#064e3b`) e fundos suaves (`#f1f5f9`).
* **Design Moderno:** Cards informativos, tabelas limpas e botões intuitivos com efeitos *hover*.

##  Portabilidade e Tech Stack
* **Ambiente:** Totalmente compatível com **Laragon Portable** (corre diretamente de uma pen-drive).
* **Backend:** PHP 8.x com extensões `mysqli`.
* **Banco de Dados:** MySQL gerenciado via HeidiSQL.
* **Frontend:** HTML5, CSS3 e FontAwesome 6.

* ##  Como Rodar o Projeto
1. Baixe o **Laragon Portable**.
2. Coloque a pasta do projeto em laragon/www/.
3. Importe o arquivo database_setup.sql no seu MySQL via HeidiSQL.
4. Acesse localhost/nome-da-sua-pasta.
   


==========================================================================================

Modificações feitas no sistema Meu Real

1. CORREÇÃO DO CÁLCULO DO DISPONÍVEL REAL

Problema: O sistema estava descontando o valor do cartão do saldo disponível, como se fosse débito.

Correção:
php

// ANTES (ERRADO)
$disponivelReal = $valorSaldo - ($valorGastos + $valorCartao + $valorMeta);

// DEPOIS (CORRETO)
$disponivelReal = $valorSaldo - ($valorGastos + $valorMeta);
// O cartão NÃO desconta do disponível

2. IMPLEMENTAÇÃO DO CONTROLE DE LIMITE DO CARTÃO

Problema: O sistema permitia gastar acima do limite do cartão.

Correção no salvar_gasto.php:
php

// Verificação antes de salvar
if ($categoria == 'Cartão') {
    $novo_total = $total_gasto + $valor;
    if ($novo_total > $limite_cartao) {
        $_SESSION['erro'] = "❌ Limite do cartão excedido!";
        header("Location: index.php");
        exit();
    }
}

Correção no formulário (select desabilitado):
php

<?php if ($limiteDisponivel <= 0): ?>
    <option value="Cartão" disabled>
        🚫 Cartão (Limite esgotado)
    </option>
<?php else: ?>
    <option value="Cartão">
         Cartão (Disponível: R$ <?php echo $limiteDisponivel; ?>)
    </option>
<?php endif; ?>

3. SISTEMA DE FATURAS POR COMPETÊNCIA

Implementação da lógica de faturamento:
php

// Calcula em qual fatura a compra vai cair
$dataCompra = new DateTime($data);
$diaCompra = (int)$dataCompra->format('d');

if ($diaCompra > $fechamento) {
    $dataCompra->modify('+1 month'); // Vai para próxima fatura
}
$fatura_mes = $dataCompra->format('Y-m');

Nova página fatura.php:

    Exibe fatura atual do mês

    Mostra histórico de faturas

    Botão para pagar fatura

4. CRIAÇÃO DA TABELA DE TRANSAÇÕES

Arquivo transacoes.php implementado com:

    Listagem de todas transações do mês

    Filtros por categoria

    Busca por descrição

    Botão de excluir com confirmação

    Cards de resumo (entradas, gastos, cartão, disponível)

5. SISTEMA ADMINISTRATIVO (PAINEL ADMIN)

Soft Delete implementado:
sql

-- Arquivar (soft delete)
UPDATE usuarios SET deletado = 1, data_arquivamento = NOW() WHERE id = $id;

-- Restaurar
UPDATE usuarios SET deletado = 0, data_arquivamento = NULL WHERE id = $id;

-- Excluir permanente
DELETE FROM usuarios WHERE id = $id AND deletado = 1;

Novas páginas:

    admin_painel.php - Lista usuários ativos

    lixeira.php - Usuários arquivados

    restaurar.php - Restaura usuário

    excluir_permanente.php - Remove definitivamente

6. CORREÇÃO DO GRÁFICO (CHART.JS)

Problema: Gráfico estava "caindo" ou não aparecendo.

Correção:
html

<!-- Container com altura fixa -->
<div style="width:100%; max-width:280px; height:250px;">
    <canvas id="myChart"></canvas>
</div>

javascript

// Aguarda carregamento completo
window.addEventListener('load', function() {
    new Chart(ctx, { ... });
});

7. RESPONSIVIDADE

CSS implementado para:

    Tablet (768px - 1024px): Sidebar reduzida

    Celular (até 768px): Sidebar horizontal no topo

    Cards: 4 colunas → 2 colunas → 1 coluna

css

@media (max-width: 1024px) { /* Tablet */ }
@media (max-width: 768px)  { /* Celular */ }
@media (max-width: 640px)  { /* Mobile pequeno */ }

8. CORREÇÃO DO SELECT DO CARTÃO

Problema: Select permitia selecionar cartão mesmo com limite esgotado.

Correção:
php

// Busca limite atualizado
$limite_total = (float)($usuario['limite_cartao'] ?? 0);
$disponivel = $limite_total - $valorCartao;

if ($disponivel <= 0) {
    // Desabilita o select
    echo '<option value="Cartão" disabled>🚫 Cartão (esgotado)</option>';
} else {
    echo '<option value="Cartão">💳 Cartão (R$ ' . $disponivel . ')</option>';
}

9. PERFIL DO USUÁRIO

Campos implementados:

    Editar nome e e-mail

    Alterar senha (com confirmação)

    Definir limite do cartão

    Definir dia de fechamento da fatura

    Excluir conta (com confirmação)

10. DASHBOARD

Cards implementados:
Card	Cor	Fórmula
Disponível	Verde	Entradas - Gastos - Meta
Gastos	Vermelho	Soma dos gastos débito
Cartão	Azul	Soma dos gastos cartão
Meta	Laranja	Soma das reservas

Gráfico de pizza mostra:

    Saldo Livre

    Gastos (Débito)

    Cartão (Fatura)

    Reserva (Meta)

11. ESTRUTURA DO BANCO DE DADOS

Campo fatura_mes adicionado:
sql

ALTER TABLE transacoes ADD COLUMN fatura_mes VARCHAR(7) DEFAULT NULL;

Campo status adicionado:
sql

ALTER TABLE transacoes ADD COLUMN status VARCHAR(20) DEFAULT 'pendente';

Campo data_arquivamento adicionado:
sql

ALTER TABLE usuarios ADD COLUMN data_arquivamento DATETIME DEFAULT NULL;

12. CORREÇÃO DO DEBUG



RESUMO DOS ARQUIVOS CRIADOS/MODIFICADOS
Arquivo	Tipo	Descrição
index.php	Modificado	Cálculos corrigidos, gráfico, select cartão
salvar_gasto.php	Modificado	Validação de limite do cartão
transacoes.php	Criado	Listagem de transações
fatura.php	Criado	Controle de faturas
perfil.php	Modificado	Edição de dados e limite
admin_painel.php	Modificado	Gestão de usuários
lixeira.php	Criado	Usuários arquivados
restaurar.php	Criado	Restaura usuário
excluir_permanente.php	Criado	Exclusão definitiva
✅ STATUS DO SISTEMA
Funcionalidade	Status
Login/Cadastro
Dashboard com gráfico
Lançar transações	
Limite do cartão	
Bloqueio de limite	
Faturas	
Perfil	
Painel Admin	
Lixeira	
Responsividade	

Este é o resumo completo de todas as modificações realizadas no sistema Meu Real! 


* > **Aviso de Autoria:** Todo o histórico de desenvolvimento e lógica de segurança foi registrado originalmente por Andreza Pires via GitHub Commits

---
