<?php
session_start();
require_once '../../config/db.php';

header('Content-Type: application/json');

// 1. Auth Check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// 2. Get Input
$input = json_decode(file_get_contents('php://input'), true);
$industry = $input['industry'] ?? '';
$country = $input['country'] ?? '';

if (empty($industry) || empty($country)) {
    http_response_code(400);
    echo json_encode(['error' => 'Industry and Country are required']);
    exit;
}

// 3. Get API Keys
$stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('openai_api_key', 'google_search_key', 'google_search_engine_id')");
$stmt->execute();
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$openai_key = $settings['openai_api_key'] ?? '';
$google_key = $settings['google_search_key'] ?? '';
$google_cx = $settings['google_search_engine_id'] ?? '';

if (empty($openai_key)) {
    http_response_code(500);
    echo json_encode(['error' => 'OpenAI API Key not configured']);
    exit;
}

// 4. Helper: Google Search Function
function google_search($query, $key, $cx)
{
    if (empty($key) || empty($cx))
        return [];

    $url = "https://www.googleapis.com/customsearch/v1?key={$key}&cx={$cx}&q=" . urlencode($query);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    $results = [];

    if (isset($data['items'])) {
        foreach ($data['items'] as $item) {
            $results[] = "Title: " . $item['title'] . " | Snippet: " . $item['snippet'];
        }
    }
    return $results;
}

// 5. Conduct Searches (Simulated Scraping via Snippets)
$search_context = "";
if (!empty($google_key) && !empty($google_cx)) {
    $search_context .= "RECENT SEARCH RESULTS:\n";

    // Search 1: Legislation
    $res1 = google_search("alteração legislação $industry $country " . date('Y'), $google_key, $google_cx);
    $search_context .= "Legislation:\n" . implode("\n", array_slice($res1, 0, 3)) . "\n\n";

    // Search 2: New Competitors
    $res2 = google_search("novas startups empresas $industry $country", $google_key, $google_cx);
    $search_context .= "Competitors:\n" . implode("\n", array_slice($res2, 0, 3)) . "\n\n";

    // Search 3: Trends
    $res3 = google_search("tendências futuro $industry $country " . date('Y'), $google_key, $google_cx);
    $search_context .= "Trends:\n" . implode("\n", array_slice($res3, 0, 3)) . "\n\n";
} else {
    $search_context = "Note: Live search is disabled (no API keys). Use your internal knowledge base up to your cutoff date.";
}

// 6. Call OpenAI GPT-4
$prompt = "
You are a Market Intelligence Expert.
Industry: $industry
Country: $country

Context from Web Search (if any):
$search_context

Task: detailed 'Market Radar' report.
Return a STRICT JSON object with these keys (values must be arrays of strings, in Portuguese):
{
    \"legislation\": [\"regulatory change 1\", \"law change 2\"],
    \"technologies\": [\"new tech 1\", \"tech 2\"],
    \"competitors\": [\"new player 1\", \"player 2\"],
    \"suppliers\": [\"rising supplier 1\", \"supplier 2\"],
    \"trends\": [\"customer trend 1\", \"market trend 2\"]
}

Focus on RECENT and RELEVANT information. If context is missing, use your expert knowledge.
";

$data = [
    'model' => 'gpt-4',
    'messages' => [
        ['role' => 'system', 'content' => 'You are a Senior Market Strategy Consultant.'],
        ['role' => 'user', 'content' => $prompt]
    ],
    'temperature' => 0.5
];

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $openai_key
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if (isset($result['choices'][0]['message']['content'])) {
    $raw_json = $result['choices'][0]['message']['content'];
    $raw_json = str_replace(['```json', '```'], '', $raw_json);
    $parsed_data = json_decode($raw_json, true);

    if ($parsed_data) {
        // Save to History (Using ci_history with a special flag or just text)
        // We will adapt ci_history to store this type by putting 'RADAR: Industry - Country' in target_url
        // Ideally we'd have a 'type' column, but let's be pragmatic.

        try {
            // Lazy Table Check already done in analyze.php, but let's perform insertion
            $stmt = $pdo->prepare("INSERT INTO ci_history (user_id, target_url, threat_score, analysis_json) VALUES (:user_id, :target, 0, :json)");
            $stmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':target' => "RADAR: $industry - $country", // Distinguishable in list
                ':json' => json_encode($parsed_data)
            ]);
        } catch (Exception $e) {
        }

        echo json_encode($parsed_data);
    } else {
        echo json_encode(['error' => 'Failed to parse AI response', 'raw' => $raw_json]);
    }
} else {
    echo json_encode(['error' => 'OpenAI API Error', 'details' => $result]);
}
?>