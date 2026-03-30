<?php
/**
 * api/chat.php — RAG Chatbot using Groq API
 * Context: all available notes titles & categories
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';

header('Content-Type: application/json');

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Rate limit: 20 requests per minute per session
$rl_key  = 'chat_rl';
$rl_time = 'chat_rl_time';
$now     = time();
if (isset($_SESSION[$rl_time]) && ($now - $_SESSION[$rl_time]) > 60) {
    $_SESSION[$rl_key]  = 0;
    $_SESSION[$rl_time] = $now;
}
$_SESSION[$rl_key]  = ($_SESSION[$rl_key]  ?? 0) + 1;
$_SESSION[$rl_time] = $_SESSION[$rl_time] ?? $now;
if ($_SESSION[$rl_key] > 20) {
    http_response_code(429);
    echo json_encode(['reply' => 'You\'re chatting quite fast! Please wait a moment before sending more messages.']);
    exit;
}

// Read body
$body    = file_get_contents('php://input');
$payload = json_decode($body, true);
$message = trim($payload['message'] ?? '');

if (empty($message) || mb_strlen($message) > 500) {
    echo json_encode(['reply' => 'Please enter a valid question (max 500 characters).']);
    exit;
}

// Build RAG context from DB
$notesContext = Notes::buildContextForAI();

$systemPrompt = <<<PROMPT
You are a helpful Tourism Studies AI assistant for the TravelTeachings website, run by Dr. Renu Malra — Associate Professor of Tourism at Kurukshetra University, India.

Your role is to:
1. Answer questions about tourism concepts, theory, history, and subjects
2. Help students find relevant study notes from the available materials below
3. Explain topics like sustainable tourism, e-tourism, hospitality management, etc.
4. Be friendly, concise, and academically accurate

AVAILABLE STUDY NOTES ON THIS WEBSITE:
$notesContext

If a student asks about a specific topic, mention which category/note might help them.
If asked something unrelated to tourism/education, gently redirect to tourism topics.
Keep responses under 200 words. Format key points as short bullet points when helpful.
PROMPT;

$groqKey = GROQ_API_KEY;

if (empty($groqKey)) {
    // Fallback response when API key not configured
    $fallback = [
        "I'm your Tourism AI assistant! While my AI features are being set up, I can tell you that TravelTeachings has notes on " . implode(', ', Notes::getCategories()) . ". Browse the Study Material page to find what you need!",
        "Great question about tourism! Please explore the Study Material section for comprehensive notes. Once the AI is fully configured, I'll be able to give you detailed answers.",
        "TravelTeachings offers notes across several tourism subjects. Head to the Study Material page to explore all categories. The AI assistant will be fully active soon!",
    ];
    echo json_encode(['reply' => $fallback[array_rand($fallback)]]);
    exit;
}

// Call Groq API
$groqPayload = [
    'model'       => GROQ_MODEL,
    'max_tokens'  => GROQ_MAX_TOKENS,
    'temperature' => 0.7,
    'messages'    => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user',   'content' => $message],
    ],
];

$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($groqPayload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $groqKey,
    ],
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !$response) {
    error_log("Groq API error: HTTP $httpCode — $response");
    echo json_encode(['reply' => 'I\'m having trouble connecting right now. Please try again in a moment, or browse the Study Material page directly!']);
    exit;
}

$data  = json_decode($response, true);
$reply = $data['choices'][0]['message']['content'] ?? 'I could not generate a response. Please try asking differently!';

echo json_encode(['reply' => $reply]);
