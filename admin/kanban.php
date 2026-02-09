<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
requireLogin();

$db = getDbConnection();
$stmt = $db->query("SELECT * FROM leads ORDER BY created_at DESC");
$leads = $stmt->fetchAll();

// Agrupar leads por status
$columns = [
    'Cold' => [],
    'Morno' => [],
    'Quente' => [],
    'Ultra Quente' => []
];

foreach ($leads as $lead) {
    $status = $lead['status_kanban'] ?? 'Cold';
    if (!isset($columns[$status])) $status = 'Cold'; // Safety
    $columns[$status][] = $lead;
}

// Stats for header
$totalLeads = count($leads);
$highTicket = count($columns['Quente']) + count($columns['Ultra Quente']);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pipeline | CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- SortableJS for Drag and Drop -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <style>
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .kanban-col { min-height: 200px; }
        .ghost { opacity: 0.5; background: #e2e8f0; border: 2px dashed #94a3b8; }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-brand-50 min-h-screen flex flex-col font-sans">

    <!-- Header -->
    <header class="bg-white border-b border-brand-100 px-8 py-4 flex justify-between items-center shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 bg-brand-600 rounded-lg flex items-center justify-center text-white font-bold text-lg">AG</div>
            <h1 class="text-xl font-bold text-slate-800">Pipeline de Vendas</h1>
        </div>
        <div class="flex items-center gap-6">
            <div class="flex gap-4 text-sm">
                <div class="px-4 py-1.5 bg-brand-50 text-brand-900 rounded-full font-medium border border-brand-100">
                    Total: <span class="font-bold"><?= $totalLeads ?></span>
                </div>
                <div class="px-4 py-1.5 bg-green-50 text-green-700 rounded-full font-medium border border-green-100">
                    Potenciais: <span class="font-bold"><?= $highTicket ?></span>
                </div>
            </div>
            <a href="logout.php" class="text-slate-500 hover:text-red-500 transition">Sair</a>
        </div>
    </header>

    <!-- Kanban Board -->
    <main class="flex-1 overflow-x-auto overflow-y-hidden p-6">
        <div class="flex gap-6 h-full min-w-max">

            <?php 
            $colConfig = [
                'Cold' => ['bg' => 'bg-slate-100', 'border' => 'border-slate-300', 'badge' => 'text-slate-600 bg-slate-200', 'title' => 'Cold (0-10k)'],
                'Morno' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'badge' => 'text-blue-600 bg-blue-100', 'title' => 'Morno (10-50k)'],
                'Quente' => ['bg' => 'bg-indigo-50', 'border' => 'border-indigo-200', 'badge' => 'text-indigo-600 bg-indigo-100', 'title' => 'Quente (50-200k)'],
                'Ultra Quente' => ['bg' => 'bg-rose-50', 'border' => 'border-rose-200', 'badge' => 'text-rose-600 bg-rose-100', 'title' => 'Ultra (200k+)']
            ];
            ?>

            <?php foreach ($columns as $status => $items): ?>
                <?php $config = $colConfig[$status]; ?>
                <div class="w-80 flex flex-col h-full">
                    <!-- Column Header -->
                    <div class="flex justify-between items-center mb-4 px-2">
                        <h3 class="font-bold text-slate-700"><?= $config['title'] ?></h3>
                        <span class="text-xs font-bold px-2 py-1 rounded-full <?= $config['badge'] ?>"><?= count($items) ?></span>
                    </div>

                    <!-- Column Area -->
                    <div class="flex-1 rounded-xl p-3 <?= $config['bg'] ?> border <?= $config['border'] ?> kanban-col overflow-y-auto custom-scrollbar"
                         data-status="<?= $status ?>">
                        
                        <?php foreach ($items as $item): ?>
                            <?php 
                                $tags = json_decode($item['tags_ai'], true) ?? [];
                                $scoreColor = $item['score_potencial'] > 80 ? 'text-green-600' : ($item['score_potencial'] > 50 ? 'text-yellow-600' : 'text-slate-500');
                            ?>
                            <div class="bg-white p-4 rounded-lg shadow-sm border border-slate-100 mb-3 cursor-grab active:cursor-grabbing hover:shadow-md transition-shadow group"
                                 data-id="<?= $item['id'] ?>">
                                
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($item['nome']) ?></h4>
                                    <span class="text-xs font-mono font-bold <?= $scoreColor ?>"><?= $item['score_potencial'] ?>/100</span>
                                </div>
                                
                                <p class="text-xs text-slate-500 mb-1"><?= htmlspecialchars($item['ramo']) ?></p>
                                <p class="text-xs font-medium text-slate-700 mb-3">Fat: <?= htmlspecialchars($item['faturamento_raw']) ?></p>

                                <div class="flex flex-wrap gap-1 mb-3">
                                    <?php foreach (array_slice($tags, 0, 3) as $tag): ?>
                                        <span class="text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded border border-slate-200"><?= htmlspecialchars($tag) ?></span>
                                    <?php endforeach; ?>
                                </div>

                                <div class="flex justify-between items-center pt-2 border-t border-slate-50 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <span class="text-[10px] text-slate-400"><?= date('d/m H:i', strtotime($item['created_at'])) ?></span>
                                    <a href="details.php?id=<?= $item['id'] ?>" class="text-xs text-brand-600 font-medium hover:underline">Ver Detalhes →</a>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </main>

    <script>
        // Init Drag and Drop
        const columns = document.querySelectorAll('.kanban-col');
        
        columns.forEach(col => {
            new Sortable(col, {
                group: 'kanban',
                animation: 150,
                ghostClass: 'ghost',
                onEnd: function (evt) {
                    const itemEl = evt.item;
                    const newStatus = evt.to.getAttribute('data-status');
                    const leadId = itemEl.getAttribute('data-id');

                    // Ajax to update
                    fetch('api/update_status.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ id: leadId, status: newStatus })
                    }).then(res => res.json())
                      .then(data => {
                          if(!data.success) {
                              alert('Erro ao mover lead!');
                              // Optional: revert logic
                          }
                      });
                }
            });
        });
    </script>
</body>
</html>
