Projeto [BSYS] - Servidor Auxiliar de Processos (Pferd Brasil)

Este repositório contém o código-fonte do Projeto [BSYS], uma aplicação Laravel destinada a servir como "app auxiliar" para a automação de processos internos e melhoria de workflows na Pferd Brasil.

Este projeto é a reformulação técnica do conceito original (pfsyst), construído sobre uma infraestrutura moderna, segura e auditável (o servidor pfServer).

Autor: Luiz Cesar (Salah Al Din)
Status: Em Desenvolvimento (Refatoração)
Repositório: https://github.com/eliphaslevii/pfsys.git

1. Stack Tecnológico

Esta aplicação roda sobre uma infraestrutura definida e provisionada em pfServer (Ubuntu 22.04 LTS).

O stack de software é:

Servidor OS: Ubuntu Server 22.04 LTS (Headless)

Web Server: Nginx

Banco de Dados: MySQL Server 8.0

PHP: PHP 8.2-FPM (via ppa:ondrej/php)

Framework: Laravel (v11+)

Firewall: UFW (Uncomplicated Firewall)

2. Workflow de Desenvolvimento

O desenvolvimento neste projeto segue um workflow padrão para garantir a segurança e a integridade do código.

Acesso ao Ambiente

O ambiente de desenvolvimento (pfServer) não é acessado por IP (frágil). O acesso é feito por nome (abstração) para garantir portabilidade.

PC Host (Windows/Linux): O arquivo hosts local é editado para mapear o IP da VM:

192.168.0.96   bsys.local (Exemplo)

Acesso ao Site: O site é acessado localmente via:

http://bsys.local

Edição e Código

O código é editado remotamente, não diretamente no terminal do servidor.

Editor: Visual Studio Code (VS Code) no PC Host.

Conexão: Extensão "Remote - SSH" (Microsoft).

Workflow: O VS Code conecta-se via SSH ao pfServer e abre a pasta /var/www/bsys diretamente, unindo o poder do editor local com os arquivos no servidor.

Versionamento

O código é versionado no GitHub (pfsys.git) usando um Personal Access Token (PAT) com permissão repo.

Segredos: O arquivo .env (credenciais) é bloqueado pelo .gitignore.

Dependências: A pasta vendor (pacotes do Composer) é bloqueada pelo .gitignore.

3. Scripts de Provisionamento (Infra-as-Code)

A infraestrutura deste servidor é documentada e gerenciada por scripts de provisionamento localizados no diretório home (~) do pfServer. Eles garantem que a infraestrutura seja replicável e auditável.

provision.sh: Provisiona o Ubuntu com Nginx, MySQL, PHP 8.2, UFW e Git.

setup_database.sh: Script interativo e seguro para criar novos bancos de dados e usuários MySQL (sem armazenar senhas).

create_site.sh: Script interativo para criar e ativar novos server blocks (sites) no Nginx, testando a sintaxe (nginx -t) e fazendo rollback em caso de falha.

fix_permissions.sh: Script interativo para aplicar as permissões de arquivo do Laravel (chown $USER, chgrp www-data, chmod ug+rwx storage).
