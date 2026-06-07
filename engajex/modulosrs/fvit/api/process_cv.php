<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Get API Key
$stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'openai_api_key'");
$apiKey = $stmt->fetchColumn();

if (!$apiKey) {
    echo json_encode(['success' => false, 'error' => 'Chave de API não configurada. Contate o administrador.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$rawText = $input['text'] ?? '';
$options = $input['options'] ?? [];

if (empty($rawText)) {
    echo json_encode(['success' => false, 'error' => 'Texto do currículo vazio.']);
    exit;
}

// Construct Prompt
$promptRuleList = [];
foreach ($options as $key => $val) {
    if ($key === 'section_order')
        continue; // Handle separately
    if ($val === true)
        $promptRuleList[] = "Active Rule: " . strtoupper(str_replace('_', ' ', $key));
    if ($val === false)
        $promptRuleList[] = "Inactive Rule: " . strtoupper(str_replace('_', ' ', $key));
}

// Handle Order
$orderInstruction = "";
if (!empty($options['section_order']) && is_array($options['section_order'])) {
    $orderInstruction = "7. STRICTLY follow this order for the sections: " . implode(" -> ", $options['section_order']);
} else {
    $orderInstruction = "7. Order the sections: Personal Data -> Summary -> Experience -> Education -> Skills -> Certifications -> Technical Competencies -> Languages.";
}

// Handle Name Formatting specifically
$nameInstruction = "";
if (!empty($options['format_name']) && $options['format_name'] === true) {
    $nameInstruction = "8. CRITICAL: Reformat the candidate's name to be ONLY [First Name] [First Letter of First Surname]. (e.g., 'Ezequiel Santos' becomes 'Ezequiel S.', 'João da Silva' becomes 'João S.'). This name MUST be styled with color: #000000 (Black).";
} else {
    $nameInstruction = "8. Keep the full name of the candidate.";
}

$rulesString = implode("\n", $promptRuleList);

$systemPrompt = "You are an expert Resume Formatter and Career Coach. Your goal is to take raw text extracted from a PDF resume and reformat it into a high-quality, professional HTML structure suitable for printing to PDF.
Rules for formatting:
1. Return ONLY the HTML code inside a main <div class='cv-document'> container. Do not include <html>, <head> or <body> tags.
2. Styling: Use inline CSS for a professional, clean look.
   - STRICT COLOR RULE: EVERY SINGLE ELEMENT must be color: #000000. Do NOT use #333, #666, #999, or any other color. NO accent colors.
   - FONT: Arial, sans-serif.
   - NAME: <h1> tag. Font-size: 42px. Font-weight: bold. Color: #000000.
   - SECTION HEADERS: <h2> tag. Font-size: 26px. Uppercase. Border-bottom: 2px solid #000000. Color: #000000.
   - TEXT: <p> tag. Font-size: 16px. Color: #000000.
3. Language: Portuguese (Brazil). Identify the language of the input, but ensure headers and standard text are in Portuguese if the resume is Brazilian.
4. Correct any obvious grammar mistakes if 'FIX GRAMMAR' rule is active.
5. If 'ANON CONTACT' is active, remove phone numbers, emails, addresses, and LinkedIn URLs. Keep only the Name.
6. If 'REMOVE PHOTO' is active, do not include any <img> tags or placeholders for photos.
7. Specific Content Rules:
    - RESPONSIBILITIES REWRITING:
        - Language: Portuguese.
        - VERB TENSE LOGIC: Check date of each role.
            - If role is CURRENT (Active): FIRST bullet point must be in PRESENT TENSE (3rd person). All others in PAST TENSE.
            - If role is PAST (Ended): FIRST bullet point must be in PAST TENSE (3rd person). All others in PAST TENSE.
        - STYLE: Simplified, list format, preserving faithful activities.
        - PUNCTUATION: End EVERY list item with a semicolon (;).
        - STRUCTURE: Wrap each individual job/company experience block in a <div class='job-item'> container.
    - GENERIC SUMMARY: Create or refine a brief, generalist 'Resumo Profissional' based on experience/education.
    - SKILLS: List Hard Skills in clear CATEGORIES (e.g., 'Linguagens', 'Frameworks', 'Banco de Dados').
    - COURSES: List courses if found.
8. $orderInstruction
$nameInstruction
9. Data cleaning: Remove 'page numbers', 'scanned by', or artifacts.

Specific Configuration:
$rulesString
";

$userPrompt = "Here is the raw resume text:\n\n" . substr($rawText, 0, 15000); // Limit context window if needed

$data = [
    'model' => 'gpt-4o',
    'messages' => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => $userPrompt]
    ],
    'temperature' => 0.5
];

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo json_encode(['success' => false, 'error' => 'Erro de conexão OpenAI: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}

curl_close($ch);

if ($httpCode !== 200) {
    echo json_encode(['success' => false, 'error' => 'Erro na API OpenAI (Code ' . $httpCode . '): ' . $response]);
    exit;
}

$responseData = json_decode($response, true);
$content = $responseData['choices'][0]['message']['content'] ?? '';

// Clean up markdown code blocks if GPT adds them
$content = preg_replace('/^```html/', '', $content);
$content = preg_replace('/^```/', '', $content);
$content = preg_replace('/```$/', '', $content);

echo json_encode(['success' => true, 'html' => $content]);
?>