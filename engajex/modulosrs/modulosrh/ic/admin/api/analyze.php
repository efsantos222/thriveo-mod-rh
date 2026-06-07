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
$target_url = $input['url'] ?? '';

if (empty($target_url)) {
    http_response_code(400);
    echo json_encode(['error' => 'URL is required']);
    exit;
}

// 3. Get API Key
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'openai_api_key'");
$stmt->execute();
$api_key = $stmt->fetchColumn();

if (empty($api_key)) {
    http_response_code(500);
    echo json_encode(['error' => 'OpenAI API Key not configured in settings']);
    exit;
}

// 4. Web Scraping (Basic) using cURL
function get_web_content($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $html = curl_exec($ch);

    if (curl_errno($ch)) {
        return null;
    }
    curl_close($ch);
    return $html;
}

$html = get_web_content($target_url);

if (!$html) {
    http_response_code(400);
    echo json_encode(['error' => 'Could not access the target URL']);
    exit;
}

// Clean HTML to Text (Simple Extraction)
$dom = new DOMDocument();
@$dom->loadHTML($html);
$xpath = new DOMXPath($dom);
// Remove scripts and styles
foreach ($xpath->query('//script|//style') as $node) {
    $node->parentNode->removeChild($node);
}
$text_content = trim(preg_replace('/\s+/', ' ', $dom->textContent));
$text_sample = substr($text_content, 0, 8000); // Limit context for token usage

// 5. Call OpenAI GPT-4
$prompt = "
Analyze the following text scraped from a competitor's website or market news. 
Target URL: $target_url
Content: $text_sample

Perform a Competitive Analysis and return a STRICT pure JSON object (no markdown, no backticks) with this structure:
{
    \"threat_score\": (integer 0-100),
    \"recommendations\": [\"actionable strategy 1\", \"strategy 2\", \"strategy 3\"],
    \"swot\": {
        \"s\": [\"strength 1\", \"strength 2\"],
        \"w\": [\"weakness 1\", \"weakness 2\"],
        \"o\": [\"opportunity 1\", \"opportunity 2\"],
        \"t\": [\"threat 1\", \"threat 2\"]
    }
}
Translate all output to Portuguese (Brazil).
";

$data = [
    'model' => 'gpt-4',
    'messages' => [
        ['role' => 'system', 'content' => 'You are a Senior Market Intelligence Analyst.'],
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
    'Authorization: Bearer ' . $api_key
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if (isset($result['choices'][0]['message']['content'])) {
    $raw_json = $result['choices'][0]['message']['content'];
    // Try to clean markdown code blocks if present // ```json ... ```
    $raw_json = str_replace(['```json', '```'], '', $raw_json);
    $parsed_data = json_decode($raw_json, true);

    if ($parsed_data) {

        // Save to History
        try {
            // Ensure table exists (Lazy check)
            $pdo->exec("CREATE TABLE IF NOT EXISTS ci_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                target_url VARCHAR(255) NOT NULL,
                threat_score INT,
                analysis_json TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )");

            $stmt = $pdo->prepare("INSERT INTO ci_history (user_id, target_url, threat_score, analysis_json) VALUES (:user_id, :target_url, :threat_score, :analysis_json)");
            $stmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':target_url' => $target_url,
                ':threat_score' => $parsed_data['threat_score'] ?? 0,
                ':analysis_json' => json_encode($parsed_data)
            ]);
        } catch (Exception $e) {
            // Silent fail for history saving or log it. 
            // Don't break the response.
            // error_log($e->getMessage()); 
        }

        echo json_encode($parsed_data);
    } else {
        echo json_encode(['error' => 'Failed to parse AI response', 'raw' => $raw_json]);
    }
} else {
    echo json_encode(['error' => 'OpenAI API Error', 'details' => $result]);
}
?>