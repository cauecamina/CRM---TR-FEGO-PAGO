<?php
require_once __DIR__ . '/../config.php';

function classifyLeadWithGemini($leadData) {
    $apiKey = GEMINI_API_KEY;
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $apiKey;

    // Constrói o texto do prompt com os dados do lead
    $promptData = json_encode($leadData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    $prompt = "Você é um assistente de qualificação de leads para uma agência de tráfego pago. Analise os dados deste lead:\n\n" . 
              $promptData . 
              "\n\nResponda APENAS com um JSON puro (sem markdown, sem ```json) contendo:\n" .
              "- faturamento_categoria (valores exatos permitidos: '0-10k', '10-50k', '50-200k', '200k+')\n" .
              "- invest_categoria (valores aproximados baseados no input: '1k', '3k', '5k', '10k', '10k+')\n" .
              "- tags_ai (array de strings com insights curtos, ex: 'E-commerce', 'Iniciante', 'Alto Potencial')\n" .
              "- score_potencial (inteiro 0-100)\n" .
              "- urgencia (enum: 'baixa', 'média', 'alta')\n" .
              "- resumo (uma frase curta descrevendo o potencial)\n\n" .
              "Regra de Faturamento: Se não for claro, estime baseado no contexto ou coloque '0-10k'.\n" .
              "JSON:";

    $payload = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.2,
            "maxOutputTokens" => 1024,
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        // Log de erro (poderia ser melhorado)
        return null;
    }

    $data = json_decode($response, true);
    
    // Extrair o texto da resposta
    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        $rawText = $data['candidates'][0]['content']['parts'][0]['text'];
        
        // Limpeza básica caso venha com markdown
        $rawText = str_replace(['```json', '```'], '', $rawText);
        $rawText = trim($rawText);
        
        return json_decode($rawText, true);
    }

    return null;
}
