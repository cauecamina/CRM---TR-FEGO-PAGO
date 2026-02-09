<?php
// Script de teste isolado para a API do Gemini
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/gemini.php';

echo "Testando conexão com Gemini...\n";
echo "API Key configurada: " . substr(GEMINI_API_KEY, 0, 5) . "...\n";

$testData = [
    'faturamento' => 'R$ 50.000 a R$ 200.000',
    'investimento' => 'R$ 3.000 a R$ 5.000',
    'ramo' => 'E-commerce',
    'faz_trafego' => 'Sim',
    'objetivo' => 'Aumentar Vendas'
];

echo "Enviando dados de teste...\n";
$start = microtime(true);
$result = classifyLeadWithGemini($testData);
$end = microtime(true);

echo "Tempo de resposta: " . number_format($end - $start, 4) . " segundos\n";

if ($result) {
    echo "SUCESSO! Resposta recebida:\n";
    print_r($result);
} else {
    echo "FALHA! A função retornou null.\n";
    // Tentar debugar o erro manualmente fazendo a request aqui de novo para ver o erro
    $apiKey = GEMINI_API_KEY;
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $apiKey;
    $promptData = json_encode($testData);
    $prompt = "Teste";
    $payload = ["contents" => [["parts" => [["text" => $prompt]]]]];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    echo "Debug Raw:\n";
    echo "HTTP Code: $httpCode\n";
    echo "CURL Error: $curlError\n";
    echo "Response Body: $response\n";
}
