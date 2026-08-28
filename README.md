# Sistema de Gestão de Lavandaria

Projeto académico desenvolvido em PHP, PDO e MySQL, com arquitetura MVC.

## Requisitos

- XAMPP com PHP 8.1 ou superior;
- Apache com `mod_rewrite` ativo;
- MySQL;

## Instalação

1. Copie a pasta `gestao_lavandaria` para `C:\xampp\htdocs`.
2. Inicie o Apache e o MySQL no XAMPP.
3. Abra `http://localhost/phpmyadmin`.
4. Importe `database/lavandaria_db.sql`.
5. Abra `http://localhost/gestao_lavandaria/public`.
6. Preencha o formulário para criar o primeiro administrador.

## Configuração da base de dados

As credenciais encontram-se em `config/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestao_lavandaria');
define('DB_USER', 'root');
define('DB_PASS', '');
```

## Estrutura MVC

- `app/Controllers`: recebe e valida as requisições;
- `app/Models`: consultas e regras ligadas aos dados;
- `app/Views`: páginas apresentadas ao utilizador;
- `app/Core`: roteamento, sessão, ligação PDO e controller base;
- `app/Middleware`: proteção de rotas;
- `config`: configurações;
- `database`: script SQL;
- `public`: ponto de entrada e recursos públicos.

## Perfis e permissões

- **Administrador:** acesso completo, incluindo utilizadores, logs e backups;
- **Gestor:** operações, catálogo, dashboard e relatórios;
- **Atendente:** clientes, serviços, pagamentos e entregas.

## Regras de negócio principais

- O preço é copiado para `itens_servico` no momento do registo. Alterar o
  catálogo não modifica serviços antigos;
- Um serviço deve possuir pelo menos uma peça;
- O desconto não pode ultrapassar o subtotal;
- As mudanças de estado seguem o fluxo definido pela aplicação;
- Não é possível cancelar um serviço com pagamentos confirmados;
- Apenas serviços prontos e totalmente pagos podem ser entregues;
- Clientes e utilizadores são desativados em vez de apagados fisicamente;
- O sistema deve manter pelo menos um administrador ativo.

## Segurança implementada

- Consultas preparadas com PDO;
- `password_hash()` e `password_verify()`;
- Token CSRF;
- Regeneração do identificador da sessão;
- Cookies `HttpOnly` e `SameSite=Lax`;
- Escape de saída contra XSS;
- Bloqueio após cinco tentativas falhadas;
- Expiração da sessão após trinta minutos de inatividade;
- Controlo de acesso por perfil;
- Proteção das pastas internas por `.htaccess`;
- Auditoria das operações críticas.

## Fluxo de teste recomendado

1. Crie o primeiro administrador;
2. Cadastre um cliente;
3. Confirme os preços no Catálogo;
4. Registe um serviço com duas ou mais peças;
5. Avance o serviço por lavagem, secagem, engomagem e pronto;
6. Registe o pagamento;
7. Registe a entrega;
8. Consulte o dashboard, o relatório PDF e os logs;
9. Crie e descarregue um backup.
