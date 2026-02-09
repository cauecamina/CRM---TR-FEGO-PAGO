<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/gemini.php';

requireLogin();

$id = $_GET['id'] ?? 0;
$db = getDbConnection();

// Reanálise Manual (Simples)
if (isset($_POST['reanalyze'])) {
    $stmt = $db->prepare("SELECT * FROM leads WHERE id = ?");
    $stmt->execute([$id]);
    $lead = $stmt->fetch();
    
    if ($lead) {
        $geminiInput = [
            'faturamento' => $lead['faturamento_raw'], // usar raw
            'investimento' => $lead['invest_raw'],
            'ramo' => $lead['ramo'],
            'faz_trafego' => $lead['faz_trafego'],
            'objetivo' => $lead['objetivo']
        ];
        
        $aiAnalysis = classifyLeadWithGemini($geminiInput);
        
        if ($aiAnalysis) {
             $stmtUpdate = $db->prepare("UPDATE leads SET 
                faturamento_categoria = ?, 
                invest_categoria = ?, 
                tags_ai = ?, 
                score_potencial = ?, 
                urgencia = ?, 
                resumo_ai = ? 
                WHERE id = ?");
             
             $stmtUpdate->execute([
                $aiAnalysis['faturamento_categoria'],
                $aiAnalysis['invest_categoria'],
                json_encode($aiAnalysis['tags_ai']),
                $aiAnalysis['score_potencial'],
                $aiAnalysis['urgencia'],
                $aiAnalysis['resumo'],
                $id
             ]);
             
             // Refresh
             header("Location: details.php?id=$id&msg=analyzed");
             exit;
        }
    }
}

$stmt = $db->prepare("SELECT * FROM leads WHERE id = ?");
$stmt->execute([$id]);
$lead = $stmt->fetch();

if (!$lead) die('Lead não encontrado');

$tags = json_decode($lead['tags_ai'], true) ?? [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Lead | CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen p-8">

    <div class="max-w-4xl mx-auto">
        <a href="kanban.php" class="text-blue-600 hover:underline mb-6 inline-block">← Voltar para Kanban</a>
        
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
            <!-- Header -->
            <div class="bg-slate-900 p-8 text-white flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold mb-2"><?= htmlspecialchars($lead['nome']) ?></h1>
                    <p class="text-gray-400 flex items-center gap-2">
                        <span><?= htmlspecialchars($lead['email']) ?></span> • <span><?= htmlspecialchars($lead['telefone']) ?></span>
                    </p>
                    <?php if($lead['instagram']): ?>
                        <a href="https://instagram.com/<?= str_replace('@', '', $lead['instagram']) ?>" target="_blank" class="text-blue-400 hover:text-blue-300 text-sm mt-2 inline-block">
                           Instragram: <?= htmlspecialchars($lead['instagram']) ?> ↗
                        </a>
                    <?php endif; ?>
                </div>
                <div class="text-right">
                    <div class="text-4xl font-bold text-blue-400"><?= $lead['score_potencial'] ?></div>
                    <div class="text-xs text-gray-400 uppercase tracking-wider">Score Potencial</div>
                    <div class="mt-2 inline-block px-3 py-1 rounded-full text-xs font-bold 
                        <?= $lead['urgencia'] == 'alta' ? 'bg-red-500/20 text-red-300' : 'bg-blue-500/20 text-blue-300' ?>">
                        Urgência: <?= ucfirst($lead['urgencia']) ?>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-0">
                <!-- Main Info -->
                <div class="col-span-2 p-8 border-r border-gray-100">
                    <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Resumo da IA</h2>
                    <p class="text-gray-600 mb-6 leading-relaxed italic">
                        "<?= htmlspecialchars($lead['resumo_ai'] ?? 'Sem análise.') ?>"
                    </p>

                    <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Respostas do Quiz</h2>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <span class="block text-xs text-gray-400 uppercase">Faturamento</span>
                            <span class="font-medium text-gray-800"><?= htmlspecialchars($lead['faturamento_raw']) ?></span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-400 uppercase">Investimento</span>
                            <span class="font-medium text-gray-800"><?= htmlspecialchars($lead['invest_raw']) ?></span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-400 uppercase">Ramo</span>
                            <span class="font-medium text-gray-800"><?= htmlspecialchars($lead['ramo']) ?></span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-400 uppercase">Já faz tráfego?</span>
                            <span class="font-medium text-gray-800"><?= htmlspecialchars($lead['faz_trafego']) ?></span>
                        </div>
                        <div class="col-span-2">
                            <span class="block text-xs text-gray-400 uppercase">Objetivo</span>
                            <span class="font-medium text-gray-800"><?= htmlspecialchars($lead['objetivo']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="p-8 bg-gray-50">
                    <h3 class="text-sm font-bold text-gray-500 uppercase mb-4">Insights & Tags</h3>
                    <div class="flex flex-wrap gap-2 mb-8">
                        <?php foreach ($tags as $tag): ?>
                            <span class="bg-white border border-gray-200 text-gray-600 px-3 py-1 rounded-full text-sm shadow-sm"><?= htmlspecialchars($tag) ?></span>
                        <?php endforeach; ?>
                    </div>

                    <h3 class="text-sm font-bold text-gray-500 uppercase mb-4">Ações</h3>
                    <form method="POST">
                        <button type="submit" name="reanalyze" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 rounded-lg transition shadow-lg shadow-indigo-200">
                            ✨ Reanalisar com IA
                        </button>
                    </form>
                    
                    <a href="https://wa.me/55<?= preg_replace('/\D/', '', $lead['telefone']) ?>" target="_blank" class="block w-full text-center mt-3 bg-green-500 hover:bg-green-600 text-white font-medium py-2 rounded-lg transition shadow-lg shadow-green-200">
                        💬 Chamar no WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
