# meu_real2
Site para controle de gastos pessoal.

#  Projeto Meu Real

Sistema de gestão financeira pessoal leve, seguro e portátil, desenvolvido para facilitar o controle de gastos e a organização financeira do utilizador comum.

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

---
