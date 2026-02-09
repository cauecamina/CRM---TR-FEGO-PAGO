<?php
// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'crm_antigravity');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configurações da API Gemini
// Substitua pela sua chave real
define('GEMINI_API_KEY', 'SUA CHAVE API AQUI'); 

// Configurações de Acesso Admin
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'admin123'); // Em produção, usar hash!

// URL Base do sistema
// Ajuste conforme seu ambiente (ex: http://localhost/crm-antigravity)
define('BASE_URL', 'http://localhost/crm-antigravity');

// Timezone
date_default_timezone_set('America/Sao_Paulo');
