# CRM Premium + Lead Capture (PHP/MySQL)

Sistema completo de captura de leads com Quiz Typeform e CRM Kanban com classificação via IA (Gemini).

## 🚀 Instalação

### 1. Requisitos
- Servidor Web (Apache/Nginx/IIS) com PHP 8.0+
- Banco de Dados MySQL/MariaDB
- Extensão `php-curl` habilitada
- Chave de API do Google Gemini

### 2. Configuração do Banco de Dados
1. Crie um banco de dados chamado `crm_antigravity` (ou outro nome de sua preferência).
2. Importe o arquivo `database.sql` para criar a tabela `leads`.

### 3. Configuração do Sistema
Edite o arquivo `config.php`:

```php
// Banco de Dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'crm_antigravity');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');

// API Gemini
define('GEMINI_API_KEY', 'SUA CHAVE API AQUI');

// URL Base (Importante para redirecionamentos)
define('BASE_URL', 'http://localhost/caminho/do/projeto');
```

### 4. Acesso ao Admin
- **URL**: `/admin`
- **Usuário Padrão**: `admin`
- **Senha Padrão**: `admin123` (Alterar em `includes/auth.php` ou criar sistema de usuários se desejar expandir).

## 📂 Estrutura de Pastas

- `api/`: Endpoints para receber dados do Quiz e atualizar Kanban.
- `admin/`: Painel administrativo (Kanban, Login, Detalhes).
- `includes/`: Lógica de conexão DB, Auth e IA.
- `assets/`: Arquivos estáticos (se necessário).
- `index.php`: Página do Quiz (Pública).

## 🤖 Como funciona a IA

Ao enviar o quiz, o arquivo `api/new-lead.php` envia os dados para o Gemini, que retorna:
- Categorização de Faturamento
- Score de Potencial (0-100)
- Tags de Insights
- Nível de Urgência

Dependendo do faturamento classificado, o lead cai automaticamente na coluna correta do Kanban (Cold, Morno, Quente, Ultra Quente).

## 🎨 Personalização

O visual utiliza Tailwind CSS via CDN. Para alterar cores:
1. Edite o `script` de configuração do Tailwind no `<head>` do `index.php` e `admin/kanban.php`.
2. As cores principais são definidas em `tailwind.config` dentro dos arquivos HTML.

