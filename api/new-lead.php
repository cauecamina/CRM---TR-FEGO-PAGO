<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/gemini.php';

header('Content-Type: application/json');

// Recebe JSON do corpo da requisição
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

try {
    $db = getDbConnection();

    // 1. Chamar o Gemini para classificar
    // Preparar dados para o Gemini (apenas relevante)
    $geminiInput = [
        'faturamento' => $input['faturamento'],
        'investimento' => $input['investimento'],
        'ramo' => $input['ramo'],
        'faz_trafego' => $input['faz_trafego'],
        'objetivo' => $input['objetivo']
    ];

    $aiAnalysis = classifyLeadWithGemini($geminiInput);

    // Fallback se a IA falhar
    if (!$aiAnalysis) {
        $aiAnalysis = [
            'faturamento_categoria' => '0-10k', // Default safe
            'invest_categoria' => '1k',
            'tags_ai' => ['Erro AI'],
            'score_potencial' => 0,
            'urgencia' => 'baixa',
            'resumo' => 'Não foi possível analisar automaticamente.'
        ];
    }

    // Mapeamento de Status Kanban baseado no Faturamento/Categoria da IA
    // Regra solicitada: Cold (0-10k), Morno (10-50k), Quente (50-200k), Ultra Quente (200k+)
    $statusKanban = 'Cold';
    $cat = $aiAnalysis['faturamento_categoria'] ?? '0-10k';
    
    if ($cat === '0-10k') $statusKanban = 'Cold';
    elseif ($cat === '10-50k') $statusKanban = 'Morno';
    elseif ($cat === '50-200k') $statusKanban = 'Quente';
    elseif ($cat === '200k+') $statusKanban = 'Ultra Quente';

    // 2. Salvar no Banco
    $stmt = $db->prepare("INSERT INTO leads (
        nome, email, telefone, instagram, ramo, 
        faturamento_raw, faturamento_categoria, 
        invest_raw, invest_categoria, 
        objetivo, faz_trafego, 
        tags_ai, score_potencial, urgencia, resumo_ai,
        status_kanban
    ) VALUES (
        ?, ?, ?, ?, ?, 
        ?, ?, 
        ?, ?, 
        ?, ?, 
        ?, ?, ?, ?,
        ?
    )");

    $stmt->execute([
        $input['nome'],
        $input['email'],
        $input['telefone'],
        $input['instagram'],
        $input['ramo'],
        $input['faturamento'],
        $aiAnalysis['faturamento_categoria'],
        $input['investimento'],
        $aiAnalysis['invest_categoria'],
        $input['objetivo'],
        $input['faz_trafego'],
        json_encode($aiAnalysis['tags_ai']),
        $aiAnalysis['score_potencial'],
        $aiAnalysis['urgencia'],
        $aiAnalysis['resumo'],
        $statusKanban
    ]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
