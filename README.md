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

* ##  Como Rodar o Projeto
1. Baixe o **Laragon Portable**.
2. Coloque a pasta do projeto em laragon/www/.
3. Importe o arquivo database_setup.sql no seu MySQL via HeidiSQL.
4. Acesse localhost/nome-da-sua-pasta.
   
<img width="1918" height="867" alt="tela_useradmin" src="https://github.com/user-attachments/assets/c777ea1a-b301-4aae-b2a8-a79a37fc208d" />

<img width="1890" height="850" alt="tela_user" src="https://github.com/user-attachments/assets/e8f76453-2108-4276-8982-7568c84258aa" />

<img width="1902" height="881" alt="tela_admin" src="https://github.com/user-attachments/assets/f4a97718-be42-4f6c-96c0-b5962465458f" />


* > **Aviso de Autoria:** Todo o histórico de desenvolvimento e lógica de segurança foi registrado originalmente por Andreza Pires via GitHub Commits

---
