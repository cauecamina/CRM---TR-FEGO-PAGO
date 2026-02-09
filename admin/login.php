<?php
require_once __DIR__ . '/../includes/auth.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/admin/kanban.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    
    if (login($user, $pass)) {
        header('Location: ' . BASE_URL . '/admin/kanban.php');
        exit;
    } else {
        $error = 'Credenciais inválidas.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin | CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-sm">
        <h1 class="text-2xl font-bold text-slate-800 mb-6 text-center">Acesso CRM</h1>
        
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-600 p-3 rounded-lg mb-4 text-sm text-center">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">Usuário</label>
                <input type="text" name="username" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">Senha</label>
                <input type="password" name="password" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" required>
            </div>
            <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded-lg transition">
                Entrar
            </button>
        </form>
    </div>
</body>
</html>
